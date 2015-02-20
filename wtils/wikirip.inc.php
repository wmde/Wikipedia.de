<?php
require_once("caching.inc.php");

class WikiRip {

  function __construct($url, $cache = null, $cachedir = null) {
    if (!$cachedir) $cachedir = @$GLOBALS['cache_dir'];
    if (!$cache) $cache = @$GLOBALS['cache'];
    
    if ( $cache ) $cache->fallback_value = ''; //XXX: force negative caching
    
    $this->cache = $cache;
    $this->image_cache = null;
    $this->image_cache_path = null;
    $this->image_cache_exceptions = null;
    $this->image_cache_duration = 1; #per default, always re-fetch when the page is re-fetched
    $this->whitelist = null; #allow only these pages 
    $this->blacklist = null; #disallow these pages 
    
    $this->page_cache_list = null;
    $this->image_cache_list = null;
    
    $this->wiki_script_url = $url;
    $this->wiki_host_url = preg_replace('!^(https?://[^/]+).*$!', '$1', $url);

    $this->cache_duration = 60;
    $this->debug = false;

    $this->substitutions = array();
    $this->manglers = array();
    $this->dummy_dir = null;
    
    $this->purge = false;
    $this->inline_rewrite_debug = false;
  }

	function setImageCache( $imageCache, $imageCachePath, $imageCacheExceptions = null ) {
		$this->image_cache = $imageCache;
		$this->image_cache_path = $imageCachePath;
		$this->image_cache_exceptions = $imageCacheExceptions;
	}
  
  static function normalizeTitle($title) {
      $title = str_replace(" ", "_", trim($title));
      $title = ucfirst($title);
      return $title;
  }

  function get_page_url($wiki_page, $action = "render") {
		$page_url = $this->wiki_script_url . "?title=" . urlencode($wiki_page) . "&action=" . urlencode($action);
		return $page_url;
  }
  
  function fetch_page($wiki_page, $action = "render") {
	debug(get_class($this) . "::fetch_page", "wiki_page", $wiki_page);
	debug(get_class($this) . "::fetch_page", "action", $action);
			
	$page_url = $this->get_page_url($wiki_page, $action);
	debug(get_class($this) . "::fetch_page", "page_url", $page_url);

	$content = @file_get_contents($page_url);
		
	if ($content===false || $content===null) {
		debug(get_class($this) . "::fetch_page", "fail, got non-value", $content);
		return false; 
	}

	if (preg_match('/^<!DOCTYPE html/', $content)) { #full HTML document usually indicates an error (e.g. access denied)
		debug(get_class($this) . "::fetch_page", "fail, got error page", $content);
		return false; 
	}

	#$content = preg_replace('/<!--.*?-->/s', '', $content); #NOTE: don't strip comments, they may contain javascript, etc!
	$content = trim($content);

	if ($this->substitutions) {
	    foreach ($this->substitutions as $pattern => $value) {
			$content = preg_replace($pattern, $value, $content);
	    }
	}

	if ($this->manglers) {
	    foreach ($this->manglers as $m) {
			$content = call_user_func($m, $content);
	    }
	}

	if ($this->image_cache && $action == "render") {
		$content = WikiRip::apply_image_cache_rewrite($content, array($this, 'image_cache_rewrite'));
	}
	
    //NOTE: keep cache list in sync
    if ( !empty($this->page_cache_list) ) {
		$this->page_cache_list->add( array("rip", $wiki_page, $action) );
	}

	#debug(get_class($this) . "::fetch_page", "result", $content);
	return $content;
  }
  
  function refetch_pages() {
    if ( !empty($this->page_cache_list) ) {
		$items = $this->page_cache_list->items();
		debug(__METHOD__, "page_cache_list", $items);
		
		foreach ( $items as $item ) {
			 debug(__METHOD__, "item", $item);
			if ( $item[0] == 'rip' ) {
				$this->rip_page($item[1], $item[2], true);
			} 
		}
	} else {
		debug(__METHOD__, "no page_cache_list!");
	}
  }

  static function apply_image_cache_rewrite($content, $callback) {
	    #$content = preg_replace_callback('/(<img\s+(?:[^<>]+\s)?src\s*=\s*["\'])([^\s\'"<>]+)(.*?\>)/si', $callback, $content);
	    $content = preg_replace_callback('/(<\w+\s+(?:[^<>]+\s)?src\s*=\s*["\'])([^\s\'"<>]+\.(?:png|gif|jpe?g|svg|tiff?|ico))(.*?\>)/si', $callback, $content);
	    $content = preg_replace_callback('/(<\w+\s+(?:[^<>]+\s)?style\s*=\s*"(?:[^"<>]*?\s)?background-image\s*:\s*url\s*\(\s*\')([^\s\'<>]+)(\'.*?\>)/si', $callback, $content);
	    $content = preg_replace_callback('/(<\w+\s+(?:[^<>]+\s)?style\s*=\s*\'(?:[^\'<>]*?\s)?background-image\s*:\s*url\s*\(\s*")([^\s"<>]+)(".*?\>)/si', $callback, $content);

	  	$content = preg_replace( '/(srcset\s*=\s*["\'])([^\'"<>])+["\']/', '', $content ); #FIXME replace srcset
	    return $content;
  }
  
  static function extract_template_params($text, $template) {
	  if ( !preg_match('/\{\{\s*'.preg_quote($template).'\s*(\|.*?)\}\}/s', $text, $m) ) {
		  return false;
	  }
	  
	  $params = array();
	  
	  if ( !preg_match_all('/\|\s*([^|{}=]+)\s*(?:=\s*([^|{}]*)\s*)?/s', $m[1], $mm, PREG_SET_ORDER) ) {
		  return $params;
	  }
	  
	  $i = 1;
	  foreach ( $mm as $m ) {
		  if ( isset($m[2]) ) {
			  $k = trim($m[1]);
			  $v = trim($m[2]);
          } else {
			  $k = $i;
			  $i += 1;
			  $v = trim($m[1]);
		  }
          		  
		  $params[$k] = $v;
	  }
	  
	  return $params;
  }
  
  function rip_template_params($wiki_page, $template, $purge = false) {
	  $text = $this->rip_page( $wiki_page, 'raw', $purge );
	  if ( !$text ) return $text;
	  
	  return WikiRip::extract_template_params($text, $template);
  }
  
  function rip_page($wiki_page, $action = "render", $purge = false) {
    $wiki_page = WikiRip::normalizeTitle($wiki_page);
    
    if ( $this->whitelist !== null && $this->whitelist !== false ) {
    	if ( !in_array( $wiki_page, $this->whitelist ) ) {
    		return false;
    	}
    }

    if ( $this->blacklist !== null && $this->blacklist !== false ) {
    	if ( in_array( $wiki_page, $this->blacklist ) ) {
    		return false;
    	}
    }

	if ( $this->dummy_dir ) {
		if ( $action == 'render' ) $ext = 'html';
		else if ( $action == 'raw' ) $ext = 'wiki';
		else $ext = $action;
		
		$f = $this->dummy_dir . '/' . urlencode($wiki_page) . '.' . $ext;
		debug(get_class($this) . "::rip_page", "file", $f);
		
		$content = @file_get_contents($f);
		
		#debug(get_class($this) . "::rip_page", "content", $content);
		return $content;
	} 
    
	$cmd = array($this, 'fetch_page', array($wiki_page, $action));
    $content = $this->cache->aquire($wiki_page, $cmd, $this->cache_duration, $this->purge || $purge);

    return $content;
  }

  function image_cache_rewrite($m) {
      $url = $m[2];
      if ( $url == '' ) return $m[0]; // empty url, nothing to do
      
      $ext = '';

      if ( !preg_match('!^https?://!', $url) ) {
	    if ( preg_match('!^/!', $url) ) {
			$url = $this->wiki_host_url . $url;
	    } else {
			$url = dirname($this->wiki_script_url) . "/" . $url;
	    }
      }

      if ( $this->image_cache_exceptions && preg_match('!//(?:[^/]*@)?([^/]+)!', $url, $u) ) {
		  $domain = $u[1];
		  
	    foreach ($this->image_cache_exceptions as $exception) {
			if ( $exception == $domain ) {
				debug(__METHOD__, "image_cache_rewrite exempt ($exception)", $domain);
				
				if ( !$this->inline_rewrite_debug ) return $m[0];
				else return "<!-- image_cache_rewrite exempt! --> " . $m[0];
			}
	    }
      }

      if ( strpos($url, $this->image_cache_path) === 0 ) {
			debug(__METHOD__, "image_cache_rewrite implicit exempt", $url);
			
			if ( !$this->inline_rewrite_debug ) return $m[0];
			else return "<!-- image_cache_rewrite exempt! --> " . $m[0];
	  }

      if (preg_match('@.*(\.[^./]+)$@', $url, $u)) $ext = $u[1];
      
      $key = md5($url) . $ext;
      $cache_file = $this->image_cache->get_cache_file($key);

      $name = basename($cache_file);
      $cache_url = $this->image_cache_path . "/" . urlencode($name);

	  $cmd = array($this, 'fetch_url', array($url, $key, $this->image_cache_list));
	  $data = $this->image_cache->aquire($key, $cmd, $this->image_cache_duration, $this->purge);

	  if ( !$data ) {
		  debug(__METHOD__, "rewrite failed for", $url);
		  
		  if ( !$this->inline_rewrite_debug ) return $m[0];
		  else return "<!-- image_cache_rewrite failed (".str_replace('--', '-=-', $url).") --> " . $m[0];
	  } else {
		  debug(__METHOD__, "rewrote", $url);
		  debug(__METHOD__, "new url", $cache_url);

			$html = $m[1] . htmlspecialchars($cache_url,  ENT_QUOTES) . $m[3];
		  
		  if ( !$this->inline_rewrite_debug ) return $html;
		  else return "<!-- image_cache_rewrite OK (".str_replace('--', '-=-', $name).") --> " . $html;
	  }
  }
 
	static function fetch_url($url, $cacheKey = null, $cacheList = null) {
		debug(__METHOD__, "fetching url", $url);
		$data = @file_get_contents($url); 
		debug(__METHOD__, "got data from url", $url);
		
		if ( $cacheList && $cacheKey ) {
			$cacheList->add( array("url", $cacheKey, $url) );
		}

		return $data;
	}
  
}


/*
error_reporting(E_ALL);
ini_set("user_agent", "WikiRip TEST <de:User:Duesentrieb>");

$rip = new WikiRip("http://de.wikipedia.org/w/index.php", "/home/daniel/tmp/");
$rip->cache_duration = 0;
$rip->image_cache_dir = "/home/daniel/www/cache";
$rip->image_cache_path = "http://localhost/daniel/cache";
#$rip->image_cache_exceptions = array( "http://upload.wikimedia.org/" );

$html = $rip->rip_page("Benutzer:Duesentrieb/Sandbox");

print $html;
*/
