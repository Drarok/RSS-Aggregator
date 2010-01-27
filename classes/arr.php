<?php

class arr {
	public static function get($arr, $key, $default = FALSE) {
		return array_key_exists($key, $arr)
			? $arr[$key]
			: $default;
	}
}
