<?php

// Bootstrap the autoloader etc.
require_once('classes/core.php');

// Include this manually because it's a git submodule.
require_once('classes/ansi-colors/ansi_color.php');

$runner = new Test_Runner();
$runner->start();

echo ansi::cprintf('red', FALSE, 'This is a test of %s', 'colors');
