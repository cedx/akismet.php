<?php declare(strict_types=1);
namespace Belin\Akismet;

use Uri\Rfc3986\Uri;

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
final class Client {

	/**
	 * The response returned by the `submit-ham` and `submit-spam` endpoints when the outcome is a success.
	 */
	private const string Success = "Thanks for making the web a better place.";

	/**
	 * The package version number.
	 */
	private static ?string $version = null;

	/**
	 * The Akismet API key.
	 */
	public readonly string $apiKey;

	/**
	 * The base URL of the remote API endpoint.
	 */
	public readonly Uri $baseUrl;

	/**
	 * The front page or home URL of the instance making requests.
	 */
	public Blog $blog;

	/**
	 * Value indicating whether the client operates in test mode.
	 */
	public bool $isTest;

	/**
	 * The user agent string to use when making requests.
	 */
	public string $userAgent;

	/**
	 * Creates a new client.
	 * @param string $apiKey The Akismet API key.
	 * @param Blog $blog The front page or home URL of the instance making requests.
	 * @param bool $isTest Value indicating whether the client operates in test mode.
	 * @param string $userAgent The user agent string to use when making requests.
	 * @param string|Uri $baseUrl The base URL of the remote API endpoint.
	 */
	public function __construct(string $apiKey, Blog $blog, bool $isTest = false, string $userAgent = "", string|Uri $baseUrl = "https://rest.akismet.com") {
		self::$version ??= json_decode((string) file_get_contents(__DIR__ . "/../composer.json"))->version;

		$this->apiKey = $apiKey;
		$this->baseUrl = new Uri(mb_rtrim($baseUrl instanceof Uri ? $baseUrl->toString() : $baseUrl, "/"));
		$this->blog = $blog;
		$this->isTest = $isTest;
		$this->userAgent = $userAgent ?: sprintf("PHP/%d | Akismet/%s", PHP_MAJOR_VERSION, self::$version);
	}

	/**
	 * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
	 * @param Comment $comment The comment to be submitted.
	 * @return CheckResult A value indicating whether the specified comment is spam.
	 * @throws \RuntimeException The remote server returned an invalid response.
	 */
	public function checkComment(Comment $comment): CheckResult {
		$response = $this->fetch("1.1/comment-check", $comment->jsonSerialize());
		if ($response->body == "false") return CheckResult::Ham;
		return ($response->headers["x-akismet-pro-tip"] ?? "") == "discard" ? CheckResult::PervasiveSpam : CheckResult::Spam;
	}

	/**
	 * Submits the specified comment that was incorrectly marked as spam but should not have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \RuntimeException The remote server returned an invalid response.
	 */
	public function submitHam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-ham", $comment->jsonSerialize());
		if ($response->body != self::Success) throw new \RuntimeException("Invalid server response.", 500);
	}

	/**
	 * Submits the specified comment that was not marked as spam but should have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \RuntimeException The remote server returned an invalid response.
	 */
	public function submitSpam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-spam", $comment->jsonSerialize());
		if ($response->body != self::Success) throw new \RuntimeException("Invalid server response.", 500);
	}

	/**
	 * Checks the API key against the service database, and returns a value indicating whether it is valid.
	 * @return bool `true` if the specified API key is valid, otherwise `false`.
	 */
	public function verifyKey(): bool {
		try {
			$response = $this->fetch("1.1/verify-key");
			return $response->body == "valid";
		}
		catch (\RuntimeException) {
			return false;
		}
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the response.
	 * @param string $endpoint The URL of the end point to query.
	 * @param object $fields The fields describing the query body.
	 * @return \stdClass The server response.
	 * @throws \RuntimeException An error occurred while querying the end point.
	 */
	private function fetch(string $endpoint, ?object $fields = null): \stdClass {
		$handle = curl_init($this->baseUrl->withPath("{$this->baseUrl->getPath()}/$endpoint")->toString());
		if (!$handle) throw new \RuntimeException("Unable to allocate the cURL handle.", 500);

		$postFields = $this->blog->jsonSerialize();
		foreach (get_object_vars($fields ?? new \stdClass) as $key => $value) $postFields->$key = $value;
		$postFields->api_key = $this->apiKey;
		if ($this->isTest) $postFields->is_test = "1";

		$headers = [];
		curl_setopt_array($handle, [
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADERFUNCTION => function($_, $header) use (&$headers) {
				if (count($parts = explode(":", $header, 2)) == 2) $headers[mb_trim($parts[0]) |> mb_strtolower(...)] = mb_trim($parts[1]);
				return strlen($header);
			},
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($postFields, arg_separator: "&"),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $this->userAgent
		]);

		$body = curl_exec($handle);
		if ($body === false) throw new \RuntimeException(curl_error($handle), 500);

		$statusCode = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
		if (intdiv($statusCode, 100) != 2) throw new \RuntimeException("The server response failed.", $statusCode);
		if (isset($headers["x-akismet-alert-code"])) throw new \RuntimeException($headers["x-akismet-alert-msg"], 403);
		if (isset($headers["x-akismet-debug-help"])) throw new \RuntimeException($headers["x-akismet-debug-help"], 400);
		return (object) ["headers" => $headers, "body" => (string) $body];
	}
}
