<?php

// Bootstrap the system.
require_once('classes/core.php');

// Start the profiler.
$profiler = new Profiler;

$feeds = Core::config('config.feeds', array());

Core::log('debug', 'Request from %s to fetch %d feeds', arr::get($_SERVER, 'REMOTE_ADDR', 'Unknown'), count($feeds));

$agg = new Aggregate();

$profiler->add_event('Fetching Feeds');
foreach ($feeds as $id => $feed) {
	// Get the data from the RSS feed.
	$rss = new RSS($id, $feed);

	foreach ($rss->get_items() as $entry) {
		$agg->add_entry($entry);
	}

	$profiler->add_event('Parsed '.$id);
}

if (Core::config('config.debug_mode')) {
	$agg->asXML();
} else {
	header('Content-Type: application/atom+xml');
	$output = $agg->asXML();
	echo str_replace('><', ">\n<", $output);;
}

$profiler->add_event('Finished refreshing');

Core::log('info', 'Finished refreshing');
Core::log('debug', $profiler->get_events());
