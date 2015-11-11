<?php
/**
 * CookieJar
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen
 */

namespace WMDE\wpde;

class CookieJar {

	private $parameters;

	public function __construct( array $params = null ) {
		$this->parameters = $params;
	}

	public function setCookie( $key, $value ) {
		setcookie( $key, $value,
				$this->addParameter( 'expire' ),
				$this->addParameter( 'path' ),
				$this->addParameter( 'domain' ),
				$this->addParameter( 'secure' ),
				$this->addParameter( 'httponly' ) );
	}

	public function getCookie( $key ) {
		return isset( $_COOKIE[$key] ) ? $_COOKIE[$key] : false;
	}

	private function addParameter( $key ) {
		return isset( $this->parameters[$key] ) ? $this->parameters[$key] : null;
	}
}
