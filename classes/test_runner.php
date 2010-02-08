<?php

class Test_Runner {
	protected $tests = array();

	protected $pass_count = 0;
	protected $fail_count = 0;

	protected $assertions = array(
		'pass' => array(),
		'fail' => array(),
	);

	protected $test_count = 0;
	protected $exception_count = 0;

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

		// Output any information that might be handy.
		Core::log(
			'info',
			'Ran %d tests, %d assertions passed, %d failed, %d exceptions.',
			$this->test_count,
			$this->pass_count,
			$this->fail_count,
			$this->exception_count
		);

		if ((bool) $this->fail_count) {
			Core::log('warning', '%d assertions failed.', $this->fail_count);
			foreach ($this->assertions['fail'] as $class => $methods) {
				foreach ($methods as $method => $assertions) {
					foreach ($assertions as $message) {
						Core::log('warning', '%s->%s = %s', $class, $method, $message);
					}
				}
			}
		}
	}

	/**
	 * Instantiate and run a single test case.
	 */
	protected function run_test($class_name, $class_path) {
		require_once($class_path);

		Core::log('info', 'Running tests from \'%s\'', $class_name);

		try {
			$r = new ReflectionClass($class_name);
			foreach ($r->getMethods() as $method) {
				// Only attempt to run public methods.
				if (! $method->isPublic())
					continue;

				// Only run '_test'-suffixed methods.
				if (substr($method->getName(), -5) != '_test')
					continue;

				// Log the method.
				Core::log(
					'info',
					'Running %s->%s',
					ansi::csprintf('blue', FALSE, $class_name),
					ansi::csprintf('cyan', FALSE, $method->getName())
				);

				// We're about to run, increment our counter.
				$this->test_count++;

				// Instantiate the test.
				$test = new $class_name();

				// Run the test method.
				try {
					// Setup first.
					$test->setup();
					$method->invoke($test);
					$test->teardown();
				} catch (Exception $e) {
					$this->exception_count++;
					Core::log('error', '%s', $e);
				}

				if ((bool) $passed = count($test->assertions['pass'])) {
					$message = ansi::csprintf('green', FALSE, 'Passed %d assertions', $passed);
					Core::log('info', $message);
				}

				if ((bool) $failed = count($test->assertions['fail'])) {
					$message = ansi::csprintf('red', FALSE, 'Failed %d assertions', $failed);
					Core::log('info', $message);
					foreach ($test->assertions['fail'] as $fail) {
						Core::log('warning', var_export($fail, TRUE));
					}
				}

				if ($passed == 0 AND $failed == 0) {
					$message = ansi::csprintf('red', FALSE, 'No assertions were checked');
					Core::log('warning', $message);
				}

				// Get the assertions from the test case and remember them.
				$this->pass_count += $passed;
				$this->fail_count += $failed;

				foreach ($test->assertions as $key => $assertions) {
					foreach ($assertions as $assertion) {
						Core::log(
							'debug',
							'Assigning into %s for %s->%s = %s',
							$key,
							$class_name,
							$method->getName(),
							$assertion
						);
						$this->assertions[$key][$class_name][$method->getName()][] = $assertion;
					}
				}

				// Unset the test before next iteration.
				unset($test);
			}
		} catch (Exception $e) {
			Core::log('error', 'Caught exception: %s', $e);
		}
	}
}
