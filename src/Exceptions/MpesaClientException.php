<?php

namespace HackDelta\Mpesa\Exceptions;

use Exception;

/**
 * Indicates client exceptions, that is an error in the request body of the client, this occurs if the
 * data passed the internal error check, and the client has sent a request to safaricom.
 * This is the 4xx error code series.
 * This will wrap around the guzzle client error exception
 */
class MpesaClientException extends Exception {

    public function __construct(string $message = "") {
        parent::__construct($message);
    }

}