<?php
/** Last remnants of super retro PHP code */
require_once './inc/blocked_terms.conf.php';

if ( empty($myDomain) ) {
	if ( !empty($_SERVER['HTTP_HOST'] ) ) {
		$myDomain = $_SERVER['HTTP_HOST'];
	} else if ( !empty($_SERVER['SERVER_NAME']) ) {
		$myDomain = $_SERVER['SERVER_NAME'];
	} else {
		$myDomain = false;
	}
}

$searchEngines = array (
	"de" => array(
		"wikipedia" => 'http://de.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
	)
);

if (!isset($_GET["l"]) || !$_GET["l"]) {
	header("Location: http://".$myDomain);
	die($myDomain);
}

$l = stripslashes($_GET["l"]);

if (!isset($_GET["q"]) || !$_GET["q"]) {
	header("Location: http://$l.wikipedia.org/wiki/Special:Search?profile=advanced");
	die($myDomain);
}

$q = stripslashes($_GET["q"]);
$url = $searchEngines[$l][$_GET["e"]];
if (preg_match($blockedPages, $q)) {
	$url = 'http://'.$myDomain.'/censored.php';
} else {
	$url = 'http://'.$l.'.wikipedia.org/wiki/'.str_replace("%2F","/",urlencode(str_replace(" ","_",$q)));
}
header("Location: ".$url);
exit;
?>
