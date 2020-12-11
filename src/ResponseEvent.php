<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{RequestInterface, ResponseInterface};

/** Represents the event parameter used for response events. */
class ResponseEvent extends RequestEvent {

	/** Creates a new response event. */
	function __construct(private ResponseInterface $response, RequestInterface $request) {
		parent::__construct($request);
	}

	/** Gets the related HTTP response. */
	function getResponse(): ResponseInterface {
		return $this->response;
	}
}
