<?php
declare(strict_types=1);
namespace Akismet;

use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Psr7\{Request, Uri};
use Psr\Http\Message\{UriInterface};
use Rx\{Observable};
use Rx\Subject\{Subject};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client implements \JsonSerializable {

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
  const VERSION = '8.0.0';

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
   * @var Subject The handler of "request" events.
   */
  private $onRequest;

  /**
   * @var Subject The handler of "response" events.
   */
  private $onResponse;

  /**
   * @var string The user agent string to use when making requests.
   */
  private $userAgent;

  /**
   * Initializes a new instance of the class.
   * @param string $apiKey The Akismet API key.
   * @param Blog|string $blog The front page or home URL of the instance making requests.
   */
  public function __construct(string $apiKey = '', $blog = null) {
    $this->onRequest = new Subject();
    $this->onResponse = new Subject();

    $this->setApiKey($apiKey);
    $this->setBlog($blog);
    $this->setEndPoint(static::DEFAULT_ENDPOINT);
    $this->setUserAgent(sprintf('PHP/%s | Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*/', '$1', PHP_VERSION), static::VERSION));
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return Observable A boolean value indicating whether it is spam.
   */
  public function checkComment(Comment $comment): Observable {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/comment-check";
    return $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()))->map(function($response) {
      return $response == 'true';
    });
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
  public function getBlog() {
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
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) [
      'apiKey' => $this->getApiKey(),
      'blog' => ($blog = $this->getBlog()) ? get_class($blog) : null,
      'endPoint' => ($endPoint = $this->getEndPoint()) ? (string) $endPoint : null,
      'isTest' => $this->isTest(),
      'userAgent' => $this->getUserAgent()
    ];
  }

  /**
   * Gets the stream of "request" events.
   * @return Observable The stream of "request" events.
   */
  public function onRequest(): Observable {
    return $this->onRequest->asObservable();
  }

  /**
   * Gets the stream of "response" events.
   * @return Observable The stream of "response" events.
   */
  public function onResponse(): Observable {
    return $this->onResponse->asObservable();
  }

  /**
   * Sets the Akismet API key.
   * @param string $value The new API key.
   * @return Client This instance.
   */
  public function setApiKey(string $value): self {
    $this->apiKey = $value;
    return $this;
  }

  /**
   * Sets the front page or home URL of the instance making requests.
   * @param Blog|string $value The new front page or home URL.
   * @return Client This instance.
   */
  public function setBlog($value): self {
    if ($value instanceof Blog) $this->blog = $value;
    else if (is_string($value)) $this->blog = new Blog($value);
    else $this->blog = null;

    return $this;
  }

  /**
   * Sets the URL of the API end point.
   * @param string|UriInterface $value The new URL of the API end point.
   * @return Client This instance.
   */
  public function setEndPoint($value): self {
    if ($value instanceof UriInterface) $this->endPoint = $value;
    else if (is_string($value)) $this->endPoint = new Uri($value);
    else $this->endPoint = null;

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
   * Sets the user agent string to use when making requests.
   * If possible, the user agent string should always have the following format: `Application Name/Version | Plugin Name/Version`.
   * @param string $value The new user agent string.
   * @return Client This instance.
   */
  public function setUserAgent(string $value): self {
    $this->userAgent = $value;
    return $this;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   * @return Observable Completes once the comment has been submitted.
   */
  public function submitHam(Comment $comment): Observable {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/submit-ham";
    return $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   * @return Observable Completes once the comment has been submitted.
   */
  public function submitSpam(Comment $comment): Observable {
    $serviceUrl = parse_url((string) $this->getEndPoint());
    $endPoint = "{$serviceUrl['scheme']}://{$this->getApiKey()}.{$serviceUrl['host']}/1.1/submit-spam";
    return $this->fetch($endPoint, get_object_vars($comment->jsonSerialize()));
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return Observable A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey(): Observable {
    $endPoint = (string) $this->getEndPoint()->withPath('/1.1/verify-key');
    return $this->fetch($endPoint, ['key' => $this->getApiKey()])->map(function($response) {
      return $response == 'valid';
    });
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param string $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return Observable The response body as string.
   */
  private function fetch(string $endPoint, array $fields = []): Observable {
    $blog = $this->getBlog();
    if (!mb_strlen($this->getApiKey()) || !$blog)
      return Observable::error(new \InvalidArgumentException('The API key or the blog URL is empty.'));

    $bodyFields = array_merge(get_object_vars($blog->jsonSerialize()), $fields);
    if ($this->isTest()) $bodyFields['is_test'] = '1';

    $request = http_build_query($bodyFields);
    $headers = [
      'Content-Length' => strlen($request),
      'Content-Type' => 'application/x-www-form-urlencoded',
      'User-Agent' => $this->getUserAgent()
    ];

    $this->onRequest->onNext(new Request('POST', $endPoint, $headers, $request));
    return Http::post($endPoint, $request, $headers)->includeResponse()->map(function($data) {
      /** @var \React\HttpClient\Response $response */
      list($body, $response) = $data;
      $headers = $response->getHeaders();
      $this->onResponse->onNext(new Response($response->getCode(), $headers, $body));
      if (in_array(static::DEBUG_HEADER, $headers)) throw new \UnexpectedValueException($headers[static::DEBUG_HEADER]);
      return $body;
    });
  }
}
