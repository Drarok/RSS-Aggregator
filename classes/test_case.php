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

	protected function assert_equal($a, $b, $message = FALSE) {
		if ($a !== $b) {
			$key = 'fail';
			$color = 'red';
		} else {
			$key = 'pass';
			$color = 'green';
		}

		Core::log('debug', 'assert_equal(%s, %s) = %s [%s, %s]', $a, $b, $message, $key, $color);

		$this->assertions[$key][] = ansi::csprintf($color, FALSE, $message);
	}
}
