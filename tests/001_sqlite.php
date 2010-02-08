<?php

class Sqlite_Test extends Test_Case {
	protected $compat;

	public function setup() {
		$this->db = new SQLite3(':memory:');

		if ($this->compat = defined('SQLITE3_COMPAT_WRAPPER')) {
			Core::log('info', 'sqlite wrapper in place');
		} else {
			Core::log('info', 'sqlite wrapper not in place');
		}
	}

	protected function create_test_data() {
		// Create a table.
		$this->db->query('CREATE TABLE "test" ("id" INTEGER PRIMARY KEY, "name" TEXT)');

		// Insert 10 rows.
		for ($x = 1; $x <= 10; ++$x)
			$this->db->query(sprintf('INSERT INTO "test" ("name") VALUES (\'test %d\')', $x));
	}

	protected function get_test_data() {
		$result = $this->db->query('SELECT * FROM "test"');

		$rows = array();

		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$rows[] = $row;
		}

		return array($result, $rows);
	}

	public function insert_and_select_test() {
		// Get some data to work with.
		$this->create_test_data();

		// Get the rows.
		list($result, $rows) = $this->get_test_data();

		// Check the result class type.
		$this->assert_equal('SQLite3Result', get_class($result), 'Result type');

		// Check how many rows there are.
		$this->assert_equal(10, count($rows), 'Row count');
	}

	public function prepared_statement_test() {
		// Get some data.
		$this->create_test_data();

		// Prepare an insert.
		$stmt = $this->db->prepare('INSERT INTO "test" ("name") VALUES (:name)');

		// Check the type.
		$this->assert_equal('SQLite3Stmt', get_class($stmt), 'Prepared statement class name');

		// Run 10 times.
		for ($x = 11; $x <= 20; ++$x) {
			$stmt->bindValue(':name', sprintf('Prepared %d', $x), SQLITE3_TEXT);
			$stmt->execute();
		}

		// Get the rows.
		list($result, $rows) = $this->get_test_data();

		// We should now have 20 rows.
		$this->assert_equal(20, count($rows), 'Row count');
	}
}
