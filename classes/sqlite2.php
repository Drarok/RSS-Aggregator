<?php

/**
 * This class is a compatibility wrapper.
 */
class SQLite2 {
	protected $db;

	public function __construct($path) {
		$this->db = new SQLiteDatabase($path);
	}

	public function __destruct() {
		unset($this->db);
	}

	public function query($sql) {
		return $this->db->query($sql);
	}

	public function exec($sql) {
		return $this->db->queryExec($sql);
	}

	public function prepare($sql) {
		return new SQLite2Statement($this, $sql);
	}

	public function lastErrorMsg() {
	}
}

class SQLite2Result {
}


define('SQLITE3_NULL', 0);
define('SQLITE3_INTEGER', 1);
define('SQLITE3_FLOAT', 2);
define('SQLITE3_TEXT', 3);

class SQLite2Statement {
	protected $db;
	protected $sql;
	protected $params = array();

	public function __construct($db, $sql) {
		$this->db = $db;
		$this->sql = $sql;
	}

	public function bindValue($key, $value, $type) {
		$this->params[$key] = array($value, $type);
	}

	/**
	 * Build SQL and return a result.
	 */
	public function execute() {
		$sql = $this->sql;
		foreach ($this->params as $key => $tuple) {
			list($value, $type) = $tuple;

			if ($type == SQLITE3_TEXT) {
				$value = sprintf('\'%s\'', sqlite_escape_string($value));
			}

			$sql = str_replace($key, $value, $sql);
		}

		return $this->db->query($sql);
	}
}
