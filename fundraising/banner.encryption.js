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
		var self = this;
		this.initialized = false;

		$( document ).ready( function() {
			self.initCryptLib();
		} );
	}

	EP = Encryption.prototype;

	/**
	 * Load the encryption library
	 */
	EP.initCryptLib = function () {
		var self = this;

		$.ajax( {
			url: Banner.config.encryption.libUrl,
			dataType: 'script',
			cache: true,
			success: function() {
				self.initialized = true;
			}
		} );
	};

	/**
	 * Encrypts a message and puts the encrypted data into the given field
	 * @param data The message to encrypt
	 * @return {Promise}
	 */
	EP.encrypt = function ( data ) {
		if ( this.initialized ) {
			var publicKey = openpgp.key.readArmored( Banner.config.encryption.publicKey );

			return openpgp.encryptMessage( publicKey.keys, data )
				.then( function( pgpMessage ) {
					return pgpMessage;
				} );
		}
	};

	Banner.encryption = new Encryption();

} )( Banner );
