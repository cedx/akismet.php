<?php
declare(strict_types=1);
namespace Akismet;

use Evenement\{EventEmitterTrait};
use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Psr7\{Request, Uri};
use Psr\Http\Message\{UriInterface};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client {
  use EventEmitterTrait;

  /**
   * @var string The HTTP header containing the Akismet error messages.
   */
  const DEBUG_HEADER = 'X-akismet-debug-help';

  /**
   * @var string The URL of the default API end point.
   */
  const DEFAULT_ENDPOINT = 'https://rest.akismet.com';

  /**
   * @var string The version number of this package.
   */
  const VERSION = '10.0.0';

  /**
   * @var string The Akismet API key.
   */
  private $apiKey;

  /**
   * @var Blog The front page or home URL of the instance making requests.
   */
  private $blog;

  /**
   * @var Uri The URL of the API end point.
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
   * Initializes a new instance of the class.
   * @param string $apiKey The Akismet API key.
   * @param Blog|string $blog The front page or home URL of the instance making requests.
   * @param string $userAgent The user agent string to use when making requests.
   */
  public function __construct(string $apiKey, $blog, string $userAgent = '') {
    $this->apiKey = $apiKey;
    $this->blog = is_string($blog) ? new Blog($blog) : $blog;
    $this->userAgent = mb_strlen($userAgent) ? $userAgent : sprintf('PHP/%s | Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*/', '$1', PHP_VERSION), static::VERSION);
    $this->setEndPoint(static::DEFAULT_ENDPOINT);
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool `true` if the specified comment is spam, otherwise `false`.
   */
  public function checkComment(Comment $comment): bool {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/comment-check";
    return $this->fetch($endPoint, get_object_vars($comment->jsonSerialize())) == 'true';
  }

  /**
   * Gets the Akismet API key.
   * @return string The Akismet API key.
   */
  public function getApiKey(): string {
    return $this->apiKey;
  }

  /**
   * Gets the front page or home URL of the instance making requests.
   * @return Blog The front page or home URL.
   */
  public function getBlog(): Blog {
    return $this->blog;
  }

  /**
   * Gets the URL of the API end point.
   * @return UriInterface The URL of the API end point.
   */
  public function getEndPoint() {
    return $this->endPoint;
  }

  /**
   * Gets the user agent string to use when making requests.
   * If possible, the user agent string should always have the following format: `Application Name/Version | Plugin Name/Version`.
   * @return string The user agent string to use when making requests.
   */
  public function getUserAgent(): string {
    return $this->userAgent;
  }

  /**
   * Gets a value indicating whether the client operates in test mode.
   * @return bool `true` if the client operates in test mode, otherwise `false`.
   */
  public function isTest(): bool {
    return $this->isTest;
  }

  /**
   * Sets the URL of the API end point.
   * @param string|UriInterface $value The new URL of the API end point.
   * @return Client This instance.
   */
  public function setEndPoint($value): self {
    $this->endPoint = is_string($value) ? new Uri($value) : $value;
    return $this;
  }

  /**
   * Sets a value indicating whether the client operates in test mode.
   * You can use it when submitting test queries to Akismet.
   * @param bool $value `true` to enable the test mode, otherwise `false`.
   * @return Client This instance.
   */
  public function setIsTest(bool $value): self {
    $this->isTest = $value;
    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitHam(Comment $comment): void {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/submit-ham";
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitSpam(Comment $comment): void {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/submit-spam";
    $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool `true` if the specified API key is valid, otherwise `false`.
   */
  public function verifyKey(): bool {
    $endPoint = (string) $this->getEndPoint()->withPath('/1.1/verify-key');
    return $this->fetch($endPoint, ['key' => $this->getApiKey()]) == 'valid';
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param string $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return string The response body.
   * @throws \InvalidArgumentException The API key or the blog URL is empty.
   * @throws \RuntimeException An error occurred while querying the end point.
   */
  private function fetch(string $endPoint, array $fields = []): string {
    try {
      $bodyFields = array_merge(get_object_vars($this->getBlog()->jsonSerialize()), $fields);
      if ($this->isTest()) $bodyFields['is_test'] = '1';

      $body = http_build_query($bodyFields);
      $headers = [
        'Content-Length' => strlen($body),
        'Content-Type' => 'application/x-www-form-urlencoded',
        'User-Agent' => $this->getUserAgent()
      ];

      $request = new Request('POST', $endPoint, $headers, $body);
      $this->emit('request', [$request]);

      $response = (new HTTPClient())->send($request);
      $this->emit('reponse', [$response]);

      if($response->hasHeader(static::DEBUG_HEADER))
        throw new \UnexpectedValueException($response->getHeader(static::DEBUG_HEADER)[0]);

      return (string) $response->getBody();
    }

    catch (\Throwable $e) {
      throw new \RuntimeException('An error occurred while querying the end point.', 0, $e);
    }
  }
}
