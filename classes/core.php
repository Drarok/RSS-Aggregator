<?php

set_time_limit(0);
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'on');

define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);
define('APPPATH', realpath('.').DS);

class Core {
	protected static $levels = array(
		'error' => 1,
		'info' => 2,
		'warning' => 3,
		'debug' => 4,
	);

	protected static $config = array();

	public static function autoload($class_name) {
		Core::log('debug', 'Autoloading %s', $class_name);

		$class_path = APPPATH.'classes/'.strtolower($class_name).EXT;
		if (file_exists($class_path)) {
			include_once($class_path);
		} else {
			echo 'Failed to load ', $class_name, "\n";
		}
	}

	protected static function load_sqlite3() {
		if (extension_loaded('sqlite3')) {
			Core::log('debug', 'sqlite3 extension already loaded - skipped');
			return TRUE;
		} else {
			Core::log('warning', 'sqlite3 extension not loaded - attempting dynamic load');

			if (self::is_win()) {
				$result = @dl('php_sqlite3.dll');
			} else {
				$result = @dl('sqlite3.so');
			}

			if ((bool) $result) {
				Core::log('debug', 'Loaded sqlite3 extension');
			} else {
				Core::log('debug', 'Failed to load sqlite3 extension');
			}

			return $result;
		}
	}

	protected static function load_sqlite2() {
		if (extension_loaded('sqlite')) {
			Core::log('debug', 'sqlite2 extension already loaded - skipped');
			return TRUE;
		} else {
			Core::log('warning', 'sqlite2 extension not loaded - attempting dynamic load');

			if (self::is_win()) {
				$result = @dl('php_sqlite.dll');
			} else {
				$result = @dl('sqlite.so');
			}

			if ((bool) $result) {
				Core::log('debug', 'Loaded sqlite2 extension');
			} else {
				Core::log('debug', 'Failed to load sqlite2 extension');
			}

			return $result;
		}
	}

	protected static function is_win() {
		return (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
	}

	public static function bootstrap() {
		// Check the default timezone, if none set we may get errors.
		if ($zone = @date_default_timezone_get() == 'UTC') {
			date_default_timezone_set('Europe/London');
		}

		// Allow configuration to specify which sqlite extension to use.
		$version = Core::config('config.sqlite_version');

		if ($version == 2) {
			if (self::load_sqlite2())
				return;
		} elseif ($version == 3) {
			if (self::load_sqlite3())
				return;
		} else {
			if (self::load_sqlite3())
				return;
		
			if (self::load_sqlite2())
				return;
		}

		throw new Exception('Unable to load SQLite extension');
	}

	public static function config($key, $default = FALSE) {
		// Break up the key passed in.
		list($file, $key) = explode('.', $key, 2);

		// If that config isn't loaded, attempt to.
		if (! array_key_exists($file, self::$config)) {
			$file_path = APPPATH.'config/'.$file.'.php';

			// No such file? Emulate an empty one.
			if (! file_exists($file_path)) {
				self::$config[$file] = array();
			} else {
				require_once($file_path);
				self::$config[$file] = isset($config)
					? $config
					: array();
			}
		}

		return array_key_exists($key, self::$config[$file])
			? self::$config[$file][$key]
			: $default;
	}

	public static function set_config($key, $value) {
		list($file, $key) = explode('.', $key, 2);
		self::$config[$file][$key] = $value;
	}

	public static function log($level, $message) {
		// Check it's a valid level.
		if (! array_key_exists($level, self::$levels))
			return;

		// Grab the level number.
		$level_num = self::$levels[$level];

		// Don't log if the level is above the configured one.
		if ($level_num > Core::config('config.log_level', 1))
			return;

		// Do printf-style formatting if required.
		$args = array_slice(func_get_args(), 2);
		if ((bool) count($args)) {
			$message = vsprintf($message, $args);
		}

		// Grab the microseconds, too.
		$micro = microtime(TRUE);
		$micro = substr(sprintf('%.4f', $micro - floor($micro)), 2);

		// Build the mesage.
		$message = sprintf(
			'%s.%s - [%s] %s',
			date('Y-m-d H:i:s'),
			$micro,
			$level,
			$message
		)."\n";

		// Output if we're debugging.
		if (Core::config('config.debug_mode'))
			echo $message;

		// Finally write it to the log.
		static $log_file;
		if (! (bool) $log_file) {
			$log_path = sprintf('%slogs/%s.txt', APPPATH, date('Y-m-d'));
			$log_file = fopen($log_path, 'a');
		}
		fwrite($log_file, $message);
	}
}

spl_autoload_register('Core::autoload');
Core::bootstrap();
