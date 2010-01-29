<?php

/**
 * This class is a compatibility wrapper and will only
 * get loaded if the sqlite3 extension is not loaded.
 */
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
		return new SQLite2Statement($this, $sql);
	}

	public function lastErrorMsg() {
	}
}

define('SQLITE3_ASSOC', SQLITE_ASSOC);
define('SQLITE3_NUM', SQLITE_NUM);
define('SQLITE3_BOTH', SQLITE_BOTH);

class SQLite3Result {
	protected $db;
	protected $sql;
	protected $result;

	public function __construct(SQLiteDatabase $db, $sql) {
		$this->db = $db;
		$this->sql = $sql;
	}

	public function fetchArray($mode = SQLITE3_BOTH) {
		if (! (bool) $this->result) {
			$this->result = $this->db->query($sql, $mode);
		}

		return $this->result->fetch();
	}

	public function reset() {
		if ((bool) $this->result) {
			sqlite_rewind($this->result);
		}
	}
}


define('SQLITE3_NULL', 0);
define('SQLITE3_INTEGER', 1);
define('SQLITE3_FLOAT', 2);
define('SQLITE3_TEXT', 4);

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

		return new SQLite2Result(
			$this->db,
			$sql
		);
	}
}
