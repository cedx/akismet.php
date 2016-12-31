<?php
/**
 * Implementation of the `akismet\Client` class.
 */
namespace akismet;

use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Psr7\{ServerRequest};
use Rx\{Observable, ObserverInterface};
use Rx\Subject\{Subject};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client implements \JsonSerializable {

  /**
   * @var string The HTTP header containing the Akismet error messages.
   */
  const DEBUG_HEADER = 'x-akismet-debug-help';

  /**
   * @var string The URL of the remote service.
   */
  const SERVICE_URL = 'https://rest.akismet.com';

  /**
   * @var string The Akismet API key.
   */
  private $apiKey = '';

  /**
   * @var Blog The front page or home URL of the instance making requests.
   */
  private $blog;

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
   * @param array $config Name-value pairs that will be used to initialize the object properties.
   */
  public function __construct(array $config = []) {
    $versions = static::getVersions();
    $this->onRequest = new Subject();
    $this->onResponse = new Subject();
    $this->userAgent = sprintf('PHP/%s | Akismet/%s', $versions->php, $versions->package);

    foreach ($config as $property => $value) {
      $setter = "set$property";
      if(method_exists($this, $setter)) $this->$setter($value);
    }
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
    $serviceURL = parse_url(static::SERVICE_URL);
    $endPoint = sprintf('%s://%s.%s/1.1/comment-check', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    return $this->fetch($endPoint, (array) $comment->jsonSerialize())->map(function($response) {
      return $response == 'true';
    });
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
      'apiKey' => $this->getAPIKey(),
      'blog' => ($blog = $this->getBlog()) ? get_class($blog) : null,
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
    else if (is_string($value)) $this->blog = new Blog(['url' => $value]);
    else $this->blog = null;

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
    $serviceURL = parse_url(static::SERVICE_URL);
    $endPoint = sprintf('%s://%s.%s/1.1/submit-ham', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    return $this->fetch($endPoint, (array) $comment->jsonSerialize());
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   * @return Observable Completes once the comment has been submitted.
   */
  public function submitSpam(Comment $comment): Observable {
    $serviceURL = parse_url(static::SERVICE_URL);
    $endPoint = sprintf('%s://%s.%s/1.1/submit-spam', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    return $this->fetch($endPoint, (array) $comment->jsonSerialize());
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return Observable A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey(): Observable {
    $endPoint = static::SERVICE_URL.'/1.1/verify-key';
    return $this->fetch($endPoint, ['key' => $this->getAPIKey()])->map(function($response) {
      return $response == 'valid';
    });
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param string $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return Observable The response as string.
   */
  private function fetch(string $endPoint, array $fields = []): Observable {
    $blog = $this->getBlog();
    if (!mb_strlen($this->getAPIKey()) || !$blog) return Observable::error(new \InvalidArgumentException('The API key or the blog URL is empty.'));

    $bodyFields = array_merge((array) $blog->jsonSerialize(), $fields);
    if ($this->isTest()) $bodyFields['is_test'] = '1';

    return Observable::create(function(ObserverInterface $observer) use($endPoint, $bodyFields) {
      try {
        $request = (new ServerRequest('POST', $endPoint))->withParsedBody($bodyFields);
        $this->onRequest->onNext($request);

        $promise = (new HTTPClient())->sendAsync($request, [
          'form_params' => $request->getParsedBody(),
          'headers' => ['User-Agent' => $this->getUserAgent()]
        ]);

        $response = $promise->then()->wait();
        $this->onResponse->onNext($response);

        if($response->hasHeader(static::DEBUG_HEADER))
          throw new \UnexpectedValueException($response->getHeader(static::DEBUG_HEADER)[0]);

        $observer->onNext((string) $response->getBody());
        $observer->onCompleted();
      }

      catch (\Throwable $e) {
        $observer->onError($e);
      }
    });
  }

  /**
   * Gets an object providing the version numbers of the package and the PHP runtime.
   * @return \stdClass An object providing some version numbers.
   */
  private static function getVersions(): \stdClass {
    static $versions;

    if (!isset($versions)) {
      $xml = @simplexml_load_file(__DIR__.'/../build.xml');
      $versions = new \stdClass();
      $versions->package = $xml ? (string) $xml->property[0]['value'] : '0.0.0';
      $versions->php = preg_replace('/^(\d+(\.\d+){2}).*/', '$1', PHP_VERSION);
    }

    return $versions;
  }
}
