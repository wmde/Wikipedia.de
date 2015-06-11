<?php
$baseUrl = 'https://spenden.wikimedia.de/ajax.php';

if ( isset( $_POST[ 'debug' ] ) && $_POST[ 'debug' ] === true ) {
	unset( $_POST[ 'debug' ] );
	$baseUrl = 'https://test.wikimedia.de/ajax.php';
}
$handle = curl_init( $baseUrl . '?action=subscribe' );
curl_setopt( $handle, CURLOPT_POST, true );
curl_setopt( $handle, CURLOPT_POSTFIELDS, $_POST );
curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
echo curl_exec( $handle );
curl_close( $handle );
