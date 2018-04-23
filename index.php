<?php
define('SETUP_WIKI_BOXES', 1);
include_once("inc/config.inc.php");
include_once("inc/functions.inc.php");

header("Content-Type: text/html; charset=UTF-8");

// Cookie setzen (zum Test)
if (!isset($_COOKIE) || !count($_COOKIE)) {
	setcookie( "cookies" , true);
}

// Sprache ermitteln
if (isset($_GET["l"]) && in_array($_GET["l"],$availableLangs)) {
	$lang = $_GET["l"];
} elseif (isset($_COOKIE["lang"]) && in_array($_COOKIE["lang"],$availableLangs)) {
	$lang = $_COOKIE["lang"];
} else {
	$lang = chooseLang($availableLangs);
}

$onKeyUp = "triggerSuggestLater('$lang');"

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $lang ?>" lang="<?= $lang ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>wikipedia.de - Wikipedia, die freie Enzyklop&auml;die</title>
	<link rel="apple-touch-icon" href="/img/wikipedia.png" />
	<link rel="stylesheet" media="screen" type="text/css" href="style.css" />
	<script type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
	<?php
		if ( isset($banner) && $banner ):
	?>
		<script type="text/javascript" src="js/jquery.cookie.min.js"></script>
	<?php
		endif;
	?>
	<script language="JavaScript" type="text/javascript" src="suggest.js"></script>
</head>

<body onload="self.focus();document.getElementById('txtSearch').focus();">
<center>

<?php
if ( $showBanner && @$bannerbox ) include("inc/bannerbox.inc.php");
else if ( @$topBanners ) banner($topBanners, "donationbox_top", "Jetzt spenden!");
?>

<table id="head" border="0" cellpadding="0" cellspacing="0" style="width:100%">
  <tr>
  <td style="text-align:left; padding:.5em 1em; vertical-align:top;">Die Hauptseite der deutschsprachigen Wikipedia finden Sie unter <a href="https://de.wikipedia.org">https://de.wikipedia.org</a>.</td>

  <td id="propertiesLink">
    <a href="properties">Einstellungen</a>
  </td>

  </tr>
</table>  <!-- head -->

<div id="main">
	<p id="langs">
<?php
foreach ($availableLangs AS $langItem) {
	if ($langItem == $lang) {
		echo '<b>'.myText("lang_".$langItem).'</b> &nbsp; ';
	} else {
		echo '<a href="index?l='.$langItem.'">'.myText("lang_".$langItem).'</a> &nbsp; ';
	}
}
echo '<a href="https://wikipedia.org">mehr</a>';
?>
	</p>

	<div id="mainbox">
		<div><a href="https://<?= $lang ?>.wikipedia.org/"><img src="img/logo.png" style="float:left;" border="0" align="left" width="100" height="100" title="Hauptseite der Wikipedia (<?= $lang ?>)" alt="Logo Wikipedia" /></a></div>
		<div id="maincontent">
			<h3><?= myText("searchin") ?></h3>
			<?php
			if (isset($_COOKIE["engine"]) && $_COOKIE["engine"] == "t-online" && $lang=="de") {
				?>
				<div id="form"><form id="frmSearch" action="go" method="get" accept-charset="UTF-8"><input type="text" id="txtSearch" name="q" alt="Search Criteria" onkeyup="<?php print htmlspecialchars($onKeyUp); ?>" autocomplete="off" /><input type="hidden" name="l" value="<?= $lang ?>" /><input type="hidden" name="e" value="t-online" /><input type="hidden" name="s" value="suchen" />&nbsp; <input type="submit" id="cmdSearch" name="b" value="<?= myText("search") ?>" alt="Suche starten" /><img src="img/t-online.ico"  width="16" height="16" title="Suchen mit T-Online" /></form></div>
				<?php
			}elseif (isset($_COOKIE["engine"]) && $_COOKIE["engine"] == "exalead" && $lang=="de") {
				?>
				<div id="form"><form id="frmSearch" action="go" method="get" accept-charset="UTF-8"><input type="text" id="txtSearch" name="q" alt="Search Criteria" onkeyup="<?php print htmlspecialchars($onKeyUp); ?>" autocomplete="off" /><input type="hidden" name="l" value="<?= $lang ?>" /><input type="hidden" name="e" value="exalead" /><input type="hidden" name="s" value="suchen" />&nbsp; <input type="submit" id="cmdSearch" name="b" value="<?= myText("search") ?>" alt="Suche starten" /><img src="img/exalead.ico" width="16" height="16" title="Suchen mit exalead" /></form></div>
				<?php
			}elseif (isset($_COOKIE["engine"]) && $_COOKIE["engine"] == "web.de" && $lang=="de") {
				?>
				<div id="form"><form id="frmSearch" action="go" method="get" accept-charset="UTF-8"><input type="text" id="txtSearch" name="q" alt="Search Criteria" onkeyup="<?php print htmlspecialchars($onKeyUp); ?>" autocomplete="off" /><input type="hidden" name="l" value="<?= $lang ?>" /><input type="hidden" name="e" value="web.de" /><input type="hidden" name="s" value="suchen" />&nbsp; <input type="submit" id="cmdSearch" name="b" value="<?= myText("search") ?>" alt="Suche starten" /><img src="img/web.de.ico" width="16" height="16" title="Suchen mit web.de" /></form></div>
				<?php
			} else {
				?>
				<div id="form"><form id="frmSearch" action="go" method="get" accept-charset="UTF-8"><input type="text" id="txtSearch" name="q" alt="Search Criteria" onkeyup="<?php print htmlspecialchars($onKeyUp); ?>" autocomplete="off" /><input type="hidden" name="l" value="<?= $lang ?>" /><input type="hidden" name="e" value="wikipedia" /><input type="hidden" name="s" value="suchen" />&nbsp; <input type="submit" id="cmdSearch" name="b" value="<?= myText("search") ?>" alt="Suche starten" /></form></div>
				<?php
			}
			?>
			<div id="search_suggest"></div>
			<p>&nbsp;</p>

			<!--<p>Fehlermeldungen? Verbesserungsvorschl&auml;ge?<br />Wir freuen uns auf <a href="http://meta.wikimedia.org/wiki/Wikipedia.de">R&uuml;ckmeldungen</a>.</p>-->
		</div> <!-- maincontent -->
	</div>  <!-- mainbox -->

      <?php if ( $showFeature && @$featurebox ) include("inc/featurebox.inc.php"); ?>

    <?php if ( isset($bottomBanners) && !empty($bottomBanners) ) banner($bottomBanners, "donationbox_bottom", "Jetzt spenden!"); ?>

<div id="donationfooter">
	<p>
		<a onclick="triggerPiwikTrack(this, 'wikimedia.de');" href="http://www.wikimedia.de">Wikimedia Deutschland e.V.</a>
		&nbsp;&ndash;&nbsp; <a href="./imprint" onclick="triggerPiwikTrack(this, 'impressum');">Impressum&nbsp;und&nbsp;Datenschutz</a>
	</p>
	<p><a onclick="triggerPiwikTrack(this, 'wikimedia.de-logo');" href="http://www.wikimedia.de"><img src="img/wikimedia_button-de.png" border="0" alt="Ein Wikimedia Projekt" title="Ein Wikimedia Projekt" /> </a></p>
</div> <!-- footer -->

</div> <!-- main -->

</center>

<!-- temporary tracking of page views with donation tracker -->
<img src="" id="piwik-tracking" />
<script type="text/javascript">
if ( Math.random() <= 0.01 ) {
	var pwkUrl = location.protocol + "//tracking.wikimedia.de/piwik.php?idsite=3&rec=1&url=",
		trackUrl = "https://wikipedia.de/";
	$('#piwik-tracking').attr( 'src', pwkUrl + encodeURIComponent( trackUrl ) );
}
</script>
</body>
</html>
