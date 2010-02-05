<?php

class Test_Runner {
	protected $tests = array();
	protected $assertions = array(
		'pass' => array(),
		'fail' => array(),
	);

	public function __construct() {
		$this->load();
	}

	/**
	 * Load each test file and work out its class name.
	 */
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

	// Begin the test procedure.
	public function start() {
		foreach ($this->tests as $class_name => $class_path) {
			$this->run_test($class_name, $class_path);
		}

		var_dump($this->assertions);
	}

	/**
	 * Instantiate and run a single test case.
	 */
	protected function run_test($class_name, $class_path) {
		require_once($class_path);

		Core::log('debug', 'Instantiating test \'%s\'', $class_name);

		try {
			$test = new $class_name();

			$r = new ReflectionObject($test);
			foreach ($r->getMethods() as $method) {
				// Only attempt to run public methods.
				if (! $method->isPublic())
					continue;

				// Only run '_test'-suffixed methods.
				if (substr($method->getName(), -5) != '_test')
					continue;

				// Log the method.
				Core::log(
					'debug',
					'Running %s->%s',
					ansi::csprintf('blue', FALSE, $class_name),
					ansi::csprintf('cyan', FALSE, $method->getName())
				);

				// Run the test method.
				try {
					$method->invoke($test);
				} catch (Exception $e) {
				}

				if ((bool) $passed = count($test->assertions['pass'])) {
					$message = ansi::csprintf('green', FALSE, 'Passed %d assertions', $passed);
					Core::log('debug', $message);
				}

				if ((bool) $failed = count($test->assertions['fail'])) {
					$message = ansi::csprintf('red', FALSE, 'Failed %d assertions', $failed);
					Core::log('debug', $message);
				}

				if ($passed == 0 AND $failed == 0) {
					$message = ansi::csprintf('red', FALSE, 'No assertions were checked');
					Core::log('debug', $message);
				}
			}

			// Get the assertions from the test case and remember them.
			$this->assertions = array_merge_recursive($this->assertions, $test->assertions);
		} catch (Exception $e) {
			echo $e->getMessage(), "\n";
		}

		unset($test);
	}
}
