<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1.0" />
    <title>Wikipedia, die freie Enzyklop&auml;die</title>
    <link rel="apple-touch-icon" href="/img/wikipedia.png" />
    <link rel="stylesheet" media="screen" type="text/css" href="style.css" />
</head>

<body>
<div id="WMDE-Banner-Container"></div>
<div id="main">
    <div id="mainbox">
        <div class="wikipedia-logo">
            <a href="https://de.wikipedia.org/"><img src="img/Wikipedia-logo-v2-de.svg" title="Hauptseite der Wikipedia" alt="Wikipedia - Die freie Enzyklopädie"/></a>
        </div>
        <search id="maincontent" title="Wikipedia durchsuchen">
            <div id="form">
                <form id="frmSearch" class="search-form" action="go.php" method="get" accept-charset="UTF-8">
                    <input type="text" id="txtSearch" name="q" alt="Search Criteria" onkeyup="triggerSuggestLater('de')" autocomplete="off" aria-label="Wikipedia durchsuchen"/>
                    <input type="hidden" name="l" value="de" />
                    <input type="hidden" name="e" value="wikipedia" /><input type="hidden" name="s" value="suchen" />
                    <button type="submit" id="cmdSearch" class="button-ooui-like" name="b">
						<span class="search-icon"></span><span class="sr-only">Suchen</span>
					</button>
                </form>
            </div>
            <div id="search_suggest"></div>
        </search>
    </div>
    <?php include 'footer.php'?>
</div>

<script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="js/jquery.cookie.min.js"></script>
<script type="text/javascript" src="js/suggest.js"></script>
<script type="text/javascript" src="js/footer.js"></script>
<!-- Matomo -->
<script defer type="text/javascript" src="js/tracking.js"></script>
<noscript><p><img src="//stats.wikimedia.de/piwik.php?idsite=3&amp;rec=1" style="border:0;" alt=""/></p></noscript>
<!-- End Matomo Code -->

<script defer src="https://bruce.wikipedia.de/banners/wikipedia.de-banners/stats.js"></script>
<?php
// Allow specifying a specific banner (instead of a random one) by checking for the "banner" URL parameter
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
<script data-banner-src="https://bruce.wikipedia.de/<?php echo $urlBanner; ?>" >
    var bannerSrcTag = $('[data-banner-src]');
    var bannerUrl = bannerSrcTag.data('banner-src');
    var vWidthParam = "?vWidth=" + window.innerWidth;
    bannerSrcTag.after('<script defer src="' + bannerUrl + vWidthParam + '"><\/script>');

	if( $( '#WMDE-Banner-Container' ).is( ':empty' ) ) {
		document.getElementById( 'txtSearch' ).focus();
	}
</script>
</body>
</html>
