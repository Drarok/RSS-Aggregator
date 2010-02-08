<?php

// Don't automatically bootstrap.
define('NO_BOOTSTRAP', TRUE);

// Include the core, for autoload etc.
require_once('classes/core.php');

// Force at least info-level logging.
if (Core::config('config.log_level') < 3) {
	Core::set_config('config.log_level', 3);
}

// Ensure we output to the console.
Core::set_config('config.debug_mode', TRUE);

// Pass in command-line options.
foreach ($_SERVER['argv'] as $arg) {
	if (preg_match('/--(.+?)=(.+?)/i', $arg, $matches)) {
		list(, $key, $value) = $matches;

		// Assume config if none specified.
		if (strpos($key, '.') === FALSE) {
			$key = 'config.'.$key;
		}

		Core::log('info', 'Setting %s to %s', $key, $value);
		Core::set_config($key, $value);
	}
}

// Finally bootstrap.
Core::bootstrap();

// Start off a test runner.
$runner = new Test_Runner();
$runner->start();
