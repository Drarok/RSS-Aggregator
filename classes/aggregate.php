<?php

class Aggregate {
	protected $db;
	protected $insert;

	public function __construct() {
		$this->db = new SQLite3(':memory:');
		$this->db->exec(
			'CREATE TABLE "entries" ('
			.'"id" INTEGER PRIMARY KEY, '
			.'"parent_id" INTEGER NULL, '
			.'"time" INTEGER, '
			.'"name" STRING, '
			.'"value" STRING'
			.')'
		);

		$this->db->exec(
			'CREATE INDEX "entries_parent_id" ON "entries" ("parent_id")'
		);
	}

	public function __destruct() {
		if ((bool) $this->insert)
			unset($this->insert);
	}

	public function __get($key) {
		if ($key === 'items') {
			return $this->get_items();
		}
	}

	protected function get_items() {
		// Build up a new RSS feed.
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><feed />');
		$xml->addChild('id', Core::config('config.url'));
		$xml->addChild('title', Core::config('config.title'));

		$roots_result = new SQLResult(
			$this->db,
			'SELECT * FROM "entries" '
			.'WHERE "parent_id" IS NULL '
			.'ORDER BY "time" DESC'
		);

		while ($root_row = $roots_result->fetch()) {
			$entry = $xml->addChild('entry');
			$entry->addChild('title', $root_row->name);
		}

		unset($roots_result);

		return $xml->asXML();
	}

	protected function prepare() {
		$this->insert = new SQLStatement(
			$this->db,
			'INSERT INTO "entries" '
			.'("parent_id", "time", "name", "value") '
			.'VALUES (:parent_id, :time, :name, :value)'
		);
	}

	protected function insert($parent_id, $time, $name, $value = NULL) {
		$this->insert->parent_id = $parent_id;
		$this->insert->time = $time;
		$this->insert->name = $name;
		$this->insert->value = $value;
		$this->insert->execute();
		return $this->db->lastInsertRowID();
	}

	public function add_entry($entry) {
		if (! (bool) $this->insert) {
			$this->prepare();
		}

		Core::log('debug', 'Adding root entry \'%s\'', $entry->title);
		$entry_id = $this->insert(NULL, strtotime($entry->published), $entry->title, NULL);

		foreach ($entry->link as $key => $link) {
			Core::log('debug', 'Link %s: %s', $key, $link);
			foreach ($link->attributes() as $attr => $attrval) {
				Core::log('debug', "\t".'%s: %s', $attr, $attrval);
			}
		}

		$link_num = 0;
		foreach ($entry->link as $link) {
			Core::log('debug', 'Appending link %d', $link_num++);
			$link_id = $this->insert($entry_id, NULL, 'link', NULL);
			foreach ($link->attributes() as $attrname => $attrval) {
				$this->insert($link_id, NULL, $attrname, $attrval);
			}
		}
	}
}
