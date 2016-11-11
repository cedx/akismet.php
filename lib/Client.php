<?php
/**
 * Implementation of the `akismet\Client` class.
 */
namespace akismet;

use GuzzleHttp\{Client as HTTPClient};
use Rx\{Observable, ObserverInterface};

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client {

  /**
   * @var string The URL of the remote service.
   */
  const SERVICE_URL = 'https://rest.akismet.com';

  /**
   * @var string The Akismet API key.
   */
  private $apiKey;

  /**
   * @var Blog The front page or home URL of the instance making requests.
   */
  private $blog;

  /**
   * @var bool Value indicating whether the client operates in test mode.
   */
  private $isTest;

  /**
   * @var string The user agent string to use when making requests. If possible, the user agent string should always have the following format: `Application Name/Version | Plugin Name/Version`.
   */
  private $userAgent;

  /**
   * Initializes a new instance of the class.
   * @param string $apiKey The Akismet API key used to query the service.
   * @param string|Blog $blog The front page or home URL transmitted when making requests.
   * @param array $options An object specifying additional values used to initialize this instance.
   * @throws \InvalidArgumentException The specified API key or blog URL is empty.
   */
  public function __construct(string $apiKey, $blog, array $options = []) {
    $this->apiKey = $apiKey;
    if (!mb_strlen($this->apiKey)) throw new \InvalidArgumentException('The specified API key is empty.');

    $this->blog = $blog instanceof Blog ? $blog : new Blog(['url' => $blog]);
    if (!mb_strlen($this->blog->getURL())) throw new \InvalidArgumentException('The specified blog URL is empty.');

    $this->isTest = isset($options['isTest']) && is_bool($options['isTest'])
      ? $options['isTest']
      : false;

    $this->userAgent = isset($options['userAgent']) && is_string($options['userAgent'])
      ? $options['userAgent']
      : sprintf('PHP/%s | Akismet/1.1.0', PHP_VERSION);
  }

  /**
   * Checks the specified comment against the service database, and returns a value indicating whether it is spam.
   * @param Comment $comment The comment to be checked.
   * @return Observable A boolean value indicating whether it is spam.
   */
  public function checkComment(Comment $comment) {
    $serviceURL = parse_url(static::SERVICE_URL);
    $endPoint = sprintf('%s://%s.%s/1.1/comment-check', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    return $this->queryService($endPoint, $comment->toJSON())->map(function($response) {
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
   * @return Blog The front page or home URL of the instance making requests.
   */
  public function getBlog(): Blog {
    return $this->blog;
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
   * You can use it when submitting test queries to Akismet.
   * @return bool `true` if the client operates in test mode, otherwise `false`.
   */
  public function isTest(): bool {
    return $this->isTest;
  }

  /**
   * Submits the specified comment that was incorrectly marked as spam but should not have been.
   * @param Comment $comment The comment to be submitted.
   * @return Observable Completes once the comment has been submitted.
   */
  public function submitHam(Comment $comment) {
    $serviceURL = parse_url(static::SERVICE_URL);
    $endPoint = sprintf('%s://%s.%s/1.1/submit-ham', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    return $this->queryService($endPoint, $comment->toJSON());
  }

  /**
   * Submits the specified comment that was not marked as spam but should have been.
   * @param Comment $comment The comment to be submitted.
   * @return Observable Completes once the comment has been submitted.
   */
  public function submitSpam(Comment $comment) {
    $serviceURL = parse_url(static::SERVICE_URL);
    $endPoint = sprintf('%s://%s.%s/1.1/submit-spam', $serviceURL['scheme'], $this->getAPIKey(), $serviceURL['host']);
    return $this->queryService($endPoint, $comment->toJSON());
  }

  /**
   * Checks the API key against the service database, and returns a value indicating whether it is valid.
   * @return Observable A boolean value indicating whether it is a valid API key.
   */
  public function verifyKey() {
    $endPoint = static::SERVICE_URL . '/1.1/verify-key';
    return $this->queryService($endPoint, ['key' => $this->getAPIKey()])->map(function($response) {
      return $response == 'valid';
    });
  }

  /**
   * Queries the service by posting the specified fields to a given end point, and returns the response as a string.
   * @param string $endPoint The URL of the end point to query.
   * @param array $fields The fields describing the query body.
   * @return Observable The response as string.
   */
  private function queryService($endPoint, array $fields = []) {
    $params = array_merge($this->getBlog()->toJSON(), $fields);
    if ($this->isTest()) $params['is_test'] = 'true';

    return Observable::create(function(ObserverInterface $observer) use($endPoint, $params) {
      try {
        $promise = (new HTTPClient())->postAsync($endPoint, ['form_params' => $params]);
        $response = $promise->then()->wait();

        $akismetHeader = 'x-akismet-debug-help';
        if($response->hasHeader($akismetHeader))
          throw new \UnexpectedValueException($response->getHeader($akismetHeader)[0]);

        $observer->onNext((string) $response->getBody());
        $observer->onCompleted();
      }

      catch(\Exception $e) {
        $observer->onError($e);
      }
    });
  }
}
