<?php
require_once __DIR__ . '/../src/CookieJar.php';

use WMDE\wpde\CookieJar;

$cliMode = false;
$devMode = false;
$myDomain = null;

ini_set('user_agent', ''); # define user agent used with wikibox functionality

$wbCacheDir = "/tmp/"; # page cache location
$wbImageCacheDir = 'cache';
$wbImageCachePath = 'cache';
$wbImageCacheExceptions = array( 'upload.wikimedia.org' );

$wbCacheDuration = 60 * 60 * 24; // one day with freshcache run from cron

$wbWikiUrl = ""; # index.php URI of (remote) mediawiki installation

$wbCachePrefix = ""; # filename prefix for cache files

if ( function_exists('apc_store') ) {
	$wbInternalCacheMode = false; //may be auto, apc, xcache or eaccellerator; use false to disable
} else {
	$wbInternalCacheMode = null;
}

$wbMemcachedServer = false; // use memcached for buffering cached content; has no effect if $wbInternalCacheMode is set.
$wbCacheBufferDuration = 20; // check files every 20 seconds 

$wbFeatureListPage = ""; # list of features to be displayed
$wbBannerListPage = ""; # list of banners to be displayed

$piwikConf = array(
	"active" => false,
	"url" => "", # URI of piwik installation
	"secureUrl" => "", # secure URI of piwik installation
	"siteId" => "", # site ID as defined in piwik
);

# block page titles and/or search terms
$blockedPages = array();
$blockedSearches = array();

if ( empty($myDomain) ) {
	if ( !empty($_SERVER['HTTP_HOST'] ) ) {
        	$myDomain = $_SERVER['HTTP_HOST'];
	} else if ( !empty($_SERVER['SERVER_NAME']) ) {
        	$myDomain = $_SERVER['SERVER_NAME'];
	} else {
        	$myDomain = false;
	}
}

if ( $myDomain == 'localhost' 
   || $myDomain == '127.0.0.1'
   || $myDomain == '::1' ) {
	   $devMode = true;
}

if ( !empty($_SERVER['argc']) ) {
	$cliMode = true;
}

if ( $cliMode && !empty( $argv[1] ) && $argv[1] == 'debug' ) {
	 $devMode = true;
}

if ( $devMode ) {
        error_reporting( E_ALL );
        ini_set( "display_errors", 1 );
}

if ( $devMode && $cliMode ) { // dev mode, use local resources
        if ( !function_exists('debug') ) {
			function debug($location, $msg = "", $var = "nothing9874325") {
				$s = "";
				
				if ( $var !== "nothing9874325" ) {
					$s = ': ' . preg_replace('/\s+/', ' ', var_export($var, true));
				}
				
				print "[$location] $msg $s\n";
			}
		}

        if ( !function_exists('backtrace') ) {
			function backtrace($label) {
				debug_print_backtrace();
			}
		}
}

if ( $devMode ) { // dev mode, use local resources
   
   $dev_conf = dirname(__FILE__) . '/dev-config.inc.php';
   
	if ( file_exists($dev_conf) ) {
		include($dev_conf);
	}

	if ( !class_exists('FirePHP') ) {
		@include_once('FirePHPCore/FirePHP.class.php');
	}
	
	if ( class_exists('FirePHP') ) {
		global $firephp; 
		$firephp = FirePHP::getInstance(true);
		
		$firephp->registerExceptionHandler(false);
		
        if ( !function_exists('debug') ) {
			function debug($location, $name = "", $var = "") {
				global $firephp; 
				$firephp->log($var, $location . ': ' . $name);
			}
		}

        if ( !function_exists('backtrace') ) {
			function backtrace($label) {
				global $firephp; 
				$firephp->trace($label);
			}
		}
		
		ob_start(); // note: buffer output, so FirePHP can attach headers 
	} 
}

if ( !function_exists('debug') ) {
	function debug() {
		//noop dummy
	}
}


$purge = @$_GET['purge'] == 'now';

$wbRipCache = null;
$wbImageCache = null;
$wbPageCacheList = null;
$wbImageCacheList = null;

if ( defined('SETUP_WIKI_BOXES') && isset($wbWikiUrl) ) {
	debug(__FILE__, "setting up wiki boxes", $wbWikiUrl);

      require_once("wikibox.class.php");
      
	  if ( $wbInternalCacheMode ) {
		  $buff = new LocalCache($wbCachePrefix, $wbInternalCacheMode);
		  $wbRipCache = new BufferedFileCache($buff, $wbCacheDir, $wbCachePrefix);      
		  $wbRipCache->buffer_expiry = $wbCacheBufferDuration;
	  } else if ( $wbMemcachedServer ) {
		  $buff = new MemcachedCache($wbMemcachedServer, $wbCachePrefix);
		  $wbRipCache = new BufferedFileCache($buff, $wbCacheDir, $wbCachePrefix);      
		  $wbRipCache->buffer_expiry = $wbCacheBufferDuration;
	  } else {
		  $wbRipCache = new FileCache($wbCacheDir, $wbCachePrefix);      
	  }

	  if ( $wbImageCacheDir ) {
		  $wbImageCache = new FileCache($wbImageCacheDir, $wbCachePrefix);
		  $wbImageCache->serialize = false; // raw data, not serialized
		  $wbImageCache->suffix = ""; // keep extension
		  $wbImageCache->fmode = 0644; //make cached files readable
	  }

		$wbPageCacheList = new FreshCacheList( "$wbCacheDir/{$wbCachePrefix}page-cache.list" );
		$wbImageCacheList = new FreshCacheList( "$wbImageCacheDir/{$wbCachePrefix}image-cache.list" );

	$cookieJarParams = array(
			'expire' => time() + 604800, /* 1 week */
			#'secure' => false,
			#'domain' => 'www.wikipedia.de',
			'path' => '/wpde'
	);
      $featurebox = new WikiBox( $wbWikiUrl, $wbRipCache, null, new CookieJar( $cookieJarParams );
      $featurebox->setImageCache($wbImageCache, $wbImageCachePath, $wbImageCacheExceptions);
      $featurebox->purge = $purge;
      $featurebox->cache_duration = $wbCacheDuration;
      $featurebox->page_cache_list = $wbPageCacheList;
      $featurebox->image_cache_list = $wbImageCacheList;

      $bannerbox = new WikiBox( $wbWikiUrl, $wbRipCache, null, new CookieJar( $cookieJarParams ) );
      $bannerbox->setImageCache($wbImageCache, $wbImageCachePath, $wbImageCacheExceptions);
      $bannerbox->purge = $purge;
      $bannerbox->cache_duration = $wbCacheDuration;
      $bannerbox->page_cache_list = $wbPageCacheList;
      $bannerbox->image_cache_list = $wbImageCacheList;
}

$featuretest = @$_GET['featuretest'];
$bannertest = @$_GET['bannertest'];

$showBanner = false;
$showFeature = false;

if( @$featurebox && !$bannertest ) {
	$feature = $featurebox->pick_page( $wbFeatureListPage, $purge, $featuretest );
	if( $feature ) {
		$showFeature = true;
	}
}

if( @$bannerbox && !$featuretest ) {
	$banner = $bannerbox->pick_page( $wbBannerListPage, $purge, $bannertest );
	if( $banner ) {
		$showBanner = true;
	}
}

if( $showFeature && $showBanner ) {
	$random = mt_rand( 0, 99 );

	if( $random < 50 ) {
		$showBanner = false;
	} else {
		$showFeature = false;
	}
}

$availableLangs = array(
	'de',
	'da',
	'nds',
	'hsb',
	'dsb',
	'stq',
	'rmy'	
);

$searchEngines = array (
	"de" => array(
		"wikipedia" => 'http://de.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"fr" => array(
		"wikipedia" => 'http://fr.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"en" => array(
		"wikipedia" => 'http://en.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"als" => array(
		"wikipedia" => 'http://als.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"nds" => array(
		"wikipedia" => 'http://nds.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"da" => array(
		"wikipedia" => 'http://da.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"hsb" => array(
		"wikipedia" => 'http://hsb.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"fy" => array(
		"wikipedia" => 'http://fy.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"rmy" => array(
		"wikipedia" => 'http://rmy.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),
	"dsb" => array(
		"wikipedia" => 'http://dsb.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),		
	"stq" => array(
		"wikipedia" => 'http://stq.wikipedia.org/wiki/Special:Search?ns0=1&search=$1'
		),		

);

$txt["de"]["lang_de"] = "Deutsch";
$txt["de"]["lang_en"] = "Englisch";
$txt["de"]["lang_fr"] = "Franz&ouml;sisch";
$txt["de"]["lang_als"] = "Alemannisch";
$txt["de"]["lang_nds"] = "Plattdeutsch";
$txt["de"]["lang_lb"] = "Luxemburgisch";
$txt["de"]["lang_da"] = "D&auml;nisch";
$txt["de"]["lang_fy"] = "Friesisch";
$txt["de"]["lang_hsb"] = "Obersorbisch";
$txt["de"]["lang_rom"] = "Romani";
$txt["de"]["lang_rmy"] = "Romani";
$txt["de"]["lang_dsb"] = "Niedersorbisch";
$txt["de"]["lang_stq"] = "Saterfriesisch";

$txt["de"]["name"] = "Deutschsprachige Wikipedia";
$txt["en"]["name"] = "English Wikipedia";
$txt["fr"]["name"] = "Wikip&eacute;dia francophone";
$txt["als"]["name"] = "Alemannische Wikipedia";
$txt["nds"]["name"] = "Plattdeutsche Wikipedia";

$txt["de"]["searchin"] = "Suche in der deutschsprachigen Wikipedia";
$txt["en"]["searchin"] = "Suche in der englischsprachigen Wikipedia";
$txt["fr"]["searchin"] = "Rechercher dans le Wikip&eacute;dia francophone";
$txt["als"]["searchin"] = "Suche in der alemannischen Wikipedia";
$txt["nds"]["searchin"] = "Suche in der plattdeutschen Wikipedia";
$txt["lb"]["searchin"] = "Suche in der luxemburgischen Wikipedia";
$txt["da"]["searchin"] = "Suche in der d&auml;nischsprachigen Wikipedia";
$txt["fy"]["searchin"] = "Suche in der friesischen Wikipedia";
$txt["hsb"]["searchin"] = "Suche in der obersorbischen Wikipedia";
$txt["rom"]["searchin"] = "Suche in der Wikipedia auf Romani";
$txt["rmy"]["searchin"] = "Suche in der Wikipedia auf Romani";
$txt["dsb"]["searchin"] = "Suche in der Wikipedia auf Niedersorbisch";
$txt["stq"]["searchin"] = "Suche in der Wikipedia auf Saterfriesisch";


$txt["de"]["search"] = "suchen";
$txt["en"]["search"] = "search";
$txt["fr"]["search"] = "rechercher";
$txt["als"]["search"] = "suchen";
$txt["nds"]["search"] = "suchen";

$txt["de"]["intro"] = "Dies ist eine Demoversion.";
$txt["en"]["intro"] = "This is a test version.";

$blockedPages = str_replace( ' ', '[_ ]', $blockedPages );
$blockedPages = '#(^'. join('$)|(^', $blockedPages) .'$)#iAu';

$blockedSearches = str_replace( ' ', '[_ ]', $blockedSearches );
$blockedSearches = '#(^'. join('$)|(^', $blockedSearches) .'$)#iAu';
