<?php
/**
 * Implementation of the `akismet\Client` class.
 */
namespace akismet;

/**
 * Submits comments to the [Akismet](https://akismet.com) service.
 */
class Client {

  /**
   * @var string The Akismet API key.
   */
  private $apiKey;

  /**
   * @var Blog The front page or home URL of the instance making requests.
   */
  private $blog;

  /**
   * @var bool Value indicating whether the client operates in test mode. You can use it when submitting test queries to Akismet.
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
   */
  public function __construct(string $apiKey, $blog, array $options = []) {
    $this->apiKey = $apiKey;
    $this->blog = $blog instanceof Blog ? $blog : new Blog(['url' => 'blog']);

    $this->isTest = isset($options['isTest']) && is_bool($options['isTest'])
      ? $options['isTest']
      : false;

    $this->userAgent = isset($options['userAgent']) && is_string($options['userAgent'])
      ? $options['userAgent']
      : sprintf('PHP/%s | Akismet/1.0.0', PHP_VERSION);
  }
}
