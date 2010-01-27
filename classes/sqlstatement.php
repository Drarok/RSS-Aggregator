<?php

class SQLStatement {
	protected $db;
	protected $sql;
	protected $statement;
	protected $params = array();

	public function __construct($db, $sql) {
		$this->db = $db;
		$this->sql = $sql;
		$this->statement = $this->db->prepare($this->sql);
		if ($this->statement === FALSE)
			throw new Exception('Failed to prepare statement: '.$sql);
	}

	public function __destruct() {
		$this->statement->close();
		unset($this->statement);
	}

	public function __set($key, $value) {
		if (is_null($value)) {
			$type = SQLITE3_NULL;
		} elseif (is_int($value)) {
			$type = SQLITE3_INTEGER;
		} elseif (is_float($value)) {
			$type = SQLITE3_FLOAT;
		} else {
			$type = SQLITE3_TEXT;
		}

		$this->statement->bindValue(':'.$key, $value, $type);
	}

	public function clear() {
		$this->statement->clear();
	}

	public function execute() {
		$this->statement->reset();
		return $this->statement->execute();
	}
}
