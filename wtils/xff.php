<?php

function get_xff_cache() {
	global $xffCache, $xffTrustedProxies, $wbCachePrefix;

	if (!$xffCache && defined('USE_MAGIC_XFF') && $xffTrustedProxies) {
		$xffCache = new LocalCache($wbCachePrefix);
	}
	
	return $xffCache;
}

function get_magic_xff() {
	global $xffMagicExpiry, $xffTrustedProxies;
	
	$cache = get_xff_cache();
	
	if ( !$cache) {
		return false;
	}
	
	if ( !in_array($_SERVER["REMOTE_ADDR"], $xffTrustedProxies)) {
		return false;
	}
	
	$xff = null;
	$key = "xff:" . $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"]; 
	
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$xff = $_SERVER['HTTP_X_FORWARDED_FOR']; 

		if ( $cache ) {
			$cache->set($key, $xff, $xffMagicExpiry);
		}
	} else {
		if ( $cache ) {
			$xff = $cache->get($key);
			if (!$xff) $xff = null;
		}
	}
	
	return $xff;
}
/*
header("Content-Type: text/plain");

echo "CLIENT: " . $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"];
echo "\n";

$xff = get_magic_xff();
echo "XFF: " . var_export($xff, true);
echo "\n";
*/