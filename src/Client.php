<?php namespace akismet;

use Nyholm\Psr7\{Response, Uri};
use Psr\Http\Message\{ResponseInterface, UriInterface};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
final class Client {

	/**
	 * The response returned by the `submit-ham` and `submit-spam` endpoints when the outcome is a success.
	 * @var string
	 */
	private const success = "Thanks for making the web a better place.";

	/**
	 * The package version.
	 * @var string
	 */
	private const version = "15.0.1";

	/**
	 * The Akismet API key.
	 */
	readonly string $apiKey;

	/**
	 * The base URL of the remote API endpoint.
	 */
	readonly UriInterface $baseUrl;

	/**
	 * The front page or home URL of the instance making requests.
	 */
	readonly Blog $blog;

	/**
	 * Value indicating whether the client operates in test mode.
	 */
	readonly bool $isTest;

	/**
	 * The user agent string to use when making requests.
	 */
	readonly string $userAgent;

	/**
	 * Creates a new client.
	 * @param string $apiKey The Akismet API key.
	 * @param Blog $blog The front page or home URL of the instance making requests.
	 * @param bool $isTest Value indicating whether the client operates in test mode.
	 * @param string $userAgent The user agent string to use when making requests.
	 * @param string $baseUrl The base URL of the remote API endpoint.
	 */
	function __construct(string $apiKey, Blog $blog, bool $isTest = false, string $userAgent = "", string $baseUrl = "https://rest.akismet.com") {
		$phpVersion = implode(".", [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);
		$this->apiKey = $apiKey;
		$this->baseUrl = new Uri(str_ends_with($baseUrl, "/") ? $baseUrl : "$baseUrl/");
		$this->blog = $blog;
		$this->isTest = $isTest;
		$this->userAgent = $userAgent ?: "PHP/$phpVersion | Akismet/".self::version;
	}

	/**
	 * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
	 * @param Comment $comment The comment to be submitted.
	 * @return CheckResult A value indicating whether the specified comment is spam.
	 */
	function checkComment(Comment $comment): CheckResult {
		$response = $this->fetch("1.1/comment-check", $comment->jsonSerialize());
		return (string) $response->getBody() == "false"
			? CheckResult::ham
			: ($response->getHeaderLine("X-akismet-pro-tip") == "discard" ? CheckResult::pervasiveSpam : CheckResult::spam);
	}

	/**
	 * Submits the specified comment that was incorrectly marked as spam but should not have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function submitHam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-ham", $comment->jsonSerialize());
		if ((string) $response->getBody() != self::success) throw new ClientException("Invalid server response.", 500);
	}

	/**
	 * Submits the specified comment that was not marked as spam but should have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function submitSpam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-spam", $comment->jsonSerialize());
		if ((string) $response->getBody() != self::success) throw new ClientException("Invalid server response.", 500);
	}

	/**
	 * Checks the API key against the service database, and returns a value indicating whether it is valid.
	 * @return bool `true` if the specified API key is valid, otherwise `false`.
	 */
	function verifyKey(): bool {
		$response = $this->fetch("1.1/verify-key", (object) ["key" => $this->apiKey]);
		return (string) $response->getBody() == "valid";
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the response.
	 * @param string $endpoint The URL of the end point to query.
	 * @param object $fields The fields describing the query body.
	 * @return ResponseInterface The server response.
	 * @throws \Psr\Http\Client\ClientExceptionInterface An error occurred while querying the end point.
	 */
	private function fetch(string $endpoint, object $fields): ResponseInterface {
		$handle = curl_init((string) $this->baseUrl->withPath("{$this->baseUrl->getPath()}$endpoint"));
		if (!$handle) throw new ClientException("Unable to allocate the cURL handle.", 500);

		$postFields = $this->blog->jsonSerialize();
		foreach (get_object_vars($fields) as $key => $value) $postFields->$key = $value;
		$postFields->api_key = $this->apiKey;
		if ($this->isTest) $postFields->is_test = "1";

		$headers = [];
		curl_setopt_array($handle, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($postFields, arg_separator: "&", encoding_type: PHP_QUERY_RFC1738),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_HEADERFUNCTION => function($_, $header) use (&$headers) {
				$parts = explode(":", $header, 2);
				if (count($parts) == 2) $headers[trim($parts[0])] = trim($parts[1]);
				return strlen($header);
			}
		]);

		$body = curl_exec($handle);
		if ($body === false) throw new ClientException("An error occurred while querying the end point.", 500);

		$response = new Response(body: (string) $body, headers: $headers, status: curl_getinfo($handle, CURLINFO_RESPONSE_CODE));
		if (intdiv($status = $response->getStatusCode(), 100) != 2) throw new ClientException($response->getReasonPhrase(), $status);

		if ($response->hasHeader("X-akismet-alert-code")) {
			$code = (int) $response->getHeaderLine("X-akismet-alert-code");
			throw new ClientException($response->getHeaderLine("X-akismet-alert-msg"), $code);
		}

		return $response->hasHeader("X-akismet-debug-help")
			? throw new ClientException($response->getHeaderLine("X-akismet-debug-help"), 400)
			: $response;
	}
}
