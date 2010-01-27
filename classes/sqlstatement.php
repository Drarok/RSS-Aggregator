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
		$types = array(
			SQLITE3_NULL 		=> 'SQLITE3_NULL',
			SQLITE3_INTEGER 	=> 'SQLITE3_INTEGER',
			SQLITE3_FLOAT		=> 'SQLITE3_FLOAT',
			SQLITE3_TEXT		=> 'SQLITE3_TEXT',
		);

		if (is_null($value)) {
			$type = SQLITE3_NULL;
		} elseif (is_int($value)) {
			$type = SQLITE3_INTEGER;
		} elseif (is_float($value)) {
			$type = SQLITE3_FLOAT;
		} else {
			$type = SQLITE3_TEXT;
		}

		Core::log('debug', 'Binding %s to %s as type %s', $key, $value, $types[$type]);
		$this->statement->bindValue(':'.$key, $value, $type);
		$this->params[$key] = $value;
	}

	public function clear() {
		$this->params = array();
		$this->statement->clear();
	}

	public function execute() {
		Core::log('debug', 'SQLStatement->execute(%s)', $this->sql);
		foreach ($this->params as $key => $value) {
			Core::log('debug', "\t".'%s => %s', $key, $value);
		}

		// Execute the statement.
		$result = $this->statement->execute();

		return new SQLResult($this->db, $this->sql, $result, $this->params);
	}
}
