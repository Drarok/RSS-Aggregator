<?php

abstract class Test_Case {
	protected $assertions = array(
		'pass' => array(),
		'fail' => array(),
	);

	public function __get($key) {
		if ($key === 'assertions') {
			return $this->assertions;
		}
	}

	protected function assert_equal($expected, $actual, $message = FALSE) {
		if ($expected !== $actual) {
			$key = 'fail';
			$color = 'red';
		} else {
			$key = 'pass';
			$color = 'green';
		}

		if ((bool) $message) {
			$message .= '. ';
		}

		$message .= sprintf(
			'Expected: %s. Actual: %s.',
			var_export($expected, TRUE),
			var_export($actual, TRUE)
		);

		$this->assertions[$key][] = ansi::csprintf($color, FALSE, $message);
	}

	public function setup() {
	}

	public function teardown() {
	}
}
