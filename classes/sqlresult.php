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

		if (! (bool) $this->result) {
			$this->result = FALSE;
			$msg = $this->db->lastErrorMsg();
			if ($result === FALSE) {
				throw new Exception('Query failed: '.$msg.'['.$sql.']');
			} else {
				throw new Exception('Statement failed: '.$msg.'['.$sql.']');
			}
		}
	}

	public function __destruct() {
		if ((bool) $this->result) {
			Core::log('debug', 'Destroying SQLResult');
			$this->result->finalize();
			unset($this->result);
		}
	}

	public function reset() {
		$this->result->reset();
	}

	public function fetch() {
		$result = $this->result->fetchArray(SQLITE3_ASSOC);

		if ($result === FALSE)
			return FALSE;

		return (object) $result;
	}
}
