<?php

define('SQLITE3_NULL', 0);
define('SQLITE3_INTEGER', 1);
define('SQLITE3_FLOAT', 2);
define('SQLITE3_TEXT', 4);

class SQLite3Stmt {
	protected $db;
	protected $sql;
	protected $params = array();

	public function __construct(SQLite3 $db, $sql) {
		$this->db = $db;
		$this->sql = $sql;
	}

	public function bindValue($key, $value, $type) {
		$this->params[$key] = array($value, $type);
	}

	public function close() {
	}

	/**
	 * Build SQL and return a result.
	 */
	public function execute() {
		Core::log('debug', 'SQLite3Stmt::execute()');

		$sql = $this->sql;
		foreach ($this->params as $key => $tuple) {
			list($value, $type) = $tuple;

			if ($type == SQLITE3_TEXT) {
				$value = sprintf('\'%s\'', sqlite_escape_string($value));
			}

			$sql = str_replace($key, $value, $sql);
		}

		Core::log('debug', 'SQLite3Stmt::$sql = %s', $sql);

		return $this->db->query($sql);
	}
}
