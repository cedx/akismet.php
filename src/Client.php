<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{ResponseInterface, UriInterface};
use Symfony\Component\EventDispatcher\{EventDispatcher};
use Symfony\Component\HttpClient\{Psr18Client};

/** Submits comments to the [Akismet](https://akismet.com) service. */
class Client extends EventDispatcher {

  /** @var string An event that is triggered when a request is made to the remote service. */
  const eventRequest = RequestEvent::class;

  /** @var string An event that is triggered when a response is received from the remote service. */
  const eventResponse = ResponseEvent::class;

  /** @var string The Akismet API key. */
  private string $apiKey;

  /** @var Blog The front page or home URL of the instance making requests. */
  private Blog $blog;

  /** @var UriInterface The URL of the API end point. */
  private UriInterface $endPoint;

  /** @var Psr18Client The HTTP client. */
  private Psr18Client $http;

  /** @var bool Value indicating whether the client operates in test mode. */
  private bool $isTest = false;

  /** @var string The user agent string to use when making requests. */
  private string $userAgent;

  /**
   * Creates a new client.
   * @param string $apiKey The Akismet API key.
   * @param Blog $blog The front page or home URL of the instance making requests.
   */
  function __construct(string $apiKey, Blog $blog) {
    assert(mb_strlen($apiKey) > 0);
    parent::__construct();

    $this->apiKey = $apiKey;
    $this->blog = $blog;
    $this->http = new Psr18Client;
    $this->endPoint = $this->http->createUri('https://rest.akismet.com/1.1/');

    $phpVersion = implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);
    $this->userAgent = sprintf("PHP/$phpVersion | Akismet/".require __DIR__.'/version.g.php');
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return string A `CheckResult` value indicating whether the specified comment is spam.
   */
  function checkComment(Comment $comment): string {
    $apiUrl = $this->getEndPoint();
    $endPoint = $this->http->createUri("{$apiUrl->getScheme()}://{$this->getApiKey()}.{$apiUrl->getAuthority()}{$apiUrl->getPath()}comment-check");

    $response = $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
    if (((string) $response->getBody()) == 'false') return CheckResult::isHam;
    return $response->getHeaderLine('X-akismet-pro-tip') == 'discard' ? CheckResult::isPervasiveSpam : CheckResult::isSpam;
  }

  /**
   * Gets the Akismet API key.
   * @return string The Akismet API key.
   */
  function getApiKey(): string {
    return $this->apiKey;
  }

  /**
   * Gets the front page or home URL of the instance making requests.
   * @return Blog The front page or home URL.
   */
  function getBlog(): Blog {
    return $this->blog;
  }

  /**
   * Gets the URL of the API end point.
   * @return UriInterface The URL of the API end point.
   */
  function getEndPoint(): UriInterface {
    return $this->endPoint;
  }

  /**
   * Gets the user agent string to use when making requests.
   * If possible, the user agent string should always have the following format: `Application Name/Version | Plugin Name/Version`.
   * @return string The user agent string to use when making requests.
   */
  function getUserAgent(): string {
    return $this->userAgent;
  }

  /**
   * Gets a value indicating whether the client operates in test mode.
   * @return bool `true` if the client operates in test mode, otherwise `false`.
   */
  function isTest(): bool {
    return $this->isTest;
  }

  /**
   * Sets the URL of the API end point.
   * @param UriInterface $value The new URL of the API end point.
   * @return $this This instance.
   */
  function setEndPoint(UriInterface $value): self {
    $this->endPoint = $value->withUserInfo('');
    return $this;
  }

  /**
   * Sets a value indicating whether the client operates in test mode.
   * You can use it when submitting test queries to Akismet.
   * @param bool $value `true` to enable the test mode, otherwise `false`.
   * @return $this This instance.
   */
  function setTest(bool $value): self {
    $this->isTest = $value;
    return $this;
  }

  /**
   * Sets the user agent string to use when making requests.
   * @param string $value The new user agent.
   * @return $this This instance.
   */
  function setUserAgent(string $value): self {
    assert(mb_strlen($value) > 0);
    $this->userAgent = $value;
    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   */
  function submitHam(Comment $comment): void {
    $apiUrl = $this->getEndPoint();
    $endPoint = $this->http->createUri("{$apiUrl->getScheme()}://{$this->getApiKey()}.{$apiUrl->getAuthority()}{$apiUrl->getPath()}submit-ham");
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  function submitSpam(Comment $comment): void {
    $apiUrl = $this->getEndPoint();
    $endPoint = $this->http->createUri("{$apiUrl->getScheme()}://{$this->getApiKey()}.{$apiUrl->getAuthority()}{$apiUrl->getPath()}submit-spam");
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool `true` if the specified API key is valid, otherwise `false`.
   */
  function verifyKey(): bool {
    $apiUrl = $this->getEndPoint();
    $response = $this->fetch($apiUrl->withPath("{$apiUrl->getPath()}verify-key"), ['key' => $this->getApiKey()]);
    return ((string) $response->getBody()) == 'valid';
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param UriInterface $endPoint The URL of the end point to query.
   * @param array<string, string> $fields The fields describing the query body.
   * @return ResponseInterface The server response.
   * @throws ClientException An error occurred while querying the end point.
   */
  private function fetch(UriInterface $endPoint, array $fields = []): ResponseInterface {
    $bodyFields = array_merge(get_object_vars($this->getBlog()->jsonSerialize()), $fields);
    if ($this->isTest()) $bodyFields['is_test'] = '1';

    try {
      $request = $this->http->createRequest('POST', $endPoint)
        ->withBody($this->http->createStream(http_build_query($bodyFields, '', '&', PHP_QUERY_RFC1738)))
        ->withHeader('User-Agent', $this->getUserAgent());

      $this->dispatch(new RequestEvent($request));
      $response = $this->http->sendRequest($request);
      $this->dispatch(new ResponseEvent($response, $request));

      if ($response->hasHeader('X-akismet-debug-help')) throw new ClientException($response->getHeaderLine('X-akismet-debug-help'), $endPoint);
      return $response;
    }

    catch (\Throwable $e) {
      if ($e instanceof ClientException) throw $e;
      throw new ClientException($e->getMessage(), $endPoint, $e);
    }
  }
}
