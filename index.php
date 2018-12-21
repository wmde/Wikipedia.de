<?php
define('SETUP_WIKI_BOXES', 1);
include_once("inc/config.inc.php");
include_once("inc/functions.inc.php");

header("Content-Type: text/html; charset=UTF-8");

// Cookie setzen (zum Test)
if (!isset($_COOKIE) || !count($_COOKIE)) {
	setcookie( "cookies" , true);
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>wikipedia.de - Wikipedia, die freie Enzyklop&auml;die</title>
    <link rel="apple-touch-icon" href="/img/wikipedia.png" />
    <link rel="stylesheet" media="screen" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="js/jquery.cookie.min.js"></script>
    <script language="JavaScript" type="text/javascript" src="suggest.js"></script>
</head>

<body onload="self.focus();document.getElementById('txtSearch').focus();">
<div id="WMDE-Banner-Container"></div>
<center>
    <div id="main">
        <div id="mainbox">
            <div class="wikipedia-logo">
                <a href="https://de.wikipedia.org/"><img src="img/Wikipedia-logo-v2-de.svg" title="Hauptseite der Wikipedia" alt="Wikipedia - Die freie Enzyklopädie"/></a>
            </div>
            <div id="maincontent">
                <div id="form">
                    <form id="frmSearch" action="go.php" method="get" accept-charset="UTF-8"><input type="text" id="txtSearch" name="q" alt="Search Criteria" onkeyup="triggerSuggestLater('de')" autocomplete="off" />
                        <input type="hidden" name="l" value="de" />
                        <input type="hidden" name="e" value="wikipedia" /><input type="hidden" name="s" value="suchen" />
                        <button type="submit" id="cmdSearch" class="button-ooui-like" name="b"><span class="search-icon"></span></button>
                    </form>
                </div>
                <div id="search_suggest"></div>
            </div>
        </div>
        <footer class="page-footer" id="donationfooter">
            <div class="link-block">
                <ul>
                    <li><a onclick="triggerPiwikTrack(this, 'wikimedia.de-logo');" href="https://www.wikimedia.de"><img class="wikimedia-logo" src="img/wmde_logo_white.svg" alt="Wikimedia Deutschland e.V."></a></li>
                    <li>
                        <p><strong>Über Wikimedia Deutschland e. V.</strong></p>
                        <p><a href="https://www.wikimedia.de/de/impressum">Impressum & Kontakt</a></p>
                        <p><a href="https://wikimedia.de/de/satzung">Satzung, Ordnungen & Beschlüsse</a></p>
                        <p><a href="https://wikimedia-deutschland.softgarden.io/de/vacancies">Stellenangebote</a></p>
                    </li>
                    <li>
                        <p><strong>Mitwirken</strong></p>
                        <p><a href="https://spenden.wikimedia.de/apply-for-membership?piwik_campaign=wpdefooter&piwik_kwd=wpdefooterbtn">Mitglied werden</a></p>
                        <p><a href="https://spenden.wikimedia.de/?piwik_campaign=wpdefooter&piwik_kwd=wpdefooterbtn">Jetzt Spenden</a></p>
                        <p><a href="https://spenden.wikimedia.de/use-of-funds">Mittelverwendung</a></p>
                    </li>
                    <li>
                        <p><strong>Vereinskanäle</strong></p>
                        <p><a href="https://blog.wikimedia.de/">Unser Blog</a></p>
                        <p><a href="https://www.facebook.com/WMDEeV">Facebook</a></p>
                        <p><a href="https://twitter.com/wikimediade">Twitter</a></p>
                    </li>
                </ul>
            </div>
        </footer>

    </div> <!-- main -->

</center>

<!-- temporary tracking of page views with donation tracker -->
<img src="" id="piwik-tracking"/>
<script type="text/javascript">
	if( Math.random() <= 0.01 ) {
		var pwkUrl = location.protocol + "//tracking.wikimedia.de/piwik.php?idsite=3&rec=1&url=",
			trackUrl = "https://wikipedia.de/";
		$( '#piwik-tracking' ).attr( 'src', pwkUrl + encodeURIComponent( trackUrl ) );
	}
</script>
<script type="application/javascript" src="https://bruce.wikipedia.de/banners/wikipedia.de-banners/stats.js"></script>
<?php
$randomBanner = 'your-contribution-to-free-knowledge.js';
$rawUrlBanner = filter_input( INPUT_GET, 'banner', FILTER_UNSAFE_RAW );
$filteredUrlBanner = basename( filter_input(
	INPUT_GET,
	'banner',
	FILTER_SANITIZE_SPECIAL_CHARS,
	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
) );
$urlBanner = ( $filteredUrlBanner && $rawUrlBanner === $filteredUrlBanner ) ? sprintf( 'banners/wikipedia.de-banners/%s.js', $filteredUrlBanner) : $randomBanner;
?>
<script type="application/javascript" src="https://bruce.wikipedia.de/<?php echo $urlBanner; ?>"></script>

<!-- Matomo -->
<script async defer type="text/javascript" src="tracking.js"></script>
<noscript><p><img src="//stats.wikimedia.de/piwik.php?idsite=3&amp;rec=1" style="border:0;" alt=""/></p></noscript>
<!-- End Matomo Code -->

</body>
</html>
