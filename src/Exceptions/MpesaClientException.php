<?php

namespace HackDelta\Mpesa\Exceptions;

/**
 * Indicates client exceptions, that is an error in the request body of the client, this occurs if the
 * data passed the internal error check, and the client has sent a request to safaricom.
 * This is the 4xx error code series.
 * This will wrap around the guzzle server error exception
 */
