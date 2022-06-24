<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{ResponseInterface, UriInterface};
use Symfony\Component\HttpClient\{Psr18Client, Psr18RequestException};
use Symfony\Component\HttpClient\Exception\TransportException;

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client {

	/**
	 * The response returned by the `submit-ham` and `submit-spam` endpoints when the outcome is a success.
	 * @var string
	 */
	private const successfulResponse = "Thanks for making the web a better place.";

	/**
	 * The Akismet API key.
	 * @var string
	 */
	public readonly string $apiKey;

	/**
	 * The base URL of the remote API endpoint.
	 * @var UriInterface
	 */
	public readonly UriInterface $baseUrl;

	/**
	 * The front page or home URL of the instance making requests.
	 * @var Blog
	 */
	public readonly Blog $blog;

	/**
	 * Value indicating whether the client operates in test mode.
	 * @var bool
	 */
	public readonly bool $isTest;

	/**
	 * The user agent string to use when making requests.
	 * @var string
	 */
	public readonly string $userAgent;

	/**
	 * The final URL of the remote API endpoint.
	 * @var UriInterface
	 */
	private UriInterface $endpoint;

	/**
	 * The underlying HTTP client.
	 * @var Psr18Client
	 */
	private Psr18Client $http;

	/**
	 * Creates a new client.
	 * @param string $apiKey The Akismet API key.
	 * @param Blog $blog The front page or home URL of the instance making requests.
	 * @param bool $isTest Value indicating whether the client operates in test mode.
	 * @param string $userAgent The user agent string to use when making requests.
	 * @param string $baseUrl The base URL of the remote API endpoint.
	 */
	function __construct(string $apiKey, Blog $blog, bool $isTest = false, string $userAgent = "", string $baseUrl = "https://rest.akismet.com/1.1/") {
		$this->apiKey = $apiKey;
		$this->blog = $blog;
		$this->isTest = $isTest;

		if ($userAgent) $this->userAgent = $userAgent;
		else {
			$phpVersion = implode(".", [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);
			$pkgVersion = json_decode(file_get_contents(__DIR__."/../composer.json") ?: "")->version; // @phpstan-ignore-line
			$this->userAgent = "PHP/$phpVersion | Akismet/$pkgVersion"; // @phpstan-ignore-line
		}

		$this->http = new Psr18Client;
		$this->baseUrl = $this->http->createUri($baseUrl);
		$this->endpoint = $this->http->createUri("{$this->baseUrl->getScheme()}://{$this->apiKey}.{$this->baseUrl->getAuthority()}{$this->baseUrl->getPath()}");
	}

	/**
	 * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
	 * @param Comment $comment The comment to be submitted.
	 * @return CheckResult A value indicating whether the specified comment is spam.
	 */
	function checkComment(Comment $comment): CheckResult {
		$endpoint = $this->endpoint->withPath("{$this->endpoint->getPath()}comment-check");
		$response = $this->fetch($endpoint, $comment->jsonSerialize());
		return $response->getBody()->getContents() == "false"
			? CheckResult::ham
			: ($response->getHeaderLine("x-akismet-pro-tip") == "discard" ? CheckResult::pervasiveSpam : CheckResult::spam);
	}

	/**
	 * Submits the specified comment that was incorrectly marked as spam but should not have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function submitHam(Comment $comment): void {
		$endpoint = $this->endpoint->withPath("{$this->endpoint->getPath()}submit-ham");
		$response = $this->fetch($endpoint, $comment->jsonSerialize());
		if ($response->getBody()->getContents() != self::successfulResponse)
			throw new ClientException("Invalid server response.", $response->getStatusCode());
	}

	/**
	 * Submits the specified comment that was not marked as spam but should have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws \Psr\Http\Client\ClientExceptionInterface The remote server returned an invalid response.
	 */
	function submitSpam(Comment $comment): void {
		$endpoint = $this->endpoint->withPath("{$this->endpoint->getPath()}submit-spam");
		$response = $this->fetch($endpoint, $comment->jsonSerialize());
		if ($response->getBody()->getContents() != self::successfulResponse)
			throw new ClientException("Invalid server response.", $response->getStatusCode());
	}

	/**
	 * Checks the API key against the service database, and returns a value indicating whether it is valid.
	 * @return bool `true` if the specified API key is valid, otherwise `false`.
	 */
	function verifyKey(): bool {
		$endpoint = $this->endpoint->withPath("{$this->endpoint->getPath()}verify-key");
		$response = $this->fetch($endpoint, (object) ["key" => $this->apiKey]);
		return $response->getBody()->getContents() == "valid";
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the response.
	 * @param UriInterface $endpoint The URL of the end point to query.
	 * @param object $fields The fields describing the query body.
	 * @return ResponseInterface The server response.
	 * @throws \Psr\Http\Client\RequestExceptionInterface An error occurred while querying the end point.
	 */
	private function fetch(UriInterface $endpoint, object $fields): ResponseInterface {
		$body = $this->blog->jsonSerialize();
		foreach ($fields as $key => $value) $body->$key = $value; // @phpstan-ignore-line
		if ($this->isTest) $body->is_test = "1";

		$request = $this->http->createRequest("POST", $endpoint)
			->withBody($this->http->createStream(http_build_query($body, arg_separator: "&", encoding_type: PHP_QUERY_RFC1738)))
			->withHeader("User-Agent", $this->userAgent);

		$response = $this->http->sendRequest($request);
		if (intdiv($response->getStatusCode(), 100) != 2)
			throw new Psr18RequestException(new TransportException($response->getReasonPhrase()), $request);

		if ($response->hasHeader("x-akismet-alert-code")) {
			$code = (int) $response->getHeaderLine("x-akismet-alert-code");
			throw new Psr18RequestException(new TransportException($response->getHeaderLine("x-akismet-alert-msg"), $code), $request);
		}

		if ($response->hasHeader("x-akismet-debug-help"))
			throw new Psr18RequestException(new TransportException($response->getHeaderLine("x-akismet-debug-help")), $request);

		return $response;
	}
}
