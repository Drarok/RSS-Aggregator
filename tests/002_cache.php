<?php

class Cache_Test extends Test_Case {
	public function expiry_test() {
		// Grab a cache object.
		$cache = new Cache('TEST');

		// Put some data in it.
		$cache->write('test data');

		// Check we can read it back ok.
		$this->assert_equal('test data', $cache->read(), 'Cache fetch');

		// Force the cache to expire immediately.
		Core::set_config('config.cache_expiry', -1);

		// Check we get no data back from cache.
		$this->assert_equal(FALSE, $cache->read(), 'Cache fetch when expired');

		// Always delete the cache file.
		$cache->invalidate();
	}
}
