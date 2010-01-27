<?php

class SQLResult {
	protected $db;
	protected $result;

	public function __construct($db, $sql, $result = FALSE) {
		$this->db = $db;
		$this->sql = $sql;

		if ($result !== FALSE) {
			$this->result = $result;
		} else {
			$this->result = $this->db->query($sql);
		}
	}

	public function __destruct() {
		$this->result->finalize();
		unset($this->result);
	}

	public function reset() {
		$this->result->reset();
	}

	public function fetch() {
		return (object) $this->result->fetchArray(SQLITE3_ASSOC);
	}
}
