<?php header('HTTP/1.1 451 Unavailable For Legal Reasons'); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1.0" />
    <title>Wikipedia, die freie Enzyklop&auml;die</title>
    <link rel="apple-touch-icon" href="/img/wikipedia.png" />
    <link rel="stylesheet" media="screen" type="text/css" href="style.css" />
    <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="js/jquery.cookie.min.js"></script>
</head>

<body>
<div id="WMDE-Banner-Container"></div>
<div id="main">
    <div id="mainbox">
        <div class="wikipedia-logo">
            <a href="https://de.wikipedia.org/"><img src="img/Wikipedia-logo-v2-de.svg" title="Hauptseite der Wikipedia" alt="Wikipedia - Die freie Enzyklopädie"/></a>
        </div>
        <div id="maincontent">
            <p>
                Aus rechtlichen Gründen ist es Wikimedia Deutschland e. V. untersagt, die Suchanfrage für den von Ihnen eingegebenen Suchbegriff durchzuführen.
                Bitte entschuldigen Sie die dadurch entstehenden Unannehmlichkeiten.
            </p>
            <p>
                <a href="https://wikipedia.de/">Zurück zur Suche</a>
            </p>
        </div>
    </div>
	<?php include 'footer.php'?>
</div>

<!-- temporary tracking of page views with donation tracker -->
<img src="" id="piwik-tracking"/>
<script type="text/javascript">
	if( Math.random() <= 0.01 ) {
		var pwkUrl = location.protocol + "//tracking.wikimedia.de/piwik.php?idsite=3&rec=1&url=",
			trackUrl = "https://wikipedia.de/";
		$( '#piwik-tracking' ).attr( 'src', pwkUrl + encodeURIComponent( trackUrl ) );
	}
</script>

<!-- Matomo -->
<script async defer type="text/javascript" src="tracking.js"></script>
<noscript><p><img src="//stats.wikimedia.de/piwik.php?idsite=3&amp;rec=1" style="border:0;" alt=""/></p></noscript>
<!-- End Matomo Code -->

</body>
</html>
