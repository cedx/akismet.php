<?php declare(strict_types=1);
namespace Akismet\Http;

use Psr\Http\Message\{RequestInterface, ResponseInterface};

/** Represents the event parameter used for request events. */
class ResponseEvent extends RequestEvent {

  /** @var ResponseInterface The related HTTP response. */
  private $response;

  /**
   * Creates a new event parameter.
   * @param RequestInterface $request The related HTTP request.
   * @param ResponseInterface $response The related HTTP response.
   */
  function __construct(RequestInterface $request, ResponseInterface $response) {
    parent::__construct($request);
    $this->response = $response;
  }

  /**
   * Gets the related HTTP response.
   * @return ResponseInterface The related HTTP response.
   */
  function getResponse(): ResponseInterface {
    return $this->response;
  }
}
