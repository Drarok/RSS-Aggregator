<?php

abstract class Test_Case {
	public function __construct() {
		Core::log('debug', 'Instantiated test \'%s\'', get_class($this));
	}

	abstract public function run();
}
