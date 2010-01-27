<?php

define('EXT', '.php');
define('APPPATH', realpath('.').'/');

function log_msg() {
	$args = func_get_args();
	$msg = array_shift($args);

	if ((bool) $args) {
		$msg = vsprintf($msg, $args);
	}

	static $log_file;
	if (! $log_file) {
		$log_file = fopen('log.txt', 'a');
	}

	$msg = sprintf('[%s] %s', date('Y-m-d H:i:s'), $msg).PHP_EOL;

	fwrite($log_file, $msg);
}
