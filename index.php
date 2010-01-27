<?php


set_time_limit(0);
error_reporting(E_ALL & E_STRICT);
ini_set('display_errors', 'on');

require_once('classes/core.php');
require_once('classes/config.php');
require_once('classes/rss.php');

$feeds = Core::config('config.feeds', array());

define('ENABLE_CACHE', Core::config('config.enable_cache'));

Core::log('debug', 'Request from %s to fetch %d feeds', $_SERVER['REMOTE_ADDR'], count($feeds));

$aggregate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><feed />');
$aggregate->addChild('id', Core::config('config.url'));
$aggregate->addChild('title', Core::config('config.title'));

foreach ($feeds as $id => $feed) {
	Core::log('info', 'Fetching %s', $id);

	$id = str_replace(array('/', ' '), '_', $id);
	$cache_file = sprintf('cache/feed_%s.xml', $id);
	
	// Grab the data from somewhere.
	if (ENABLE_CACHE AND file_exists($cache_file)) {
		$data = file_get_contents($cache_file);
	} else {
		$data = file_get_contents($feed);
		if (ENABLE_CACHE)
			file_put_contents($cache_file, $data);
	}

	Core::log('debug', 'Parsing %d bytes', strlen($data));
	
	// Parse the feed.
	$xml = simplexml_load_string($data);

	Core::log('debug', 'Appending data');
	
	foreach ($xml->entry as $entry) {
		$node = $aggregate->addChild('entry');
		foreach ($entry as $key => $value) {
//			Core::log('debug', 'Appending key %s, data %s', (string) $key, (string) $value);
			$subnode = $node->addChild((string) $key, str_replace('&', '&#38;', (string) $value));
			foreach ($value->attributes() as $attrkey => $attrval) {
				$subnode->addAttribute($attrkey, (string) $attrval);
			}
		}
	}
}

if (Core::config('config.debug_mode')) {
	header('Content-Type: text/plain');
} else {
	header('Content-Type: application/atom+xml');
	echo $aggregate->asXML();
}

Core::log('info', 'Finished refreshing');
