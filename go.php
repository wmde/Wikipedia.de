<?php
require_once("./inc/config.inc.php");
require_once("./inc/functions.inc.php");

if (!isset($_GET["q"]) || !$_GET["q"]) {
	header("Location: http://".$myDomain);
	die($myDomain);
}
if (!isset($_GET["l"]) || !$_GET["l"]) {
	header("Location: http://".$myDomain);
	die($myDomain);
}

$q = stripslashes($_GET["q"]);
$l = stripslashes($_GET["l"]);

if(isset($_GET["s"])) {
	if (@preg_match($blockedSearches, $q) || @preg_match($blockedPages, $q)) {
		search_log("blocked",$l,$q);
		error_page("Die Suche nach <b>".htmlspecialchars($q)."</b> kann von diesem Angebot aus bis auf Weiteres nicht durchgef&uuml;hrt werden. Bitte entschuldigen Sie die dadurch entstehenden Unannehmlichkeiten.", $l);
		exit;
	} else {
		$url = $searchEngines[$l][$_GET["e"]];

		if ( !$url ) {
			search_log("!".$_GET["e"],$l,$q);
			error_page("Der angefragte Suchmodus ist nicht verfügbar.", $l);
			exit;
		} else {
			$url = str_replace('$1',urlencode($q),$url);
			search_log($_GET["e"],$l,$q);
		}
	}
	
} elseif (preg_match($blockedPages, $q)) {
	search_log("blocked",$l,$q);
	error_page("Der Wikipedia-Artikel <b>".htmlspecialchars($q)."</b> wird von uns diesem Angebot aus bis auf Weiteres nicht verlinkt. Bitte entschuldigen Sie die dadurch entstehenden Unannehmlichkeiten.", $l);
	exit;
} else {
	$url = 'http://'.$l.'.wikipedia.org/wiki/'.str_replace("%2F","/",urlencode(str_replace(" ","_",$q)));
	search_log("suggest",$l,$q);
}

/*
echo "<pre>";
print_r($_GET);
echo "</pre>";
echo '<a href="'.$url.'">'.$url.'</a>';
exit;
*/
header("Location: ".$url);
exit;
//$_GET
?>
