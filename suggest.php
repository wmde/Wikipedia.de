<?php
/** Last remnants of super retro PHP code */
require_once './inc/blocked_terms.conf.php';
$max_seconds = 5;
set_time_limit( $max_seconds + 1 );

function load_url($url) {
	global $useCURL, $max_seconds;
	if (!$useCURL) return file_get_contents($url);

	$ch = curl_init($url);
	if (!$ch) return file_get_contents($url);

	curl_setopt($ch, CURLOPT_USERAGENT, ini_get('user_agent'));
	curl_setopt($ch, CURLOPT_TIMEOUT, $max_seconds); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);

	$text = curl_exec($ch);

	$errno = curl_errno($ch);
	if ($errno>0) $text = null;

	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($code!=200) $text = null;

	@curl_close($ch);
	return $text;
}

function fail($message, $code = 502) {
  header("Status: $code", true, $code);
  die($message);
}

$useCURL = function_exists('curl_init') && !@$_GET['nocurl'];

//Send some headers to keep the user's browser from caching the response.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-Type: text/plain; charset=UTF-8");
if ($useCURL) header("X-Using-cURL: yes" );

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