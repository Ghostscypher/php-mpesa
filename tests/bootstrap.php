<?php

// Used to bootstrap the test credentials
use Hackdelta\Mpesa\Utility\DotEnv;

// Autoload environment variables
(new DotEnv(__DIR__ . '/../.env'))->load();

