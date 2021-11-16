<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{RequestInterface, ResponseInterface};

/** Represents the event parameter used for response events. */
class ResponseEvent extends RequestEvent {

	/** @var ResponseInterface The related HTTP response. */
	private ResponseInterface $response;

	/**
	 * Creates a new response event.
	 * @param ResponseInterface $response The related HTTP response.
	 * @param RequestInterface $request The request that triggered this response.
	 */
	function __construct(ResponseInterface $response, RequestInterface $request) {
		parent::__construct($request);
		$this->response = $response;
	}

	/** Gets the related HTTP response. */
	function getResponse(): ResponseInterface {
		return $this->response;
	}
}
