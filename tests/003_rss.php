<?php

class Rss_Test extends Test_Case {
	public function valid_rss_test() {
		$rss = new RSS('Test RSS', APPPATH.'test_data/003_valid_rss.xml', FALSE);

		$titles = array(
			'A Headline Goes Here',
			'Another Headline Goes Here',
		);

		foreach ($rss->get_items() as $key => $item) {
			$this->assert_equal((string) $item->title, $titles[$key], 'Checking title');
		}
	}
}
