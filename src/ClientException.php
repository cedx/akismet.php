<?php namespace akismet;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * An exception caused by an error in a {@see Client} request.
 */
class ClientException extends \RuntimeException implements ClientExceptionInterface {}
