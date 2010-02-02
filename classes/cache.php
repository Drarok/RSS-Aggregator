<?php

class Cache {
	protected static $enabled;
	protected $path;

	public static function init() {
		self::$enabled = Core::config('config.cache_enabled', TRUE);
		Core::log('debug', 'Initialised Cache. Enabled = %s', self::$enabled ? 'TRUE' : 'FALSE');
	}

	public function __construct($name) {
		Core::log('debug', 'Creating new Cache object for %s', $name);
		$name = str_replace('/', '_', $name);
		$this->path = APPPATH.'cache/'.$name;
	}

	protected function valid() {
		// Should we even use the cache?
		if (! self::$enabled)
			return FALSE;

		// Can't be valid unless it exists.
		if (! file_exists($this->path))
			return FALSE;

		// Always assume an empty file is invalid.
		if (! filesize($this->path))
			return FALSE;

		// Check the modification time isn't earlier than now() - expiry.
		$mod_time = filemtime($this->path);
		$now = time();
		$expiry = Core::config('config.cache_expiry', 1200);
		$earliest = $now - $expiry;

		// Log detailed information in debug mode.
		Core::log(
			'debug',
			'Cache file time is %s, earliest valid is %s',
			date('Y-m-d H:i:s', $mod_time),
			date('Y-m-d H:i:s', $earliest)
		);

		// Compare the values.
		if ($mod_time < $earliest) {
			Core::log('info', 'Expiring cache file %s', $this->path);
			unlink($this->path);
			return FALSE;
		}

		// Must be ok!
		return TRUE;
	}

	public function read() {
		if (! $this->valid())
			return FALSE;

		return file_get_contents($this->path);
	}

	public function write($data) {
		if (! self::$enabled)
			return;

		@file_put_contents($this->path, $data);
	}
}

Cache::init();
