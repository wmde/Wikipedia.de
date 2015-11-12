<?php
require_once 'wikirip.inc.php';

use WMDE\wpde\CookieJar;

class WikiBox extends WikiRip {

	private $bannerParameters;
	private $cookieJar;

	function __construct( $url, $cache = null, $cachedir = null, CookieJar $cookieJar = null ) {
		$this->cookieJar = $cookieJar;
		parent::__construct( $url, $cache, $cachedir );
	}

	function pick_page( $listPage, $purge = false, $testPage = false ) {
		$list = $this->fetchList( $listPage, $purge, $testPage );
		if ( !$list && !$testPage ) {
			return false;
		}

		if ( $testPage ) {
			$page = WikiRip::normalizeTitle( "Web:{$testPage}" );
			$this->cache_duration = 0; //disable cache for testing
			$html = $this->rip_page( $page, 'render', true );
			if ( !$html ) {
				$html = '<i class="error">page not found: ' . htmlspecialchars( $page ) . '</i>';
			} else if ( !in_array( $page, $list ) && !preg_match( '/wikibox-test/i', $html ) ) {
				$html = '<i class="error">inactive feature page not marked with "wikibox-test": ' . htmlspecialchars( $page ) . '</i>';
			}
		} else {
			$i = mt_rand( 0, count( $list ) - 1 );
			$page = $list[$i];
				
			$html = $this->rip_page( $page, 'render', $purge );
			if ( !$html ) {
				return false;
			}
		}

		if ( $html ) {
			$html = preg_replace( '!(<(span|div)[^<>]*>)?\s*BEGIN_NORIP.*?END_NORIP\s*(</\2 *>)?!', '', $html );
			$html = preg_replace( '!(<(span|div)[^<>]*wikibox-test[^<>]*>).*(</\2 *>)!', '', $html );

			$this->countImpression( $page );
		}

		return $html;
	}

	private function fetchList( $listPage, $purge = false, $testPage = false ) {
		$list = $this->rip_page( $listPage, 'raw', $purge );
		if ( !$list && !$testPage ) {
			return false;
		}

		$list = preg_replace( '@<!--.*?-->@s', '', $list );
		$items = array();
		preg_match_all( '/^\*+ ?(.+)/m', $list, $items, PREG_SET_ORDER );
		if ( !$items && !$testPage ) {
			return false;
		}

		$list = array();

		foreach ( $items as $item ) {
			$pageTitle = $this->parseItem( $item[1] );
			if ( $pageTitle !== false ) {
				$list[] = $pageTitle;
			}
		}

		return $list;
	}

	private function parseItem( $item ) {
		if ( $this->itemIsTemplate( $item ) ) {
			$this->bannerParameters = $this->extractParameters( $item );
			return $this->getPageTitleByParams( $this->bannerParameters );
		} else {
			$item = str_replace( array( '[', ']' ), '', $item );
			return WikiRip::normalizeTitle( $item );
		}
	}

	private function itemIsTemplate( $item ) {
		return strpos( $item, '{{' ) !== false;
	}

	private function extractParameters( $item ) {
		$parameters = array();

		$item = str_replace( array( '{{BannerDefinition|', '}}' ), '', $item );
		$params = explode( '|', $item );

		foreach ( $params as $param ) {
			$param = explode( '=', $param );
			$parameters[$param[0]] = $param[1];
		}

		return $parameters;
	}

	private function getPageTitleByParams( $params ) {
		if ( !array_key_exists( 'title', $params ) ) {
			return false;
		}

		if ( array_key_exists( 'maxOverallImp', $params ) &&
				intval( $this->cookieJar->getCookie( 'overallImpCount' ) ) >= intval( $params['maxOverallImp'] ) ) {
			return false;
		}

		if ( array_key_exists( 'maxImp', $params ) && array_key_exists( 'secondaryTitle', $params ) ) {
			if ( $this->parseImpCountFromCookie() >= intval( $params['maxImp'] ) ) {
				return WikiRip::normalizeTitle( $params['secondaryTitle'] );
			}
		}

		return WikiRip::normalizeTitle( $params['title'] );
	}

	private function countImpression( $page ) {
		$impCount = $this->cookieJar->getCookie( 'impCount' );
		if ( $impCount ) {
			list( $bannerName, $impCount ) = explode( '|', $impCount );

			$campaignName = $this->getCampaignName();
			if ( $campaignName ) {
				$cookieValue = $campaignName . '|' . ( intval( $impCount ) + 1 );
			} elseif ( $bannerName !== $page ) {
				$cookieValue = $page . '|1';
			} else {
				$cookieValue = $bannerName . '|' . ( intval( $impCount ) + 1 );
			}
			$this->cookieJar->setCookie( 'impCount', $cookieValue );
		} else {
			$this->cookieJar->setCookie( 'impCount', $page . '|1' );
		}

		$overallImpCount = $this->cookieJar->getCookie( 'overallImpCount' );
		if ( $overallImpCount ) {
			$this->cookieJar->setCookie( 'overallImpCount', intval( $overallImpCount ) + 1 );
		} else {
			$this->cookieJar->setCookie( 'overallImpCount', '1' );
		}
	}

	private function getCampaignName() {
		return isset( $this->bannerParameters['campaign'] ) ? $this->bannerParameters['campaign'] : false; 
	}

	private function parseImpCountFromCookie() {
		$impCount = $this->cookieJar->getCookie( 'impCount' );
		if ( $impCount ) {
			$values = explode( '|', $impCount );
			if ( count( $values ) > 1 ) {
				return $values[1];
			}
		}
		return 0;
	}

}
