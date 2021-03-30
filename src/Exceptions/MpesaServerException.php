<?php

namespace HackDelta\Mpesa\Exceptions;

/**
 * This error occurs if the is a server error in the MPesa gateway
 * and is not the client's fault. This is the 5xx error code series.
 * and will wrap around theguzzle server error exception
*/