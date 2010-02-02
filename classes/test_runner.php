<?php

class Test_Runner {
	protected $test_files = array();

	public function __construct() {
	}

	protected function load() {
		$test_files = glob(APPPATH.'tests/*.php');
	}

	public function start() {
	}
}
