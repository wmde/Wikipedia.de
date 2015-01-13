<?php
include_once("./inc/config.inc.php");
include_once("./inc/functions.inc.php");

// Default-Werte
$lang = false;
$engine = "wikipedia.org";

// Formularverarbeitung
$okay = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (isset($_POST["lang"]) && in_array($_POST["lang"],$availableLangs)) {
		setcookie( "lang" , $_POST["lang"] , time()+3600*24*30,"/",".$myDomain");
		$_COOKIE["lang"] = $_POST["lang"];
	} elseif (isset($_POST["lang"]) && $_POST["lang"]=="auto") {
		setcookie( "lang" , false, time()-3600*24*30,"/",".$myDomain");
		$_COOKIE["lang"] = false;
	}
	if (isset($_POST["engine"]) && array_key_exists($_POST["engine"],$searchEngines["de"])) {
		setcookie( "engine" , $_POST["engine"] , time()+3600*24*30,"/",".$myDomain");
		$_COOKIE["engine"] = $_POST["engine"];
	}
	$okay = true;
}

// Aktuelle Werte aus Cookies auslesen
if (isset($_COOKIE["lang"])) $lang = $_COOKIE["lang"];
if (isset($_COOKIE["engine"])) $engine = $_COOKIE["engine"];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>wikipedia.de - Wikipedia, die freie Enzyklop&auml;die</title>
	<link rel="stylesheet" media="screen" type="text/css" href="style.css" />
</head>

<body>
<div id="head">
<p style="padding:1em;float:right;font-size:9pt;"><a href="properties">Einstellungen</a></p>
<ul>
	<li><a href="/">Suche</a></li>
	<li><a href="/about">&Uuml;ber dieses Angebot</a></li>
	<li><a href="/imprint">Impressum und Datenschutz</a></li>
</ul>
</div>
<div id="main">

<h3>Einstellungen</h3>
<div style="width:600px;align:center;">
<?

if ($okay) {
	echo '<p><b>Ihre Einstellungen wurden gespeichert, zur&uuml;ck <a href="/">zur Suche</a></b></p>';
}
?>
<div id="form">
<?
if (!isset($_COOKIE) || !count($_COOKIE)) {
	echo "<p><b>Um diese Funktion nutzen zu k&ouml;nnen, muss Ihr Browser Cookies erlauben.</b></p>";
	//print_r($_COOKIE);
}
?>

	<p>Hier k&ouml;nnen Sie Einstellungen vornehmen, um die Suchfunktion auf wikipedia.de an Ihre pers&ouml;nlichen Vorlieben anzupassen. Um diese Funktion nutzen zu k&ouml;nnen, muss Ihr Browser Cookies erlauben.</p>
	<form id="frmSearch" action="properties" method="post">
	<p>Bitte w&auml;hlen Sie Ihre bevorzugte Sprache</p>
	<p>
<?
echo '<input type="radio" name="lang" value="auto" '.(!$lang?'checked="checked"':"").' />&nbsp;&nbsp;automatisch<br />';
foreach ($availableLangs AS $langItem) {
	echo '<input type="radio" name="lang" value="'.$langItem.'" '.($langItem==$lang?'checked="checked"':"").' />&nbsp;&nbsp;'.myText("lang_".$langItem).'<br />';
}
?>
	</p>
	<p>Bitte w&auml;hlen Sie Ihre bevorzugte Suchmaschine zum Durchsuchen der Wikipedia (Nicht alle Suchmaschinen sind in allen Sprachen verf&uuml;gbar. Im Zweifel wird immer wikipedia.org verwendet):</p>
	<p>
<?
foreach ($searchEngines["de"] AS $engineItem=>$engineURL) {
	echo '<input type="radio" name="engine" value="'.$engineItem.'" '.($engineItem==$engine?"CHECKED":"").' />&nbsp;&nbsp;'.$engineItem.'<br />';
}
?>
	</p>
	<input type="submit" value="Einstellungen speichern" />
	</form>
</div>
<?

?>
</div>
</div>
</body>
</html>