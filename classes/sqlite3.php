<?php

/**
 * This class is a compatibility wrapper and will only
 * get loaded if the sqlite3 extension is not loaded.
 */
define('SQLITE3_COMPAT_WRAPPER', TRUE);

class SQLite3 {
	protected $db;

	public function __construct($path) {
		$this->db = new SQLiteDatabase($path);
	}

	public function __destruct() {
		unset($this->db);
	}

	public function query($sql) {
		return new SQLite3Result(
			$this->db,
			$sql
		);
	}

	public function exec($sql) {
		return $this->db->queryExec($sql);
	}

	public function prepare($sql) {
		return new SQLite3Stmt($this, $sql);
	}

	public function lastErrorMsg() {
	}
}
