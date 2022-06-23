<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{ResponseInterface, UriInterface};
use Symfony\Component\HttpClient\{Psr18Client, Psr18NetworkException, Psr18RequestException};
use Symfony\Component\HttpClient\Exception\TransportException;

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client {

	/**
	 * The response returned by the `submit-ham` and `submit-spam` endpoints when the outcome is a success.
	 * @var {string}
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
			$pkgVersion = json_decode(file_get_contents(__DIR__."/../composer.json"))->version;
			$this->userAgent = "PHP/$phpVersion | Akismet/$pkgVersion";
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
		$response = $this->fetch($this->http->createUri("{$this->endpoint}comment-check"), $comment->jsonSerialize());
		return ((string) $response->getBody()) == "false"
			? CheckResult::ham
			: $response->getHeaderLine("x-akismet-pro-tip") == "discard" ? CheckResult::pervasiveSpam : CheckResult::spam;


		if (((string) $response->getBody()) == "false") return CheckResult::ham;
		return $response->getHeaderLine("x-akismet-pro-tip") == "discard" ? CheckResult::pervasiveSpam : CheckResult::spam;
	}

	/**
	 * Submits the specified comment that was incorrectly marked as spam but should not have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws Psr18RequestException TODO
	 */
	function submitHam(Comment $comment): void {
		$response = $this->fetch($this->http->createUri("{$this->endpoint}submit-ham"), $comment->jsonSerialize());
		if (((string) $response->getBody()) != self::successfulResponse) throw new \Exception("TODO Psr18RequestException");
	}

	/**
	 * Submits the specified comment that was not marked as spam but should have been.
	 * @param Comment $comment The comment to be submitted.
	 * @throws Psr18RequestException TODO
	 */
	function submitSpam(Comment $comment): void {
		$response = $this->fetch($this->http->createUri("{$this->endpoint}submit-spam"), $comment->jsonSerialize());
		if (((string) $response->getBody()) != self::successfulResponse) throw new \Exception("TODO Psr18RequestException");
	}

	/**
	 * Checks the API key against the service database, and returns a value indicating whether it is valid.
	 * @return bool `true` if the specified API key is valid, otherwise `false`.
	 */
	function verifyKey(): bool {
		$response = $this->fetch($this->http->createUri("{$this->baseUrl}verify-key"), (object) ["key" => $this->apiKey]);
		return ((string) $response->getBody()) == "valid";
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the response.
	 * @param UriInterface $endpoint The URL of the end point to query.
	 * @param object $fields The fields describing the query body.
	 * @throws ClientException An error occurred while querying the end point.
	 */
	private function fetch(UriInterface $endpoint, object $fields): ResponseInterface {
		$bodyFields = array_merge(get_object_vars($this->blog->jsonSerialize()), get_object_vars($fields));
		if ($this->isTest) $bodyFields["is_test"] = "1";

		try {
			$request = $this->http->createRequest("POST", $endpoint)
				->withBody($this->http->createStream(http_build_query($bodyFields, "", "&", PHP_QUERY_RFC1738)))
				->withHeader("User-Agent", $this->userAgent);

			$response = $this->http->sendRequest($request);
			if ($response->hasHeader("x-akismet-debug-help")) throw new ClientException($response->getHeaderLine("x-akismet-debug-help"), $endpoint);
			return $response;
		}

		catch (\Throwable $e) {
			if ($e instanceof ClientException) throw $e;
			throw new ClientException($e->getMessage(), $endpoint, $e);
		}
	}
}
