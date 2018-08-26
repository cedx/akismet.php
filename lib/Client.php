<?php
declare(strict_types=1);
namespace Akismet;

use Evenement\{EventEmitterInterface, EventEmitterTrait};
use GuzzleHttp\{Client as HttpClient};
use GuzzleHttp\Exception\{RequestException};
use GuzzleHttp\Psr7\{Request, Uri};
use Psr\Http\Message\{UriInterface};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client implements EventEmitterInterface {
  use EventEmitterTrait;

  /**
   * @var string An event that is triggered when a request is made to the remote service.
   */
  const EVENT_REQUEST = 'request';

  /**
   * @var string An event that is triggered when a response is received from the remote service.
   */
  const EVENT_RESPONSE = 'response';

  /**
   * @var string The version number of this package.
   */
  const VERSION = '12.0.0';

  /**
   * @var string The Akismet API key.
   */
  private $apiKey;

  /**
   * @var Blog The front page or home URL of the instance making requests.
   */
  private $blog;

  /**
   * @var UriInterface The URL of the API end point.
   */
  private $endPoint;

  /**
   * @var bool Value indicating whether the client operates in test mode.
   */
  private $isTest = false;

  /**
   * @var string The user agent string to use when making requests.
   */
  private $userAgent;

  /**
   * Creates a new client.
   * @param string $apiKey The Akismet API key.
   * @param Blog $blog The front page or home URL of the instance making requests.
   * @param string $userAgent The user agent string to use when making requests.
   */
  function __construct(string $apiKey, Blog $blog, string $userAgent = '') {
    $this->apiKey = $apiKey;
    $this->blog = $blog;
    $this->userAgent = mb_strlen($userAgent) ? $userAgent : sprintf('PHP/%s | Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*$/', '$1', PHP_VERSION), static::VERSION);
    $this->setEndPoint(new Uri('https://rest.akismet.com'));
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool `true` if the specified comment is spam, otherwise `false`.
   */
  function checkComment(Comment $comment): bool {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = new Uri("{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/comment-check");
    return $this->fetch($endPoint, get_object_vars($comment->jsonSerialize())) == 'true';
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
   * @return self This instance.
   */
  function setEndPoint(UriInterface $value): self {
    $this->endPoint = $value;
    return $this;
  }

  /**
   * Sets a value indicating whether the client operates in test mode.
   * You can use it when submitting test queries to Akismet.
   * @param bool $value `true` to enable the test mode, otherwise `false`.
   * @return self This instance.
   */
  function setIsTest(bool $value): self {
    $this->isTest = $value;
    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   */
  function submitHam(Comment $comment): void {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = new Uri("{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/submit-ham");
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  function submitSpam(Comment $comment): void {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = new Uri("{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/submit-spam");
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool `true` if the specified API key is valid, otherwise `false`.
   */
  function verifyKey(): bool {
    $endPoint = $this->getEndPoint()->withPath('/1.1/verify-key');
    return $this->fetch($endPoint, ['key' => $this->getApiKey()]) == 'valid';
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param UriInterface $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return string The response body.
   * @throws ClientException An error occurred while querying the end point.
   */
  private function fetch(UriInterface $endPoint, array $fields = []): string {
    $bodyFields = array_merge(get_object_vars($this->getBlog()->jsonSerialize()), $fields);
    if ($this->isTest()) $bodyFields['is_test'] = '1';

    $body = http_build_query($bodyFields);
    $headers = [
      'content-type' => 'application/x-www-form-urlencoded',
      'user-agent' => $this->getUserAgent()
    ];

    $request = new Request('POST', $endPoint, $headers, $body);
    $this->emit(static::EVENT_REQUEST, [$request]);

    try { $response = (new HttpClient)->send($request); }
    catch (RequestException $e) { throw new ClientException($e->getMessage(), $endPoint, $e); }
    $this->emit(static::EVENT_RESPONSE, [$request, $response]);

    if($response->hasHeader('x-akismet-debug-help')) throw new ClientException($response->getHeader('x-akismet-debug-help')[0], $endPoint);
    return (string) $response->getBody();
  }
}
