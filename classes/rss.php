<?php

class RSS {
	protected $name;
	protected $url;
	protected $items;

	public function __construct($name, $url) {
		$this->name = $name;
		$this->url = $url;
	}

	public function __get($key) {
		if ($key === 'items') {
			if (! (bool) $this->items)
				$this->fetch();

			return new ArrayIterator($this->items);
		}
	}

	public function fetch() {
		// Initialise.
		$this->items = array();

		// Grab the data.
		$data = $this->get_rss_data();

		// Debugging info.
		Core::log('debug', 'Parsing %d bytes', strlen($data));
		
		// Parse the feed.
		$xml = simplexml_load_string($data);

		foreach ($xml->entry as $entry) {
			$this->items[] = $entry;
		}

		Core::log('debug', 'Parsed %d items', count($this->items));
	}

	protected function get_rss_data() {
		// Initialise vars.
		$data = FALSE;
		$cache_path = APPPATH.'cache/'.$this->name.'.xml';

		// Should we use the cache?
		if (Core::config('config.enable_cache')) {
			// Is there a cached result for this feed?
			if (file_exists($cache_path)) {
				Core::log('debug', 'Fetching %s RSS data from cache', $this->name);
				$data = file_get_contents($cache_path);
			}
		}

		// If nothing fetched, get the real data.
		if (! (bool) $data) {
			Core::log('debug', 'Fetching %s RSS data from %s', $this->name, $this->url);
			$data = file_get_contents($this->url);

			// Should we store this in the cache?
			if (Core::config('config.enable_cache')) {
				file_put_contents($cache_path, $data);
			}
		}

		return $data;
	}
}
