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
class MpesaServerException extends Exception {

    public function __construct(string $message = "") {
        parent::__construct($message);
    }

}