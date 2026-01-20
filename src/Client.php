<?php declare(strict_types=1);
namespace Belin\Akismet;

use Nyholm\Psr7\{Response, Uri};
use Psr\Http\Message\UriInterface;

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
final readonly class Client {

	/**
	 * The response returned by the `submit-ham` and `submit-spam` endpoints when the outcome is a success.
	 */
	private const string success = "Thanks for making the web a better place.";

	/**
	 * The package version.
	 */
	private const string version = "16.0.0";

	/**
	 * The Akismet API key.
	 */
	public string $apiKey;

	/**
	 * The base URL of the remote API endpoint.
	 */
	public UriInterface $baseUrl;

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
	 * @param string|UriInterface $baseUrl The base URL of the remote API endpoint.
	 */
	function __construct(string $apiKey, Blog $blog, bool $isTest = false, string $userAgent = "", string|UriInterface $baseUrl = "https://rest.akismet.com") {
		$this->apiKey = $apiKey;
		$this->baseUrl = new Uri(mb_rtrim((string) $baseUrl, "/"));
		$this->blog = $blog;
		$this->isTest = $isTest;
		$this->userAgent = $userAgent ?: sprintf("PHP/%d | Akismet/%s", PHP_MAJOR_VERSION, self::version);
	}

	/**
	 * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
	 * @param Comment $comment The comment to be submitted.
	 * @return CheckResult A value indicating whether the specified comment is spam.
	 * @throws \RuntimeException The remote server returned an invalid response.
	 */
	function checkComment(Comment $comment): CheckResult {
		$response = $this->fetch("1.1/comment-check", $comment->jsonSerialize());
		return (string) $response->getBody() == "false"
			? CheckResult::Ham
			: ($response->getHeaderLine("x-akismet-pro-tip") == "discard" ? CheckResult::PervasiveSpam : CheckResult::Spam);
	}

	/**
	 * Submits the specified comment that was incorrectly marked as spam but should not have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \RuntimeException The remote server returned an invalid response.
	 */
	function submitHam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-ham", $comment->jsonSerialize());
		if ((string) $response->getBody() != self::success) throw new \RuntimeException("Invalid server response.", 500);
	}

	/**
	 * Submits the specified comment that was not marked as spam but should have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \RuntimeException The remote server returned an invalid response.
	 */
	function submitSpam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-spam", $comment->jsonSerialize());
		if ((string) $response->getBody() != self::success) throw new \RuntimeException("Invalid server response.", 500);
	}

	/**
	 * Checks the API key against the service database, and returns a value indicating whether it is valid.
	 * @return bool `true` if the specified API key is valid, otherwise `false`.
	 */
	function verifyKey(): bool {
		try {
			$response = $this->fetch("1.1/verify-key", new \stdClass);
			return (string) $response->getBody() == "valid";
		}
		catch (\RuntimeException) {
			return false;
		}
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the response.
	 * @param string $endpoint The URL of the end point to query.
	 * @param object $fields The fields describing the query body.
	 * @return Response The server response.
	 * @throws \RuntimeException An error occurred while querying the end point.
	 */
	private function fetch(string $endpoint, object $fields): Response {
		$handle = curl_init((string) $this->baseUrl->withPath("{$this->baseUrl->getPath()}/$endpoint"));
		if (!$handle) throw new \RuntimeException("Unable to allocate the cURL handle.", 500);

		$postFields = $this->blog->jsonSerialize();
		foreach (get_object_vars($fields) as $key => $value) $postFields->$key = $value;
		$postFields->api_key = $this->apiKey;
		if ($this->isTest) $postFields->is_test = "1";

		$headers = [];
		curl_setopt_array($handle, [
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADERFUNCTION => function($_, $header) use (&$headers) {
				if (count($parts = explode(":", $header, 2)) == 2) $headers[mb_trim($parts[0])][] = mb_trim($parts[1]);
				return strlen($header);
			},
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($postFields, arg_separator: "&"),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $this->userAgent
		]);

		$body = curl_exec($handle);
		if ($body === false) throw new \RuntimeException(curl_error($handle), 500);

		$response = new Response(curl_getinfo($handle, CURLINFO_RESPONSE_CODE), $headers, (string) $body);
		if (intdiv($status = $response->getStatusCode(), 100) != 2) throw new \RuntimeException($response->getReasonPhrase(), $status);

		return $response->hasHeader("x-akismet-alert-code")
			? throw new \RuntimeException("{$response->getHeaderLine("x-akismet-alert-code")} {$response->getHeaderLine("x-akismet-alert-msg")}", 403)
			: ($response->hasHeader("x-akismet-debug-help") ? throw new \RuntimeException($response->getHeaderLine("x-akismet-debug-help"), 400) : $response);
	}
}
