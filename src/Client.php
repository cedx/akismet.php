<?php namespace akismet;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpException;
use Symfony\Contracts\HttpClient\{HttpClientInterface, ResponseInterface as Response};

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
	private const string version = "15.0.1";

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
	 * The underlying HTTP client.
	 */
	private HttpClientInterface $http;

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
		$this->http = HttpClient::createForBaseUri((string) $this->baseUrl);
		$this->isTest = $isTest;
		$this->userAgent = $userAgent ?: "PHP/$phpVersion | Akismet/".self::version;
	}

	/**
	 * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
	 * @param Comment $comment The comment to be submitted.
	 * @return CheckResult A value indicating whether the specified comment is spam.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function checkComment(Comment $comment): CheckResult {
		$response = $this->fetch("1.1/comment-check", $comment->jsonSerialize());
		if ($response->getContent() == "false") return CheckResult::ham;

		$headers = $response->getHeaders();
		return ($headers["x-akismet-pro-tip"][0] ?? "") == "discard" ? CheckResult::pervasiveSpam : CheckResult::spam;
	}

	/**
	 * Submits the specified comment that was incorrectly marked as spam but should not have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function submitHam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-ham", $comment->jsonSerialize());
		if ($response->getContent() != self::success) throw new ClientException("Invalid server response.", 500);
	}

	/**
	 * Submits the specified comment that was not marked as spam but should have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function submitSpam(Comment $comment): void {
		$response = $this->fetch("1.1/submit-spam", $comment->jsonSerialize());
		if ($response->getContent() != self::success) throw new ClientException("Invalid server response.", 500);
	}

	/**
	 * Checks the API key against the service database, and returns a value indicating whether it is valid.
	 * @return bool `true` if the specified API key is valid, otherwise `false`.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function verifyKey(): bool {
		$response = $this->fetch("1.1/verify-key", (object) ["key" => $this->apiKey]);
		return $response->getContent() == "valid";
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the response.
	 * @param string $endpoint The URL of the end point to query.
	 * @param object $fields The fields describing the query body.
	 * @return Response The server response.
	 * @throws \Psr\Http\Client\ClientExceptionInterface An error occurred while querying the end point.
	 */
	private function fetch(string $endpoint, object $fields): Response {
		$postFields = $this->blog->jsonSerialize();
		foreach (get_object_vars($fields) as $key => $value) $postFields->$key = $value;
		$postFields->api_key = $this->apiKey;
		if ($this->isTest) $postFields->is_test = "1";

		try {
			$response = $this->http->request("POST", $endpoint, ["body" => get_object_vars($postFields)]);
			$headers = $response->getHeaders();
			if (isset($headers["x-akismet-alert-code"])) throw new ClientException($headers["x-akismet-alert-msg"][0], (int) $headers["x-akismet-alert-code"][0]);
			return isset($headers["x-akismet-debug-help"]) ? throw new ClientException($headers["x-akismet-debug-help"][0], 400) : $response;
		}
		catch (HttpException) {
			throw new ClientException("An error occurred while querying the end point.", 500);
		}
	}
}
