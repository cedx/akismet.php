<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\UriInterface;

/** An exception caused by an error in a `Client` request. */
class ClientException extends \RuntimeException {

	/**
	 * Creates a new client exception.
	 * @param string $message A message describing the error.
	 * @param UriInterface|null $uri The URL of the HTTP request or response that failed.
	 * @param \Throwable|null $previous The previous exception used for the exception chaining.
	 */
	function __construct(string $message, private ?UriInterface $uri = null, ?\Throwable $previous = null) {
		parent::__construct($message, 0, $previous);
	}

	/**
	 * Gets the URL of the HTTP request or response that failed.
	 * @return UriInterface|null The URL of the HTTP request or response that failed.
	 */
	function getUri(): ?UriInterface {
		return $this->uri;
	}
}
