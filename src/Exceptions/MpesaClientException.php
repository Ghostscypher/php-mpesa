<?php

namespace Hackdelta\Mpesa\Exceptions;

use Exception;

/**
 * Indicates client exceptions, that is an error in the request body of the client, this occurs if the
 * data passed the internal error check, and the client has sent a request to safaricom.
 * This is the 4xx error code series.
 * This will wrap around the guzzle client error exception
 */
class MpesaClientException extends Exception 
{
    protected string $error_body = '';
    protected int $status_code = 0;
    protected ?array $request_parameters = null;

    public function __construct(string $message, string $error_body, int $status_code, ?array $request_parameters = null) 
    {
        parent::__construct($message);

        $this->error_body = $error_body;
        $this->status_code = $status_code;
        $this->request_parameters = $request_parameters;
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function getErrorBody(): string
    {
        return $this->error_body;
    }

    // Get the request params e.g. headers, body, method, url
    public function getRequestParameters(): ?array
    {
        return $this->request_parameters;
    }

}