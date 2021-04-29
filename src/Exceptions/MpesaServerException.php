<?php

namespace HackDelta\Mpesa\Exceptions;

use Exception;

/**
 * This error occurs if the is a server error in the MPesa gateway
 * and is not the client's fault. This is the 5xx error code series.
 * and will wrap around the guzzle server error exception
 * 
 * This error will wrap around the GuzzleServerException
*/
class MpesaServerException extends Exception 
{
    protected string $error_body = '';
    protected int $status_code = 0;

    public function __construct(string $message, string $error_body, int $status_code) 
    {
        parent::__construct($message);

        $this->error_body = $error_body;
        $this->status_code = $status_code;
    }

    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    public function getErrorBody(): string
    {
        return $this->error_body;
    }

}