<?php

function get_raw_post_data() {
	if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) return "";
	
	$data = file_get_contents("php://input");
	
	if ( $data === false || $data === null  || $data === '' ) {
		$data = @$HTTP_RAW_POST_DATA;
	}
	
	if ( $data === false || $data === null ) {
		print_r($_SERVER);
		$msg = "unable to access raw post data; "
				."enctype = " . @$_SERVER['CONTENT_TYPE'] . "; "
				."always_populate_raw_post_data = " . ini_get('always_populate_raw_post_data');
		 
		trigger_error($msg, E_USER_WARNING);
		debug(__FUNCTION__, "error", $msg);
	}
	
	return $data;
}

/*
function send_raw_request( $url, $header = null, $payload = null, $method = null ) {
	if ( !preg_match('!^([-\w\d]+)://([-.\w\d]+)(?::(\d+))?(?:/([^\s]*?))?(#[^\s]*)?$!', $url, $m) ) {
		return "ARGL! $url";
	}
	
	$proto = $m[1];
	$host = $m[2];
	$port = @$m[3];
	$path = @$m[4];
	
	if ( $path === null || $path === '' ) $path = '/';
	
	if ( $proto == 'http' ) {
		if ( !$port ) $port = 80;
 	} else if ( $proto == 'https' ) {
		$host = 'ssl://' . $host;
		if ( !$port ) $port = 443;
 	} else {
 		return false;
 	}
 	
 	$data = "$method $path HTTP/1.0\r\n"; //NOTE: use HTTP 1.0 to disable fancy stuff
 	
 	if ( $payload !== null && $payload !== false && $payload !== '' ) {
 		if (!$method) $method = 'POST';
 		$data .= "Content-Length: " . strlen($payload) . "\r\n";
 	}
 	else {
 		if (!$method) $method = 'GET';
 	}
 	
 	$data .= "Connection: close\r\n";
 	
 	if ( $header && is_array($header) ) {
 		$header = join( "\r\n", $header );
 	}
 	
 	if ( $header ) {
 		$data .= trim( $header ) . "\r\n";
 	} 
 	
 	$data = $data . "\r\n" . $payload;
 	
 	$fp = fsockopen ("$host", $port, $errno, $errstr);
 	
 	if ( $errno ) {
		return array(
			'error' => $errstr,
			'errno' => $errno,
		);
  	}

  	if ( !$fp ) {
		return array(
			'error' => "error initializing socket",
			'errno' => -1,
		);
  	}
  	
  	// send request
  	fputs ($fp, $header . $payload);
  	fflush($fp);
  	fclose($fp); // must close first, then read response!
  	
  	//receive response
  	$response = "";
  	
  	//NOTE: feof only becomes true after the server actually closes the connection
  	//NOTE: keep-alive is not supported!
  	while ($fp && !feof($fp)) { 
  		$response .= fgets ($fp, 1024);
  	}
  	
  	return parse_http_response( $response );
}
*/

function parse_http_response( $response ) {
	if ( !preg_match('/^(.*?)(?:\r\n\r\n|\r\r|\n\n)(.*)$/s', $response, $m) ) return false;
	list($dummy, $header, $content) = $m;
		
		while ( preg_match('#^HTTP/1\.[01] 100 Continue#', $header) ) {
			list($header, $content) = preg_split("/\r\n\r\n|\r\r|\n\n/", $content, 2);
		}
		
		$cookies = array();
		
		if ( preg_match_all('/^Set-Cookie:\s*(.*)$/m', $header, $m, PREG_PATTERN_ORDER ) ) {
			foreach ( $m[1] as $c ) {
				$c = preg_replace('/; .*$/', '', $c);
				list($k, $v) = explode('=', $c, 2);
				$cookies[$k] = $v;
			} 
		} 
		
		preg_match('!^HTTP/1\.\d+ (\d+)(?: ([^\r\n]*))?!', $header, $m); 
		$status = @$m[1]; #FIXME: handle error mor eexplicitely
		
		$headers = preg_split("/\r\n|\r|\n/", $header); #FIXME: continuations
		
		return array(
			'status' => $status,
			'headers' => $headers,
			'cookies' => $cookies,
			'content' => $content,
		);
}

function compose_url_paramters( $data ) {
	$params = '';
	foreach ($data as $k => $v) {
		if ($params !== '') $params .= '&';
		$params .= urlencode($k);
		$params .= '=';
		$params .= urlencode($v);
	}
	
	return $params;
}

function post($url, $data, $headers = false) {
	$ch = curl_init($url);
	if (!$ch) return false;
	
	if (STAGING_MODE == 'dev') curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	else curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	
	if ( $data && is_array($data) ) {
		//NOTE: if CURLOPT_POSTFIELDS is passed an array, this triggers Content-Type: multipart/form-data. We don't want that.
		//      instead, we set Content-Type: application/x-www-form-urlencoded if headers are not given explicitly.
		
		$data = compose_url_paramters($data);
		
		if ( !$headers ) {
			$headers = array( "Content-Type: application/x-www-form-urlencoded" );
		}
	}
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	curl_setopt($ch, CURLOPT_USERAGENT, ini_get('user_agent'));

	if ( $headers ) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
	}
	
	$ret = curl_exec($ch);
	$err = curl_errno($ch); 

	if ($err) {
		if ( function_exists('debug') ) {
			debug(__FUNCTION__, "CURL Error Nr ", $err);
			debug(__FUNCTION__, "CURL Error", curl_error($ch));
		}

		return array(
			'error' => curl_error($ch),
			'errno' => $err,
		);
	}
	
	if ( $ret ) {
		$result = parse_http_response( $ret );
		
		if ( isset($result['status']) && $result['status'] >= 400 ) {
			if ( !empty($result['headers'][0]) ) $result['error'] = $result['headers'][0];
			else $result['error'] = $result['status'] . " error";
			
			$result['errno'] = $result['status'];
		}
	} else  {
		return array(
			'error' => 'unknown error',
			'errno' => true,
		);
	}
	
	curl_close($ch);
	
	return $result;
}
