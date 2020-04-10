<?php declare(strict_types=1);
namespace Akismet;

use Psr\Http\Message\{RequestInterface};
use Symfony\Contracts\EventDispatcher\{Event};

/** Represents the event parameter used for request events. */
class RequestEvent extends Event {

  /** @var RequestInterface The related HTTP request. */
  private RequestInterface $request;

  /**
   * Creates a new request event.
   * @param RequestInterface $request The related HTTP request.
   */
  function __construct(RequestInterface $request) {
    $this->request = $request;
  }

  /**
   * Gets the related HTTP request.
   * @return RequestInterface The related HTTP request.
   */
  function getRequest(): RequestInterface {
    return $this->request;
  }
}
