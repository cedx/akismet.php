<?php
/**
 * Implementation of the `akismet\Client` class.
 */
namespace akismet;

use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Psr7\{ServerRequest};
use Rx\{Observable};
use Rx\Subject\{Subject};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client {

  /**
   * @var string The HTTP header containing the Akismet error messages.
   */
  const DEBUG_HEADER = 'x-akismet-debug-help';

  /**
   * @var string The URL of the default API end point.
   */
  const DEFAULT_ENDPOINT = 'https://rest.akismet.com';

  /**
   * @var string The version number of this package.
   */
  const VERSION = '5.0.0';

  /**
   * @var string The Akismet API key.
   */
  private $apiKey;

  /**
   * @var Blog The front page or home URL of the instance making requests.
   */
  private $blog;

  /**
   * @var string The URL of the API end point.
   */
  private $endPoint = self::DEFAULT_ENDPOINT;

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

    $this->setAPIKey($apiKey);
    $this->setBlog($blog);
    $this->setUserAgent(sprintf('PHP/%s | Akismet/%s', preg_replace('/^(\d+(\.\d+){2}).*/', '$1', PHP_VERSION), static::VERSION));
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return bool A boolean value indicating whether it is spam.
   */
  public function checkComment(Comment $comment): bool {
    $serviceURL = parse_url($this->getEndPoint());
    $endPoint = sprintf('%s://%s.%s/1.1/comment-check', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    return $this->fetch($endPoint, (array) $comment->jsonSerialize()) == 'true';
  }

  /**
   * Gets the Akismet API key.
   * @return string The Akismet API key.
   */
  public function getAPIKey(): string {
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
   * @return string The URL of the API end point.
   */
  public function getEndPoint(): string {
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
  public function setAPIKey(string $value): self {
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
   * @param string $value The new URL of the API end point.
   * @return Client This instance.
   */
  public function setEndPoint(string $value) {
    $this->endPoint = $value;
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
   */
  public function submitHam(Comment $comment) {
    $serviceURL = parse_url($this->getEndPoint());
    $endPoint = sprintf('%s://%s.%s/1.1/submit-ham', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    $this->fetch($endPoint, (array) $comment->jsonSerialize());
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   */
  public function submitSpam(Comment $comment) {
    $serviceURL = parse_url($this->getEndPoint());
    $endPoint = sprintf('%s://%s.%s/1.1/submit-spam', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    $this->fetch($endPoint, (array) $comment->jsonSerialize());
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return bool A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey(): bool {
    $endPoint = $this->getEndPoint().'/1.1/verify-key';
    return $this->fetch($endPoint, ['key' => $this->getAPIKey()]) == 'valid';
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
    $blog = $this->getBlog();
    if (!mb_strlen($this->getAPIKey()) || !$blog) throw new \InvalidArgumentException('The API key or the blog URL is empty.');

    $bodyFields = array_merge((array) $blog->jsonSerialize(), $fields);
    if ($this->isTest()) $bodyFields['is_test'] = '1';

    try {
      $request = (new ServerRequest('POST', $endPoint))->withParsedBody($bodyFields);
      $this->onRequest->onNext($request);

      $response = (new HTTPClient())->send($request, [
        'form_params' => $request->getParsedBody(),
        'headers' => ['User-Agent' => $this->getUserAgent()]
      ]);

      $this->onResponse->onNext($response);
      if($response->hasHeader(static::DEBUG_HEADER))
        throw new \UnexpectedValueException($response->getHeader(static::DEBUG_HEADER)[0]);

      return (string) $response->getBody();
    }

    catch (\Throwable $e) {
      throw new \RuntimeException('An error occurred while querying the end point.');
    }
  }
}
