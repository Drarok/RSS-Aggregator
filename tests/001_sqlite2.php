<?php

class Sqlite2_Test extends Test_Case {
	public function load_extension_test() {
		Core::set_config('config.sqlite_version', 2);
		Core::bootstrap();
	}
}
