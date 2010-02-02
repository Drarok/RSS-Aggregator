<?php

class Aggregate {
	protected $db;
	protected $insert;
	protected $next_key = 0;
	protected $entries = array();

	public function __construct() {
		$this->db = new SQLite3(':memory:');
		$this->db->exec(
			'CREATE TABLE "entry_order" ('
			.'"time" INTEGER, '
			.'"key" INTEGER'
			.')'
		);

		$this->db->exec(
			'CREATE INDEX "entry_order_time" ON "entry_order" ("time")'
		);

		$this->insert = new SQLStatement(
			$this->db,
			'INSERT INTO "entry_order" ("time", "key") VALUES (:time, :key)'
		);
	}

	public function __destruct() {
		unset($this->insert);
	}

	protected function insert($time, $key) {
		$this->insert->time = $time;
		$this->insert->key = $key;
		$this->insert->execute();
	}

	public function add_entry($entry) {
		Core::log('debug', 'Adding root entry \'%s\'', $entry->title);

		$this->insert(strtotime($entry->published), $this->next_key);
		$this->entries[$this->next_key] = $entry;
		++$this->next_key;
	}

	public function asXML() {
		// Build up a new RSS feed.
		$xml = new AdvancedXMLElement('<?xml version="1.0" encoding="utf-8" ?><feed xmlns="http://www.w3.org/2005/Atom" />');
		$xml->addChild('id', Core::config('config.url'));
		$xml->addChild('title', Core::config('config.title'));
		$link = $xml->addChild('link');
		$link->addAttribute('href', Core::config('config.url'));
		$link->addAttribute('rel', 'self');

		// Fetch each item from the array in order.
		$roots_result = new SQLResult(
			$this->db,
			'SELECT * FROM "entry_order" '
			.'ORDER BY "time" DESC'
		);

		// Set up a variable to keep track of the 'updated' field.
		$updated = FALSE;

		while ($root_row = $roots_result->fetch()) {
			$entry = $this->entries[(int) $root_row->key];

			// If our feed doesn't have an updated item yet, add one.
			if (! $updated) {
				$updated = (bool) $xml->addChild('updated', (string) $entry->published);
			}
			
			// Add the entry to the feed.
			$xml->addElement($entry);
		}

		unset($roots_result);
		
		// Strip any blank lines.
		$result = '';
		foreach (explode("\n", $xml->asXML()) as $line) {
			if ((bool) $line = trim($line))
				$result .= $line;
		}
		
		return $result;
	}
}
