/**
 * JavaScript library for encryption functionalities
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
( function( Banner ) {
	'use strict';

	var EP;

	function Encryption() {
		this.initCryptLib();
	}

	EP = Encryption.prototype;

	/**
	 * Load the encryption library
	 */
	EP.initCryptLib = function () {
		$.ajax( {
			url: Banner.config.encryption.libUrl,
			dataType: 'script',
			cache: true,
			success: function() {
				console.log( 'encryption library loaded' );
			}
		} );
	};

	EP.encrypt = function ( data, $targetField ) {
		var publicKey = openpgp.key.readArmored( Banner.config.encryption.publicKey );

		openpgp.encryptMessage( publicKey.keys, data )
			.then( function( pgpMessage ) {
				$targetField.val( pgpMessage );
			} ).catch( function( error ) {
				// ...
			}
		);
	};

	Banner.encryption = new Encryption();

} )( Banner );
