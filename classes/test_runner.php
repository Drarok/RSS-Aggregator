<?php

class Test_Runner {
	protected $tests = array();

	public function __construct() {
		$this->load();
	}

	protected function load() {
		foreach (glob(APPPATH.'tests/*.php') as $test_path) {
			$class_name = $this->transform($test_path);
			$this->tests[$class_name] = $test_path;
		}
	}

	/**
	 * Transform a class file path into its class name.
	 * This is only valid for test cases, so it's not
	 * available outside this class.
	 */
	protected function transform($class_path) {
		// Remove the path part.
		$class_name = basename($class_path, EXT);

		// Explode on underscores.
		$parts = explode('_', $class_name);

		// Remove the leading numbers.
		array_shift($parts);

		// Add the trailing 'test'
		array_push($parts, 'test');

		// Uppercase the words and return.
		return implode('_', array_map('ucfirst', $parts));
	}

	public function start() {
		foreach ($this->tests as $class_name => $class_path) {
			require_once($class_path);
			$test = new $class_name();
			try {
				$test->run();
			} catch (Exception $e) {
				echo $e->getMessage(), "\n";
			}
			unset($test);
		}
	}
}
