<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\EventDispatcher\Event;

/** Represents the event parameter used for request events. */
class RequestEvent extends Event {

	/** Creates a new request event. */
	function __construct(private RequestInterface $request) {}

	/** Gets the related HTTP request. */
	function getRequest(): RequestInterface {
		return $this->request;
	}
}
