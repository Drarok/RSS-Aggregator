<?php

class RSS {
	protected $name;
	protected $url;

	public function __construct($name, $url) {
		$this->name = $name;
		$this->url = $url;
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
		// Use the cache if available.
		$cache = new Cache($this->name.'.xml');
		$data = $cache->read();

		if (! (bool) $data) {
			Core::log('debug', 'Fetching %s RSS data from %s', $this->name, $this->url);
			$cache->write($data = file_get_contents($this->url));
		}

		return $data;
	}
}
