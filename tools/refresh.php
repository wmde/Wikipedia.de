<?php
if (!isset($_SERVER['argc'])) {
    die('can only be used fro mthe command line!');
}

define('SETUP_WIKI_BOXES', 1);
chdir( dirname(__FILE__) . '/..'); //make sure we are in the right dir.

include_once("inc/config.inc.php");
include_once("inc/functions.inc.php");

$purge = true;

debug(__FILE__, "running refresh script");
debug(__FILE__, "wbImageCacheList", $wbImageCacheList);
debug(__FILE__, "wbPageCacheList", $wbPageCacheList);

if (!empty($featurebox)) {
	$featurebox->pure = $purge;
	$featurebox->tracing = 'cli';
	$featurebox->refetch_pages(); //re-fetching of images is implicit!
}

if (!empty($bannerbox)) {
	$bannerbox->pure = $purge;
	$bannerbox->tracing = 'cli';
	$bannerbox->refetch_pages(); //re-fetching of images is implicit!
}

if (!empty($wbRipCache) && !empty($wbPageCacheList)) {
	$items = $wbPageCacheList->items();
	
	foreach ( $items as $item ) {
		if ( $item[0] == 'donors' ) {
			$max = (int)$item[1];
			
			getLatestDonors($max, $purge);
		} 
	}
}
