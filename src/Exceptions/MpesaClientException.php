<?php

namespace HackDelta\Mpesa\Exceptions;

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

    public function __construct(string $message, string $error_body, int $status_code) 
    {
        parent::__construct($message);

        $this->error_body = $error_body;
        $this->status_code = $status_code;
    }

    

}