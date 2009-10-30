<?php

define('ENABLE_CACHE', FALSE);
define('DEBUG', FALSE);

set_time_limit(0);

function log_msg() {
	$args = func_get_args();
	$msg = array_shift($args);

	if ((bool) $args) {
		$msg = vsprintf($msg, $args);
	}

	static $log_file;
	if (! $log_file) {
		$log_file = fopen('log.txt', 'a');
	}

	$msg = sprintf('[%s] %s', date('Y-m-d H:i:s'), $msg).PHP_EOL;

	fwrite($log_file, $msg);
}

require_once('config.php');
$feeds = $config['feeds'];

log_msg('Request from %s to fetch %d feeds', $_SERVER['REMOTE_ADDR'], count($feeds));

$aggregate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><feed />');
$aggregate->addChild('id', $config['url']);
$aggregate->addChild('title', $config['title']);

foreach ($feeds as $id => $feed) {
	log_msg('Fetching %s', $id);

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

	log_msg('Parsing %d bytes', strlen($data));
	
	// Parse the feed.
	$xml = simplexml_load_string($data);

	log_msg('Appending data');
	
	foreach ($xml->entry as $entry) {
		$node = $aggregate->addChild('entry');
		foreach ($entry as $key => $value) {
			$subnode = $node->addChild((string) $key, str_replace('&', '&#38;', (string) $value));
			foreach ($value->attributes() as $attrkey => $attrval) {
				$subnode->addAttribute($attrkey, (string) $attrval);
			}
		}
	}
}

if (defined('DEBUG') AND DEBUG)
	header('Content-Type: text/plain');
else
	header('Content-Type: application/atom+xml');

echo $aggregate->asXML();

log_msg('Finished refreshing');
