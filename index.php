<?php

// Bootstrap the system.
require_once('classes/core.php');

$feeds = Core::config('config.feeds', array());

Core::log('debug', 'Request from %s to fetch %d feeds', $_SERVER['REMOTE_ADDR'], count($feeds));

$aggregate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><feed />');
$aggregate->addChild('id', Core::config('config.url'));
$aggregate->addChild('title', Core::config('config.title'));

foreach ($feeds as $id => $feed) {
	// Get the data from the RSS feed.
	$rss = new RSS($id, $feed);
	$xml = $rss->fetch();

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

if (Core::config('config.debug_mode')) {
	header('Content-Type: text/plain');
} else {
	header('Content-Type: application/atom+xml');
	echo $aggregate->asXML();
}

Core::log('info', 'Finished refreshing');
