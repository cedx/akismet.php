<?php
declare(strict_types=1);
namespace Akismet;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};

/**
 * An exception caused by an error in a `Client` request.
 */
class ClientException extends \RuntimeException {

  /**
   * @var Uri The URL of the HTTP request or response that failed.
   */
  private $uri;

  /**
   * Creates a new client exception.
   * @param string $message A message describing the error.
   * @param string|UriInterface $uri The URL of the HTTP request or response that failed.
   * @param \Throwable $previous The previous exception used for the exception chaining.
   */
  public function __construct($message, $uri = null, \Throwable $previous = null) {
    parent::__construct($message, 0, $previous);
    $this->uri = is_string($uri) ? new Uri($uri) : $uri;
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $values = "'{$this->getMessage()}'";
    if ($uri = $this->getUri()) $values .= ", uri: '$uri'";
    return static::class . "($values)";
  }

  /**
   * Gets the URL of the HTTP request or response that failed.
   * @return UriInterface The URL of the HTTP request or response that failed.
   */
  public function getUri(): ?UriInterface {
    return $this->uri;
  }
}
