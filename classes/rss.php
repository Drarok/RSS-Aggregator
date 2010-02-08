<?php

class RSS {
	protected $name;
	protected $url;
	protected $use_cache;

	public function __construct($name, $url, $use_cache = TRUE) {
		$this->name = $name;
		$this->url = $url;
		$this->use_cache = $use_cache;
	}

	public function get_items() {
		// Initialise.
		$items = array();

		// Grab the data.
		$data = $this->get_rss_data();

		// Debugging info.
		Core::log('debug', 'Parsing %d bytes', strlen($data));
		
		// Parse the feed.
		$xml = simplexml_load_string($data, 'AdvancedXMLElement');

		foreach ($xml->entry as $entry) {
			$items[] = $entry;
		}

		Core::log('debug', 'Parsed %d items', count($items));

		return $items;
	}

	protected function get_rss_data() {
		// Always instantiate the cache.
		$cache = new Cache($this->name.'.xml');

		// Initialise the data.
		$data = FALSE;

		// Use the cache if allowed.
		if ($this->use_cache) {
			$data = $cache->read();
		}

		// No data? Fetch it.
		if (! (bool) $data) {
			Core::log('debug', 'Fetching %s RSS data from %s', $this->name, $this->url);
			$cache->write($data = file_get_contents($this->url));
		}

		return $data;
	}
}
