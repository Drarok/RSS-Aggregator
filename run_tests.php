<?php

// Bootstrap the autoloader etc.
require_once('classes/core.php');

// Force at least info-level logging.
if (Core::config('config.log_level') < 3)
	Core::set_config('config.log_level', 3);

// Ensure we output to the console.
Core::set_config('config.debug_mode', TRUE);

// Start off a test runner.
$runner = new Test_Runner();
$runner->start();
