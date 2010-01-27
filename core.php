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

class Core {
	protected static $levels = array(
		'error' => 1,
		'info' => 2,
		'warning' => 3,
		'debug' => 4,
	);

	public static function log($level, $message) {
		echo 'Core::log(', $level, ', ', $message, ")\n";
		// Check it's a valid level.
		if (! array_key_exists($level, self::$levels))
			return;

		// Grab the level number.
		$level_num = self::$levels[$level];

		// Don't log if the level is above the configured one.
		if ($level_num > config::item('config.log_level', 1))
			return;

		// Do printf-style formatting if required.
		$args = array_slice(func_get_args(), 2);
		if ((bool) count($args)) {
			$message = vsprintf($message, $args);
		}

		// Build the mesage.
		$message = sprintf(
			'%s - [%s] %s',
			date('Y-m-d H:i:s'),
			$level,
			$message
		)."\n";

		// Output if we're debugging.
		if (config::item('config.debug_mode'))
			echo $message;

		// Finally write it to the log.
		static $log_file;
		if (! (bool) $log_file) {
			$log_file = fopen('log.txt', 'a');
		}
		fwrite($log_file, $message);
	}
}
