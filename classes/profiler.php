<?php

class Profiler {
	protected $events = array();

	public function __construct() {
		$this->add_event(FALSE);
		$this->add_event('Profiler Started');
	}

	public function add_event($name) {
		$this->events[] = array($name, microtime(TRUE));
	}

	public function get_events() {
		$result = '';
		$start = FALSE;
		$previous = FALSE;
		$last = FALSE;

		foreach ($this->events as $parts) {
			list($name, $time) = $parts;

			if ($previous === FALSE) {
				$start = $previous = $time;
				continue;
			}

			$result .= sprintf('%s => %.5f', $name, $time - $previous)."\n";
			$last = $previous = $time;
		}

		$result .= sprintf('Total time: %.5f', $last - $start)."\n";

		return $result;
	}
}
