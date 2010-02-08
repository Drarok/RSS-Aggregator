<?php

define('SQLITE3_ASSOC', SQLITE_ASSOC);
define('SQLITE3_NUM', SQLITE_NUM);
define('SQLITE3_BOTH', SQLITE_BOTH);

class SQLite3Result {
	protected $db;
	protected $sql;
	protected $result;
	protected $mode;

	public function __construct(SQLiteDatabase $db, $sql) {
		$this->db = $db;
		$this->sql = $sql;
		$this->mode = SQLITE3_ASSOC;
		$this->result = $this->db->query($this->sql, $this->mode);
	}

	public function fetchArray($mode = SQLITE3_BOTH) {
		if ($mode !== $this->mode)
			throw new Exception('Invalid use of SQLite3Result::fetchArray()');

		return $this->result->fetch();
	}

	public function reset() {
		if ((bool) $this->result) {
			sqlite_rewind($this->result);
		}
	}
}
