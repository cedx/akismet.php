<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{ResponseInterface, UriInterface};
use Symfony\Component\HttpClient\Psr18Client;

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client {

	/** An event that is triggered when a request is made to the remote service. */
	const eventRequest = RequestEvent::class;

	/** An event that is triggered when a response is received from the remote service. */
	const eventResponse = ResponseEvent::class;

	/** The Akismet API key. */
	private string $apiKey;

	/** The front page or home URL of the instance making requests. */
	private Blog $blog;

	/** The URL of the API end point. */
	private UriInterface $endPoint;

	/** The HTTP client. */
	private Psr18Client $http;

	/** Value indicating whether the client operates in test mode. */
	private bool $isTest = false;

	/** The user agent string to use when making requests. */
	private string $userAgent;

	/** Creates a new client. */
	function __construct(string $apiKey, Blog $blog) {
		$this->apiKey = $apiKey;
		$this->blog = $blog;
		$this->http = new Psr18Client;
		$this->endPoint = $this->http->createUri("https://rest.akismet.com/1.1/");

		$phpVersion = implode(".", [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);
		$pkgVersion = require __DIR__."/version.g.php";
		$this->userAgent = "PHP/$phpVersion | Akismet/$pkgVersion";
	}

	/** Checks the specified comment against the service database, and returns a value indicating whether it is spam. */
	function checkComment(Comment $comment): string {
		$apiUrl = $this->getEndPoint();
		$endPoint = $this->http->createUri("{$apiUrl->getScheme()}://{$this->getApiKey()}.{$apiUrl->getAuthority()}{$apiUrl->getPath()}comment-check");

		$response = $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
		if (((string) $response->getBody()) == "false") return CheckResult::isHam;
		return $response->getHeaderLine("x-akismet-pro-tip") == "discard" ? CheckResult::isPervasiveSpam : CheckResult::isSpam;
	}

	/** Gets the Akismet API key. */
	function getApiKey(): string {
		return $this->apiKey;
	}

	/** Gets the front page or home URL of the instance making requests. */
	function getBlog(): Blog {
		return $this->blog;
	}

	/** Gets the URL of the API end point. */
	function getEndPoint(): UriInterface {
		return $this->endPoint;
	}

	/**
	 * Gets the user agent string to use when making requests.
	 * If possible, the user agent string should always have the following format: `Application Name/Version | Plugin Name/Version`.
	 */
	function getUserAgent(): string {
		return $this->userAgent;
	}

	/** Gets a value indicating whether the client operates in test mode. */
	function isTest(): bool {
		return $this->isTest;
	}

	/** Sets the URL of the API end point. */
	function setEndPoint(UriInterface $value): self {
		$this->endPoint = $value->withUserInfo("");
		return $this;
	}

	/**
	 * Sets a value indicating whether the client operates in test mode.
	 * You can use it when submitting test queries to Akismet.
	 */
	function setTest(bool $value): self {
		$this->isTest = $value;
		return $this;
	}

	/** Sets the user agent string to use when making requests. */
	function setUserAgent(string $value): self {
		$this->userAgent = $value;
		return $this;
	}

	/** Submits the specified comment that was incorrectly marked as spam but should not have been. */
	function submitHam(Comment $comment): void {
		$apiUrl = $this->getEndPoint();
		$endPoint = $this->http->createUri("{$apiUrl->getScheme()}://{$this->getApiKey()}.{$apiUrl->getAuthority()}{$apiUrl->getPath()}submit-ham");
		$this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
	}

	/** Submits the specified comment that was not marked as spam but should have been. */
	function submitSpam(Comment $comment): void {
		$apiUrl = $this->getEndPoint();
		$endPoint = $this->http->createUri("{$apiUrl->getScheme()}://{$this->getApiKey()}.{$apiUrl->getAuthority()}{$apiUrl->getPath()}submit-spam");
		$this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
	}

	/** Checks the API key against the service database, and returns a value indicating whether it is valid. */
	function verifyKey(): bool {
		$apiUrl = $this->getEndPoint();
		$response = $this->fetch($apiUrl->withPath("{$apiUrl->getPath()}verify-key"), ["key" => $this->getApiKey()]);
		return ((string) $response->getBody()) == "valid";
	}

	/**
	 * Queries the service by posting the specified fields to a given end point, and returns the server response.
	 * @throws ClientException An error occurred while querying the end point.
	 */
	private function fetch(UriInterface $endPoint, array $fields = []): ResponseInterface {
		$bodyFields = array_merge(get_object_vars($this->getBlog()->jsonSerialize()), $fields);
		if ($this->isTest()) $bodyFields["is_test"] = "1";

		try {
			$request = $this->http->createRequest("POST", $endPoint)
				->withBody($this->http->createStream(http_build_query($bodyFields, "", "&", PHP_QUERY_RFC1738)))
				->withHeader("User-Agent", $this->getUserAgent());

			$this->dispatch(new RequestEvent($request));
			$response = $this->http->sendRequest($request);
			$this->dispatch(new ResponseEvent($response, $request));

			if ($response->hasHeader("x-akismet-debug-help")) throw new ClientException($response->getHeaderLine("x-akismet-debug-help"), $endPoint);
			return $response;
		}

		catch (\Throwable $e) {
			if ($e instanceof ClientException) throw $e;
			throw new ClientException($e->getMessage(), $endPoint, $e);
		}
	}
}
