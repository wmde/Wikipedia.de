<?php
require_once("wikirip.inc.php");

class WikiBox extends WikiRip {
	var $test = null;
	var $testText = null;

	function __construct($url, $cache = null, $cachedir = null) {
		parent::__construct($url, $cache, $cachedir);
	}

	function pick_page( $listPage, $purge = false, $testPage = false, $testText = false ) {
		if ( !$testPage && isset($testText) && !empty($testText) ) {
			$feature = $testText;
		} else {
				
			$list = $this->rip_page($listPage, 'raw', $purge);
			if (!$list && !$testPage) return false;

			$list = preg_replace('@<!--.*?-->@s', '', $list);
			if (!preg_match_all('/^\*+.*\[\[(.+?)( *\|.*?)?\]\]/m', $list, $mm, PREG_SET_ORDER)) $mm = array();
			if (!$mm && !$testPage) return  false;

			$list = array();

			foreach ($mm as $m) {
				$list[] = WikiRip::normalizeTitle($m[1]);
			}
			
			if ( $testPage ) {
				$page = WikiRip::normalizeTitle("Web:{$testPage}");
				$this->cache_duration = 0; //disable cache for testing
				$html = $this->rip_page($page, 'render', true);
				if (!$html) $html = "<i class='error'>page not found: ".htmlspecialchars($page)."</i>";
				else if (!in_array($page, $list) && !preg_match('/wikibox-test/i', $html)) $html = "<i class='error'>inactive feature page not marked with \"wikibox-test\": ".htmlspecialchars($page)."</i>";
			} else {
				$i = mt_rand(0, count($list)-1 );
				$page = $list[$i];
					
				$html = $this->rip_page($page, 'render', $purge);
				if (!$html) return false;
			}
		}

		if ($html) {
			$html = preg_replace('!(<(span|div)[^<>]*>)?\s*BEGIN_NORIP.*?END_NORIP\s*(</\2 *>)?!', '', $html);
			$html = preg_replace('!(<(span|div)[^<>]*wikibox-test[^<>]*>).*(</\2 *>)!', '', $html);
		}

		return $html;
	}
}

