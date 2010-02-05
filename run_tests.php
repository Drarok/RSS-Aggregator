<?php

// Bootstrap the autoloader etc.
require_once('classes/core.php');

// Include this manually because it's a git submodule.
require_once('classes/ansi-colors/ansi_color.php');

// Force debug-level logging.
Core::set_config('config.debug_mode', TRUE);
Core::set_config('config.log_level', 4);

// Start off a test runner.
$runner = new Test_Runner();
$runner->start();
