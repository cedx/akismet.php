<?php declare(strict_types=1);
namespace Akismet\Http;

use function GuzzleHttp\Psr7\{build_query};
use Akismet\{Blog, CheckResult, Comment};
use GuzzleHttp\{Client as HttpClient};
use GuzzleHttp\Psr7\{Request, Uri, UriResolver};
use League\Event\{Emitter};
use Psr\Http\Message\{ResponseInterface, UriInterface};

/** Submits comments to the [Akismet](https://akismet.com) service. */
class Client extends Emitter {

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

  /** @var bool Value indicating whether the client operates in test mode. */
  private bool $isTest = false;

  /** @var string The user agent string to use when making requests. */
  private string $userAgent;

  /**
   * Creates a new client.
   * @param string $apiKey The Akismet API key.
   * @param Blog $blog The front page or home URL of the instance making requests.
   * @param string $userAgent The user agent string to use when making requests.
   */
  function __construct(string $apiKey, Blog $blog, string $userAgent = '') {
    $this->apiKey = $apiKey;
    $this->blog = $blog;
    $this->endPoint = new Uri('https://rest.akismet.com/1.1/');
    $this->userAgent = mb_strlen($userAgent)
      ? $userAgent
      : sprintf('PHP/%s | Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*$/', '$1', PHP_VERSION), require __DIR__.'/../version.g.php');
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return string A `CheckResult` value indicating whether the specified comment is spam.
   */
  function checkComment(Comment $comment): string {
    $apiUrl = $this->getEndPoint();
    $host = $apiUrl->getHost() . (($port = $apiUrl->getPort()) ? ":$port" : '');
    $endPoint = new Uri("{$apiUrl->getScheme()}://{$this->getApiKey()}.$host{$apiUrl->getPath()}");

    $response = $this->fetch(UriResolver::resolve($endPoint, new Uri('comment-check')), get_object_vars($comment->jsonSerialize()));
    if (((string) $response->getBody()) == 'false') return CheckResult::isHam;

    $header = $response->getHeader('X-akismet-pro-tip');
    return count($header) && $header[0] == 'discard' ? CheckResult::isPervasiveSpam : CheckResult::isSpam;
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
    $this->endPoint = $value;
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
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   */
  function submitHam(Comment $comment): void {
    $apiUrl = $this->getEndPoint();
    $host = $apiUrl->getHost() . (($port = $apiUrl->getPort()) ? ":$port" : '');
    $endPoint = new Uri("{$apiUrl->getScheme()}://{$this->getApiKey()}.$host{$apiUrl->getPath()}");
    $this->fetch(UriResolver::resolve($endPoint, new Uri('submit-ham')), get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  function submitSpam(Comment $comment): void {
    $apiUrl = $this->getEndPoint();
    $host = $apiUrl->getHost() . (($port = $apiUrl->getPort()) ? ":$port" : '');
    $endPoint = new Uri("{$apiUrl->getScheme()}://{$this->getApiKey()}.$host{$apiUrl->getPath()}");
    $this->fetch(UriResolver::resolve($endPoint, new Uri('submit-spam')), get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool `true` if the specified API key is valid, otherwise `false`.
   */
  function verifyKey(): bool {
    $response = $this->fetch(UriResolver::resolve($this->getEndPoint(), new Uri('verify-key')), ['key' => $this->getApiKey()]);
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

    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded',
      'User-Agent' => $this->getUserAgent()
    ];

    try {
      $request = new Request('POST', $endPoint, $headers, build_query($bodyFields));
      $this->emit(new RequestEvent($request));

      $response = (new HttpClient)->send($request);
      $this->emit(new ResponseEvent($response, $request));

      if ($response->hasHeader('X-akismet-debug-help')) throw new ClientException($response->getHeader('X-akismet-debug-help')[0], $endPoint);
      return $response;
    }

    catch (\Throwable $e) {
      if ($e instanceof ClientException) throw $e;
      throw new ClientException($e->getMessage(), $endPoint, $e);
    }
  }
}
