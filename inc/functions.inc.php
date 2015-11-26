<?php
function myText($key) {
	global $lang,$txt;
	return isset($txt[$lang][$key])?$txt[$lang][$key]:$txt["de"][$key];
}
function chooseLang($availableLangs) {
	if (!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		return "de";
	}
	$pref=array();
	foreach(explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]) as $lang) {
		if (preg_match('/^([a-z]+).*?(?:;q=([0-9.]+))?/i', $lang.';q=1.0', $split)) {
			if (!isset($split[2])) $split[2]=0;
			//echo $split[1]."<br>";
			$pref[sprintf("%f%d", $split[2], rand(0,9999))]=strtolower($split[1]);
		}
	}
	krsort($pref);

	$langs = array_merge(array_intersect($pref, $availableLangs), $availableLangs);
	//print_r($langs);
	return array_shift($langs);
}

function banner($banners, $id, $text = NULL) {
	if ( !$banners ) return;
	?>
	<div id="<?= htmlspecialchars($id) ?>">
		<?php
			$bannerNo = mt_rand(0, count($banners)-1);
			$banner = $banners[$bannerNo];
			$banner_tracking_params = "b=" . urlencode($bannerNo + 1);
			$banner_tracking_params .= "&piwik_campaign=wikipediade_" . urlencode($id);
			$banner_tracking_params .= "&piwik_kwd=" . urlencode($banner);
			$banner_target_url = "https://spenden.wikimedia.de/spenden/?$banner_tracking_params";

			if ( preg_match('/\.php$/', $banner) ) {
				include( $banner );
			} else {
				?>
				<a href="<?php echo htmlspecialchars($banner_target_url); ?>"><img src="img/<?php echo urlencode($banner); ?>" border="0" alt="<?php echo htmlspecialchars($text); ?>" title="<?php echo htmlspecialchars($text); ?>" /></a>
				<?php
			}
		?>
		<br style="clear:right"/>
	</div> 
	<?php
}

function error_page($message_html, $lang) {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Wikipedia - die freie Enzyklop&auml;die (Portal Deutschland)</title>
	<link rel="stylesheet" media="screen" type="text/css" href="style.css" />
	<script language="JavaScript" type="text/javascript" src="ajax_search.js"></script>
</head>
<body>
<div id="head">
<ul>
	<li><a href="/">Suche</a></li>
	<li><a href="/properties">Einstellungen</a></li>
	<li><a href="/about">&Uuml;ber dieses Angebot</a></li>
	<li><a href="/imprint">Impressum und rechtliche Hinweise</a></li>
</ul>
</div>
	<div id="main">
		<h3>Sorry</h3>
		<div style="width:600px;align:center;">
		<p><?= $message_html ?></p>
		<p><a href="index?l<?= urlencode($lang) ?>" onclick="history.back();return false;">zur&uuml;ck zur Suche</a></p>
		</div>
	</div>
</body>
</html>
<?php
}

function search_log($kind, $lang, $item) {
	global $logFile;
	if ( !$logFile ) return;

	$message = date("Y-m-d H:i:s")."\t$kind\t$lang\t$item\n";

	return file_put_contents($logFile, $message, FILE_APPEND);
}

?>
