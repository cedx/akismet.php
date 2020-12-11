<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\UriInterface;

/** An exception caused by an error in a `Client` request. */
class ClientException extends \RuntimeException {

	/** Creates a new client exception. */
	function __construct(string $message, private ?UriInterface $uri = null, ?\Throwable $previous = null) {
		parent::__construct($message, 0, $previous);
	}

	/** Gets the URL of the HTTP request or response that failed. */
	function getUri(): ?UriInterface {
		return $this->uri;
	}
}
