<?php
declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{UriInterface};

/**
 * An exception caused by an error in a `Client` request.
 */
class ClientException extends \RuntimeException {

  /**
   * @var UriInterface|null The URL of the HTTP request or response that failed.
   */
  private $uri;

  /**
   * Creates a new client exception.
   * @param string $message A message describing the error.
   * @param UriInterface|null $uri The URL of the HTTP request or response that failed.
   * @param \Throwable|null $previous The previous exception used for the exception chaining.
   */
  function __construct(string $message, UriInterface $uri = null, \Throwable $previous = null) {
    parent::__construct($message, 0, $previous);
    $this->uri = $uri;
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  function __toString(): string {
    $values = "'{$this->getMessage()}'";
    if ($uri = $this->getUri()) $values .= ", uri: '$uri'";
    return static::class . "($values)";
  }

  /**
   * Gets the URL of the HTTP request or response that failed.
   * @return UriInterface|null The URL of the HTTP request or response that failed.
   */
  function getUri(): ?UriInterface {
    return $this->uri;
  }
}
