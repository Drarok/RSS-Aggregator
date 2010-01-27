<?php

class config {
	protected static $data = array();

	public static function item($key, $default = FALSE) {
		// Break up the key passed in.
		list($file, $key) = explode('.', $key, 2);

		if (! array_key_exists($file, self::$data)) {
			$file_path = APPPATH.'config/'.$file.EXT;
			if (! file_exists($file_path)) {
				self::$data[$file] = array();
			} else {
				require_once($file_path);
				self::$data[$file] = isset($config)
					? $config
					: array();
			}
		}

		return array_key_exists($key, self::$data[$file])
			? self::$data[$file][$key]
			: $default;
	}
}
