<?php

// Bootstrap the system.
require_once('classes/core.php');

$feeds = Core::config('config.feeds', array());

Core::log('debug', 'Request from %s to fetch %d feeds', arr::get($_SERVER, 'REMOTE_ADDR', 'Unknown'), count($feeds));

$agg = new Aggregate();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><feed />');
$xml->addChild('id', Core::config('config.url'));
$xml->addChild('title', Core::config('config.title'));

foreach ($feeds as $id => $feed) {
	// Get the data from the RSS feed.
	$rss = new RSS($id, $feed);

	foreach ($rss->items as $entry) {
		$agg->add_entry($entry);
	}
}

if (Core::config('config.debug_mode')) {
	var_dump($agg->items);
} else {
	header('Content-Type: application/atom+xml');
	echo $aggregate->asXML();
}

Core::log('info', 'Finished refreshing');
