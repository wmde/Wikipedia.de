<?php
/** Last remnants of super retro PHP code */
require_once './inc/blocked_terms.conf.php';
$max_seconds = 5;
set_time_limit( $max_seconds + 1 );

function load_url($url) {
	global $max_seconds;

	$ch = @curl_init($url);
	if (!$ch) {
		error_log("Failed to initialize curl for URL '$url' - check your PHP configuration");
		return null;
	}

	// wikipedia.org no longer accepts requests without user agent.
	$userAgent = ini_get('user_agent') || 'wikipedia.de Search Relay (+https://github.com/wmde/Wikipedia.de)';
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($ch, CURLOPT_TIMEOUT, $max_seconds); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	// Allow for transparent redirects
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 20);

	$text = $fullResponse = curl_exec($ch);

	$errno = curl_errno($ch); 
	if ($errno>0) {
		error_log("curl request to '$url' failed. curl_error:  $errno");
		$text = null;
	}

	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($code!=200) {
		error_log("curl request to '$url' failed with HTTP status $code. Response Text: $fullResponse");
		$text = null;
	}

	if ($text !== null && trim($text) === "") {
		error_log("curl request to '$url' got an empty response");
		$text = null;
	}

	return $text;
}

function fail($message, $code = 502) {
  header("Status: $code", true, $code);
  die($message);
}

//Send some headers to keep the user's browser from caching the response.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-Type: text/plain; charset=UTF-8");

if (isset($_GET['search']) && $_GET['search'] != '' && isset($_GET['lang'])) {
	$search = urlencode($_GET['search']);
	$lang = urlencode($_GET['lang']);

	$useApi = @$_GET['query'];
	if ($useApi == "query") {
		$result = @load_url( 'https://' . $lang . '.wikipedia.org/w/api.php?action=query&list=allpages&apnamespace=0&aplimit=20&apprefix=' . $search . '&format=php' );
		if ($result===null) fail("api call failed");

		$result = unserialize($result);
		if ($result===null) fail("failed to decode results");

		echo urlencode($search)."\t".$lang."\n";
		foreach($result["query"]["allpages"] AS $id=>$page) {
			if (preg_match($blockedPages,$page["title"])) {
				continue;
			}                                                                                               
			echo urlencode($page["title"])."\t".$_GET["lang"]."\n";
		}
	} else {
		$input = @load_url( 'https://' . $lang . '.wikipedia.org/w/api.php?action=opensearch&search=' . $search );
		if ($input===null) fail("api call failed");

		$result = json_decode($input);
		if ($result===null) fail("failed to decode results");

		echo urldecode($search)."\t".$lang."\n";
		if (is_array($result[1])) {
			foreach($result[1] AS $id=>$title) {
				if (preg_match($blockedPages,$title)) {
					continue;
				}
				echo $title."\t".$lang."\n";
			}
		}
	}

}
?>
