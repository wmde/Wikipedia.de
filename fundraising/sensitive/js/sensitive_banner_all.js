/*jshint latedef: nofunc */
/*jshint unused: false */
/* globals getAmount */
var isOpen = false,
	addressType = 'private';

$( function () {
	var paymentButtons = $( '#WMDE_BannerForm-payment button' ),
		fundsBox = new BannerModalInfobox( 'funds' ),
		taxesBox = new BannerModalInfobox( 'taxes' ),
		bitcoinBox = new BannerModalInfobox( 'bitcoin' ),
		dataProtectionBox = new BannerModalInfobox( 'dataprotection' );

	unlockForm();
	toggleDebitType();
	initWatchingInitialValues();

	$( '#interval_onetime' ).on( 'click', function () {
		$( '#WMDE_BannerForm-wrapper' ).css( 'height', '158px' );
		$( '.interval-options input[name=interval]' ).prop( 'checked', false );
	} );
	$( '#interval_multiple' ).on( 'click', function () {
		$( '#WMDE_BannerForm-wrapper' ).css( 'height', '204px' );
		$( '#interval1' ).prop( 'checked', 'checked' );
	} );

	paymentButtons.on( 'click', function ( e ) {
		e.preventDefault();
	} );

	$( '#WMDE_BannerFullForm-finish' ).on( 'click', function ( e ) {
		$( this ).trigger( 'blur' );
		$( this ).addClass( 'waiting' );
		lockForm();
	} );

	$( '#WMDE_BannerFullForm-finish-sepa' ).on( 'click', handleSepaValidation );

	$( '#WMDE_BannerFullForm-close-step1' ).on( 'click', function () {
		hideFullForm();
		unlockForm();
	} );
	$( '#WMDE_BannerFullForm-close-step2' ).on( 'click', function () {
		$( '#WMDE_BannerFullForm-step2' ).slideToggle( 400, function () {
			$( '#WMDE_BannerFullForm-step1' ).slideToggle();
		} );
		hideFullForm();
		unlockForm();
	} );

	$( '.WMDE_BannerFullForm-confirm-edit' ).on( 'click', function () {
		debitBackToFirstStep();
	} );

	paymentButtons.hover( function () {
			if ( !isOpen ) {
				$( '#WMDE_BannerFullForm-arrow' ).show();
			}
		},
		function () {
			$( '#WMDE_BannerFullForm-arrow' ).hide();
		} );

	$( 'input[name=\'debit-type\']' ).on( 'click', function () {
		toggleDebitType();
	} );

	$( '#address-type-1' ).on( 'click', function () {
		$( '#WMDE_BannerFullForm-company' ).slideUp();
		$( '#WMDE_Banner-person' ).slideDown();
		$( '#WMDE_Banner-address' ).slideDown();
		addressType = 'private';
	} );

	$( '#address-type-2' ).on( 'click', function () {
		$( '#WMDE_Banner-person' ).slideUp();
		$( '#WMDE_BannerFullForm-company' ).slideDown();
		$( '#WMDE_Banner-address' ).slideDown();
		addressType = 'company';
	} );

	$( '#address-type-3' ).on( 'click', function () {
		$( '#WMDE_BannerFullForm-company' ).slideUp();
		$( '#WMDE_Banner-person' ).slideUp();
		$( '#WMDE_Banner-address' ).slideUp();
		$( '#send-information' ).prop( 'checked', false );
		addressType = 'anonymous';
	} );

	// Enter key in input fields should trigger the correct submit button
	$( '#donationForm input[type=text]' ).keypress( function ( evt ) {
		if ( evt.key === 'Enter' || evt.which === 13 ) {
			evt.preventDefault();
			$( '#donationForm .submit:visible' ).first().trigger( 'click' );
		}
	} );

	// set validation event handlers
	$( '#donationForm' ).on( 'banner:validationFailed', function () {
		unlockForm();
		$( '#WMDE_BannerFullForm-finish' ).removeClass( 'waiting' );
	} );

	$( '#donationForm' ).on( 'banner:validationSucceeded', function ( evt ) {
		unlockForm();
		if ( $( '#zahlweise' ).val() === 'BEZ' ) {
			debitNextStep();
			evt.preventDefault();
		} else {
			this.submit();
		}
	} );

} );

function initWatchingInitialValues() {
	initWatchingInitialPeriodValues();
	initWatchingInitialAmountValues();
	// Payment type initial value is handled by the payment button event handlers
}

/**
 * If a payment period is selected, store its value, otherwise add event
 * handlers to store the first period that is selected.
 */
function initWatchingInitialPeriodValues() {
	var intervalOnetime = $( '#interval_onetime' ),
		intervalMultipleIsSelected = $( '#interval_multiple:checked' ),
		intervals = $( 'input[name=interval]' ),
		selectedInterval = intervals.filter( ':checked' );

	if ( intervalOnetime.prop( 'checked' ) ) {
		storeIntervalSelection( intervalOnetime );
	} else if ( intervalMultipleIsSelected.length && selectedInterval.length ) {
		storeIntervalSelection( selectedInterval );
	} else {
		intervalOnetime.one( 'click', function () {
			storeIntervalSelection( $( this ) );
		} );
		intervals.one( 'click', function () {
			storeIntervalSelection( $( this ) );
		} );
	}
}

function storeIntervalSelection( selectedInput ) {
	if ( selectedInput.prop( 'id' ) === 'interval_onetime' ) {
		setInitialValue( 'periode', 0 );
	} else {
		setInitialValue( 'periode', selectedInput.val() );
	}
}

function initWatchingInitialAmountValues() {
	var amounts = $( '.amount-options .amount-radio' ),
		selectedAmount = amounts.filter( ':checked' );
	if ( selectedAmount.length ) {
		setInitialValue( 'betrag', getAmountSelectionOrInput( selectedAmount ) );
	} else {
		amounts.one( 'click', function () {
			getAmountSelectionOrInput( $( this ) );
		} );
	}
}

function getAmountSelectionOrInput( selected ) {
	if ( selected.prop( 'id' ) === 'amount-other' ) {
		return $( 'amount-other-input' ).val();
	} else {
		return selected.val();
	}
}

/**
 * Store the first value a user selected for for payment type, amount and period
 */
function setInitialValue( name, value ) {
	var elem = $( '#' + name + '_orig' );
	if ( !elem.data( 'has-value' ) ) {
		elem.data( 'has-value', true );
		elem.val( value );
	}
}

/**
 * Handle clicks on the button on the SEPA confirmation page.
 *
 * When checkboxes are ok, submit the form, if not, highlight missing checkboxes.
 *
 * @param {Event} evt Button click event
 */
function handleSepaValidation( evt ) {
	evt.preventDefault();
	if ( $( '#confirm_sepa' ).prop( 'checked' ) && $( '#confirm_shortterm' ).prop( 'checked' ) ) {
		$( '#donationForm' ).submit();
	} else {
		$( '#confirm_sepa, #confirm_shortterm' ).each( function ( index, element ) {
			var $element = $( element ),
				$parent;
			if ( $element.prop( 'checked' ) ) {
				return;
			}
			$parent = $element.parent();
			$parent.css( { border: 'red 1px solid' } );
			$element.one( 'click', function () {
				$parent.css( { border: 'none' } );
			} );
		} );
	}
}

function lockForm() {
	$( '#WMDE_BannerForm-payment button' ).prop( 'disabled', true );
	$( 'input' ).prop( 'disabled', true );
	$( 'select' ).prop( 'disabled', true );
}

function unlockForm() {
	$( '#WMDE_BannerForm-payment button' ).prop( 'disabled', false );
	$( 'input' ).prop( 'disabled', false );
	$( 'select' ).prop( 'disabled', false );
}

function toggleDebitType() {
	if ( $( 'input:radio[name=debit-type]:checked' ).val() === 'sepa' ) {
		$( '#WMDE_BannerFullForm-nosepa' ).slideUp();
		$( '#WMDE_Banner-sepa' ).slideDown();
	} else {
		$( '#WMDE_Banner-sepa' ).slideUp();
		$( '#WMDE_BannerFullForm-nosepa' ).slideDown();
	}
}

function showFullForm() {
	$( '#WMDE_BannerFullForm-arrow' ).hide();
	$( '#WMDE_BannerFullForm-shadow' ).fadeIn();
	$( '#WMDE_BannerFullForm-details' ).slideDown();
	$( '#WMDE_BannerFullForm-info' ).slideDown();
	$( '#WMDE_Banner' ).css( 'position', 'absolute' );

	$( 'html, body' ).animate( {
		scrollTop: 0
	}, 'slow' );
	isOpen = true;
}

function hideFullForm() {
	$( '#zahlweise' ).val( '' );
	$( '#form_action' ).prop( 'name', '' );
	$( '#donationIframe' ).val( '' );
	isOpen = false;
	$( '#WMDE_BannerFullForm-details' ).slideUp( 400, function () {
		$( '#WMDE_Banner' ).css( 'position', 'fixed' );
		resetButtons();
	} );
	$( '#WMDE_BannerFullForm-info' ).hide();
	$( '#WMDE_BannerFullForm-shadow' ).fadeOut();
}

function debitNextStep() {
	$( '#WMDE_BannerFullForm-step1' ).slideToggle( 400, function () {
		$( '#WMDE_BannerFullForm-step2' ).slideToggle();
	} );

	fillConfirmationValues();

	$( 'html, body' ).animate( {
		scrollTop: 0
	}, 'slow' );
}

function fillConfirmationValues() {
	$( '#WMDE_BannerFullForm-confirm-amount' ).text( getAmount() );
	$( '#WMDE_BannerFullForm-confirm-salutation' ).text( getSalutation() );
	$( '#WMDE_BannerFullForm-confirm-street' ).text( $( '#street' ).val() );
	$( '#WMDE_BannerFullForm-confirm-city' ).text( $( '#post-code' ).val() + ' ' + $( '#city' ).val() );
	$( '#WMDE_BannerFullForm-confirm-country' ).text( getCountryByCode( $( '#country' ).val() ) );
	$( '#WMDE_BannerFullForm-confirm-mail' ).text( $( '#email' ).val() );
	$( '#WMDE_BannerFullForm-confirm-IBAN' ).text( $( '#iban' ).val() );
	$( '#WMDE_BannerFullForm-confirm-BIC' ).text( $( '#bic' ).val() );
	$( '#WMDE_BannerFullForm-confirm-bankname' ).text( $( '#bank-name' ).val() );
	$( '#WMDE_BannerFullForm-confirm-date' ).text( getCurrentDateString() );
}

function getSalutation() {
	var companyName = $( '#company-name' ).val(),
		firstName = $( '#first-name' ).val(),
		lastName = $( '#last-name' ).val(),
		title = $( '#personal-title' ).val(),
		salutation = '';

	if ( companyName !== '' ) {
		return companyName;
	}

	if ( firstName !== '' && lastName !== '' ) {
		salutation += $( 'input[name=anrede]:checked' ).val();
		if ( title !== 'Kein Titel' ) {
			salutation += ' ' + title;
		}
		salutation += ' ' + firstName + ' ' + lastName;
		return salutation;
	}

	return false;
}

function getCountryByCode( code ) {
	switch ( code ) {
		case 'DE':
			return 'Deutschland';
		case 'AT':
			return 'Österreich';
		case 'CH':
			return 'Schweiz';
		case 'IT':
			return 'Italien';
		case 'LI':
			return 'Liechtenstein';
		case 'LU':
			return 'Luxemburg';
		case 'BE':
			return 'Belgien';
		default:
			return '';
	}
}

function getCurrentDateString() {
	var now = new Date(),
		day = now.getDate(),
		month = now.getMonth() + 1;
	return ( day < 10 ? '0' : '' )
		+ day
		+ '.'
		+ ( month < 10  ? '0' : '' )
		+ month
		+ '.'
		+ now.getFullYear();
}

function debitBackToFirstStep() {
	$( '#donationForm' ).trigger( 'banner:validationReset' );
	$( '#WMDE_BannerFullForm-step2' ).slideToggle( 400, function () {
		$( '#WMDE_BannerFullForm-step1' ).slideToggle();
	} );

	$( 'html, body' ).animate( {
		scrollTop: 0
	}, 'slow' );
}

/* Payment methods show and hide */

function showDebitDonation( button ) {
	if ( $( '#zahlweise' ).val() === 'BEZ' ) {
		hideFullForm();
	} else {
		$( '#zahlweise' ).val( 'BEZ' );
		$( '#form_action' ).prop( 'name', 'go_prepare--pay:einzug' );
		$( '#donationIframe' ).val( '' );
		$( '#WMDE_Banner-debit-type' ).slideDown();
		$( '#WMDE_Banner-anonymous' ).slideUp();
		$( '#WMDE_BannerFullForm-finish' ).hide();
		$( '#WMDE_BannerFullForm-next' ).show();
		resetAddressType();
		resetButtons();
		$( button ).addClass( 'active' );
		showFullForm();
		setInitialValue( 'zahlweise', 'BEZ' );
	}
}

function resetAddressType() {
	$( '#address-type-1' ).trigger( 'click' );
}

function showDepositDonation( button ) {
	if ( $( '#zahlweise' ).val() === 'UEB' ) {
		hideFullForm();
	} else {
		$( '#zahlweise' ).val( 'UEB' );
		$( '#form_action' ).prop( 'name', 'go_prepare--pay:ueberweisung' );
		$( '#donationIframe' ).val( '' );
		showNonDebitParts( button );
		setInitialValue( 'zahlweise', 'UEB' );
	}
}

function showCreditDonation( button ) {
	if ( $( '#zahlweise' ).val() === 'MCP' ) {
		hideFullForm();
	} else {
		$( '#zahlweise' ).val( 'MCP' );
		$( '#form_action' ).prop( 'name', 'go_prepare--pay:micropayment-i' );
		$( '#donationIframe' ).val( 'micropayment-iframe' );
		showNonDebitParts( button );
		setInitialValue( 'zahlweise', 'MCP' );
	}
}

function showPayPalDonation( button ) {
	if ( $( '#zahlweise' ).val() === 'PPL' ) {
		hideFullForm();
	} else {
		$( '#zahlweise' ).val( 'PPL' );
		$( '#form_action' ).prop( 'name', 'go_prepare--pay:paypal' );
		$( '#donationIframe' ).val( '' );
		showNonDebitParts( button );
		setInitialValue( 'zahlweise', 'PPL' );
	}
}

function showNonDebitParts( button ) {
	resetButtons();
	$( button ).addClass( 'active' );
	$( '#WMDE_Banner-debit-type' ).slideUp();
	$( '#WMDE_Banner-anonymous' ).slideDown();
	$( '#WMDE_BannerFullForm-finish' ).show();
	$( '#WMDE_BannerFullForm-next' ).hide();
	showFullForm();
}

function resetButtons() {
	$( '#WMDE_BannerForm-payment button' ).trigger( 'blur' );
	$( '#WMDE_BannerForm-payment button' ).removeClass( 'active' );
}

/* LightBoxes show and hide */

/**
 * This class handles showing and hiding the info boxes that are linked.
 * Whenever an infobox is opened, the other in foxes are closed.
 */
function BannerModalInfobox( boxName ) {
	this.$box = $( '#WMDE_BannerFullForm-' + boxName );
	this.$link = $( '#WMDE_BannerFullForm-' + boxName + '-link' );
	this.boxName = boxName;
	this.$box.on( 'banner:openInfobox', this.open.bind( this ) );
	this.$box.on( 'banner:closeInfobox', this.close.bind( this ) );
	this.$link.on( 'click', this.toggle.bind( this ) );
	$( '.banner-lightbox-close', this.$box ).on( 'click', this.close.bind( this ) );
}

BannerModalInfobox.prototype.toggle = function ( e ) {
	if ( this.$box.hasClass( 'opened' ) ) {
		this.$box.trigger( 'banner:closeInfobox' );
	} else {
		this.$box.trigger( 'banner:openInfobox' );
	}
};

BannerModalInfobox.prototype.open = function ( e ) {
	// close other banners
	$( '.banner-unique' ).trigger( 'banner:closeInfobox' );

	// wait for the slide-out to be done before showing banner
	$( '.banner-unique' ).promise().done( function () {
		$( '#WMDE_BannerFullForm-info' ).addClass( this.boxName );
		this.$box.addClass( 'opened' );
		this.$box.slideDown();
	}.bind( this ) );
};

BannerModalInfobox.prototype.close = function ( e ) {
	if ( !this.$box.hasClass( 'opened' ) ) {
		return;
	}
	this.$box.slideUp( 400, function () {
		this.$box.removeClass( 'opened' );
		this.$link.removeClass( 'opened' );
		$( '#WMDE_BannerFullForm-info' ).removeClass( this.boxName );
	}.bind( this ) );
};
$( document ).ready( function () {
	$( '.list-item-title' ).on( 'click', function () {
		$( this ).toggleClass( 'opened' );
		$( this ).next().toggle();
	} );
} );
/* Function.prototype.bind polyfill from https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Function/bind#Polyfill */
Function.prototype.bind||(Function.prototype.bind=function(t){if("function"!=typeof this)throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");var o=Array.prototype.slice.call(arguments,1),n=this,r=function(){},i=function(){return n.apply(this instanceof r?this:t,o.concat(Array.prototype.slice.call(arguments)))};return this.prototype&&(r.prototype=this.prototype),i.prototype=new r,i});/*  Polyfill for browsers which don't provide window.btoa and window.atob https://github.com/davidchambers/Base64.js */
!function(){function t(t){this.message=t}var r="undefined"!=typeof exports?exports:this,e="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";t.prototype=new Error,t.prototype.name="InvalidCharacterError",r.btoa||(r.btoa=function(r){for(var o,n,a=String(r),i=0,c=e,d="";a.charAt(0|i)||(c="=",i%1);d+=c.charAt(63&o>>8-i%1*8)){if(n=a.charCodeAt(i+=.75),n>255)throw new t("'btoa' failed: The string to be encoded contains characters outside of the Latin1 range.");o=o<<8|n}return d}),r.atob||(r.atob=function(r){var o=String(r).replace(/=+$/,"");if(o.length%4==1)throw new t("'atob' failed: The string to be decoded is not correctly encoded.");for(var n,a,i=0,c=0,d="";a=o.charAt(c++);~a&&(n=i%4?64*n+a:a,i++%4)?d+=String.fromCharCode(255&n>>(-2*i&6)):0)a=e.indexOf(a);return d})}();/**
 * JavaScript library for general banner functionalities
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
var Banner;

( function () {
	'use strict';

	Banner = {};

} )();
/**
 * Banner configuration
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
( function ( Banner ) {
	'use strict';

	Banner.config = {
		encryption: {
			libUrl: '../js/lib/openpgp.min.js',
			legacyLibUrl: '../js/lib/pgp-legacy-libs.js',
			publicKey: ''
		},
		form: {
			formId: '',
			formAction: 'https://spenden.wikimedia.de/spenden/'
		},
		tracking: {
			campaign: 'default',
			keyword: 'default',
			accessCode: 'default',
			baseUrl: 'https://spenden.wikimedia.de/',
			libUrl: 'https://spenden.wikimedia.de/piwik/piwik.js',
			trackerUrl: 'https://spenden.wikimedia.de/piwik/piwik.php',
			siteId: 1,
			events: {
				BANNER_CLOSED: {
					pathName: 'banner-closed',
					clickElement: '',
					sample: 0
				},
				BANNER_EXPANDED: {
					pathName: 'banner-expanded',
					clickElement: '',
					sample: 0
				},
				BANNER_SHOWN: {
					pathName: 'banner-shown',
					clickElement: '',
					sample: 0
				},
				LIGHTBOX_CLICKED: {
					pathName: 'lightbox-clicked',
					clickElement: '',
					sample: 0
				},
				LINK_CLICKED: {
					pathName: 'link-clicked',
					clickElement: '',
					sample: 0
				}
			}
		},
		setConfig: function ( settings ) {
			$.extend( true, Banner.config, settings );
		}
	};

} )( Banner );
/**
 * JavaScript library for tracking functionalities
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
( function ( Banner, $ ) {
	'use strict';

	var TP;

	function Tracking() {
		var self = this;
		this._tracker = null;
		this._eventsTracked = [];

		$( document ).ready( function () {
			self.initTrackingLib();
			self.initClickHandlers();
		} );
	}

	TP = Tracking.prototype;

	/**
	 * Set the tracker lib
	 *
	 * @param {Tracker} tracker an instance of Piwik's Tracker class
	 */
	TP.setTracker = function ( tracker ) {
		this._tracker = tracker;
	};

	/**
	 * Track a virtual page view
	 *
	 * @param {string} eventName
	 */
	TP.trackVirtualPageView = function ( eventName ) {
		if ( this.shouldTrack( eventName, this.getRandomNumber() ) ) {
			this._tracker.trackPageView(
				Banner.config.tracking.baseUrl +
				Banner.config.tracking.events[ eventName ].pathName +
				'/' +
				Banner.config.tracking.keyword
			);
		}
	};

	/**
	 * Determines whether an event should be tracked
	 *
	 * @param {string} eventName event name based on the property keys of Banner.tracking.events
	 * @param {number} randomNumber randomly generated number to compare against the configured sample size
	 * @return {boolean}
	 */
	TP.shouldTrack = function ( eventName, randomNumber ) {
		return this._tracker &&
			Banner.config.tracking.events[ eventName ] &&
			Banner.config.tracking.events[ eventName ].sample > randomNumber;
	};

	/**
	 * @return {number}
	 */
	TP.getRandomNumber = function () {
		return Math.random() * ( 1 - 0.01 ) + 0.01;
	};

	/**
	 * fetch the piwik library and get the specified tracker from it
	 */
	TP.initTrackingLib = function () {
		var self = this;
		$.ajax( {
			url: Banner.config.tracking.libUrl,
			dataType: 'script',
			cache: true,
			success: function () {
				var trackingConfig = Banner.config.tracking;
				self.setTracker( Piwik.getTracker( trackingConfig.trackerUrl, trackingConfig.siteId ) );
			}
		} );
	};

	/**
	 * bind click events to elements as configured
	 */
	TP.initClickHandlers = function () {
		var self = this;
		$.each( Banner.config.tracking.events, function ( key, settings ) {
			$( settings.clickElement ).click( function () {
				if ( $.inArray( key, self._eventsTracked ) === -1 ) {
					Banner.tracking.trackVirtualPageView( key );
					self._eventsTracked.push( key );
				}
			} );
		} );
	};

	Banner.tracking = new Tracking();

} )( Banner, jQuery );
/**
 * JavaScript library for encryption functionalities
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
( function ( Banner ) {
	'use strict';

	var EP;

	function Encryption() {
		var self = this;
		this.initialized = false;
		this.useLegacyEncryption = !this._browserSupportsCryptoAPI();

		$( document ).ready( function () {
			self.initCryptLib();
		} );
	}

	EP = Encryption.prototype;

	/**
	 * Check if the browser supports the window.crypto API
	 */
	EP._browserSupportsCryptoAPI = function () {
		if ( typeof window !== 'undefined' && window.crypto && window.crypto.getRandomValues ) {
			return true;
		} else if ( typeof window !== 'undefined' && typeof window.msCrypto === 'object'
			&& typeof window.msCrypto.getRandomValues === 'function' ) {
			return true;
		}
		return false;
	};

	/**
	 * Load the encryption library
	 */
	EP.initCryptLib = function () {
		var self = this,
			libUrl = Banner.config.encryption.libUrl;

		if ( this.useLegacyEncryption ) {
			libUrl = Banner.config.encryption.legacyLibUrl;
		}

		$.ajax( {
			url: libUrl,
			dataType: 'script',
			cache: true,
			success: function () {
				var legacyEncryption;
				if ( self.useLegacyEncryption ) {
					/*jshint -W117 *//* Ignore undefined LegacyPGP (it's loaded dynamically) */
					legacyEncryption = new LegacyPGP();
					/*jshint +W117 */
					self.encrypt = legacyEncryption.encrypt.bind( legacyEncryption );
				}
				self.initialized = true;
			}
		} );
	};

	/**
	 * Encrypts a message and puts the encrypted data into the given field
	 *
	 * @param {string} data The message to encrypt
	 * @return {Promise}
	 */
	EP.encrypt = function ( data ) {
		var publicKey;
		if ( this.initialized ) {
			publicKey = openpgp.key.readArmored( Banner.config.encryption.publicKey );

			return openpgp.encryptMessage( publicKey.keys, data )
				.then( function ( pgpMessage ) {
					return pgpMessage;
				} );
		}
	};

	Banner.encryption = new Encryption();

} )( Banner );
/**
 * Donation API used in the banner
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
( function ( banner, $ ) {
	'use strict';

	function Api() {
		banner.config.setConfig( {
			api: {
				apiUrl: 'https://spenden.wikimedia.de/ajax.php',
				validationModule: 'ajaxValidation',
				validationAction: 'validateDonation',
				ibanModule: 'action',
				ibanGenerationAction: 'generateIban',
				ibanCheckAction: 'checkIban'
			}
		} );
	}

	/**
	 * Encrypts request data and sends a request to the API.
	 *
	 * @param {string} module
	 * @param {string} action
	 * @param {Object} data
	 * @return {Promise}
	 */
	Api.prototype._sendEncryptedRequest = function ( module, action, data ) {
		var self = this;
		return banner.encryption.encrypt( $.param( data ) )
			.then( function ( encryptedData ) {
				return self._sendRequest( module, action, {
					enc: self._encodeBase64( encryptedData )
				} );
			} );
	};

	/**
	 * Returns base64-encoded string representation of the object.
	 *
	 * @param {Object} data
	 * @return {string}
	 */
	Api.prototype._encodeBase64 = function ( data ) {
		return window.btoa( data );
	};

	/**
	 * Sends a request to API and returns results.
	 *
	 * @param {string} module
	 * @param {string} action
	 * @param {Object} data
	 * @return {Object} jQuery XMLHttpRequest (jqXHR)
	 */
	Api.prototype._sendRequest = function ( module, action, data ) {
		var requestData = data;
		$.extend( requestData, { module: module, action: action } );
		return $.ajax( {
			url: banner.config.api.apiUrl,
			data: requestData,
			dataType: 'jsonp'
		} );
	};

	/**
	 * Sends a data validation request to API and returns results.
	 *
	 * @param {Object} data
	 * @return {Promise}
	 *         Resolved parameter:
	 *         {Object} responseData object with keys:
	 *                  - status: "OK" or "ERR",
	 *                  - invalid: array containing names of fields with invalid values,
	 *                  - missing: array containing names of missing obligatory fields.
	 */
	Api.prototype.sendValidationRequest = function ( data ) {
		return this._sendEncryptedRequest(
			banner.config.api.validationModule,
			banner.config.api.validationAction,
			data
		);
	};

	/**
	 * Sends a reqeust to the API to generate IBAN and BIC from bank data
	 *
	 * @param {Object} data
	 * @return {Promise}
	 *         Resolved parameter:
	 *         {Object} responseData object with keys:
	 *                  - status: "OK" or "ERR",
	 *                  - iban
	 *                  - bic
	 *                  - bankCode
	 *                  - bankName
	 *                  - account
	 *                  - message ( if status is ERR )
	 */
	Api.prototype.sendIbanGenerationRequest = function ( data ) {
		return this._sendEncryptedRequest(
			banner.config.api.ibanModule,
			banner.config.api.ibanGenerationAction,
			data
		);
	};

	/**
	 * Sends a reqeust to the API to check the validate of an IBAN
	 *
	 * @param {Object} data
	 * @return {Promise}
	 *         Resolved parameter:
	 *         {Object} responseData object with keys:
	 *                  - status: "OK" or "ERR",
	 *                  - iban
	 *                  - bic
	 *                  - bankCode
	 *                  - bankName
	 *                  - account
	 *                  - message ( if status is ERR )
	 */
	Api.prototype.sendIbanCheckRequest = function ( data ) {
		return this._sendEncryptedRequest(
			banner.config.api.ibanModule,
			banner.config.api.ibanCheckAction,
			data
		);
	};

	banner.api = new Api();

}( Banner, jQuery ) );
/**
 * Class handling submitting of the donation form embedded in the banner.
 *
 * @licence GNU GPL v2+
 * @author Leszek Manicki <leszek.manicki@wikimedia.de>
 */
( function ( banner, $ ) {
	'use strict';

	function Form() {
		var self = this;
		this.validated = false;
		this.validationPending = false;
		this.bankCheckPending = false;
		$( document ).ready( function () {
			self.amountValidationAnchor = $( '#amount75' );
			self._initSubmitHandler();
			self._initBankDataHandler();
			self._initFieldClearHandlers();
			self._initValidationResetHandler();
		} );
	}

	Form.prototype._initSubmitHandler = function () {
		var self = this,
			form = $( '#' + banner.config.form.formId );
		form.prop( 'action', banner.config.form.formAction );
		form.on( 'submit', function () {
			if ( !self.validated && !self.validationPending ) {
				self.validated = false;
				self.validationPending = true;
				self._clearValidity();
				self.validateData( self._getFormData() )
					.then( function ( validationResult ) {
						self.validationPending = false;
						if ( validationResult.validated ) {
							self.validated = true;
							form.trigger( 'banner:validationSucceeded' );
						} else {
							self._applyValidationErrors( validationResult.fieldsMissingValue, validationResult.fieldsWithInvalidValue );
							form.trigger( 'banner:validationFailed' );
						}
					} );
			}
			return self.validated;
		} );
	};

	Form.prototype._initFieldClearHandlers = function () {
		var clearBankData = function () {
			$( '#' + banner.config.form.formId + ' input[name=bic]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=iban]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=konto]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=blz]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=bankname]' ).val( '' );
		};
		$( '#address-type-1' ).on( 'click', function () {
			$( '#' + banner.config.form.formId + ' input[name=firma]' ).val( '' );
		} );
		$( '#address-type-2' ).on( 'click', function () {
			$( '#' + banner.config.form.formId + ' input[name=vorname]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=nachname]' ).val( '' );
			$( '#' + banner.config.form.formId + ' select[name=titel]' ).val( '' );
		} );
		$( '#address-type-3' ).on( 'click', function () {
			$( '#' + banner.config.form.formId + ' input[name=firma]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=vorname]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=nachname]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=anrede]' ).prop( 'checked', false );
			$( '#' + banner.config.form.formId + ' select[name=titel]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=email]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=plz]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=ort]' ).val( '' );
			$( '#' + banner.config.form.formId + ' input[name=strasse]' ).val( '' );
		} );
		$( '#WMDE_BannerForm-payment button' ).each( function ( index, element ) {
			var $element = $( element );

			if ( $element.data( 'behavior' ) === 'clearBankData' ) {
				$element.on( 'click', clearBankData );
			}
		} );
	};

	Form.prototype._initBankDataHandler = function () {
		$( '#account-number, #bank-code, #iban' ).on( 'change', this.checkBankData.bind( this ) );
	};

	Form.prototype._initValidationResetHandler = function () {
		var self = this,
			form = $( '#' + banner.config.form.formId );
		form.on( 'banner:validationReset', function () {
			self.validated = false;
		} );
	};

	Form.prototype.checkBankData = function ( evt ) {
		var cleanBankData = function ( data, isIBAN ) {
				data = data.toString();
				if ( isIBAN ) {
					data = data.toUpperCase();
					return data.replace( /[^0-9A-Z]/g, '' );
				} else {
					return data.replace( /[^0-9]/g, '' );
				}
			},
			self = this,
			data,
			apiMethod,
			accNumElm = $( '#account-number' ),
			bankCodeElm = $( '#bank-code' );

		if ( evt.target.id === 'account-number' || evt.target.id === 'bank-code' ) {

			if ( accNumElm.val() === '' || bankCodeElm.val() === '' ) {
				return;
			}
			data = {
				accNum: cleanBankData( accNumElm.val(), false ),
				bankCode: cleanBankData( bankCodeElm.val(), false )
			};
			apiMethod = banner.api.sendIbanGenerationRequest.bind( banner.api );
		} else if ( evt.target.id === 'iban' ) {
			data = { iban: cleanBankData( evt.target.value, true ) };
			apiMethod = banner.api.sendIbanCheckRequest.bind( banner.api );
		}
		$( '#bic, #iban, #account-number, #bank-code' ).each( function ( index, element ) {
			self._clearElementValidity( $( element ) );
		} );
		this.bankCheckPending = true;
		apiMethod( data ).then( this._setBankdataAfterCheck.bind( this ) );
	};

	Form.prototype._setBankdataAfterCheck = function ( data ) {
		var $iban = $( '#iban' ),
			$accNumber = $( '#account-number' ),
			errorMessage;

		// payment method might have been switched already, check whether the related fields are visible
		if ( $iban.is( ':visible' ) || $accNumber.is( ':visible' ) ) {
			if ( data.status === 'OK' ) {
				$iban.val( data.iban ? data.iban : '' );
				$( '#bic' ).val( data.bic ? data.bic : '' );
				$accNumber.val( data.account ? data.account : '' );
				$( '#bank-code' ).val( data.bankCode ? data.bankCode : '' );
				$( '#bank-name' ).val( data.bankName ? data.bankName : '' );
			} else {
				errorMessage = 'Die eingegebenen Bankdaten sind nicht korrekt.';
				this._showError( $iban, errorMessage );
				this._showError( $accNumber, errorMessage );
			}
		}
		this.bankCheckPending = false;
	};

	Form.prototype._clearValidity = function () {
		var self = this;
		$( '#' + banner.config.form.formId + ' :input:not([type=hidden])' ).each( function ( index, element ) {
			self._clearElementValidity( $( element ) );
		} );
		self._clearElementValidity( this.amountValidationAnchor );
	};

	Form.prototype._clearElementValidity = function ( $element ) {
		var $parent = $element.parent();
		$element.removeClass( 'invalid' ).removeClass( 'valid' );
		$( '.validation', $parent ).removeClass( 'icon-bug' ).removeClass( 'icon-ok' );
		$( '.form-field-error-box', $parent ).remove();
	};

	/**
	 * @return {Object}
	 */
	Form.prototype._getFormData = function () {
		/* globals getAmount */
		var formId = banner.config.form.formId,
			formData = {
				adresstyp: $( '#' + formId + ' input[name="adresstyp"]:checked' ).val(),
				betrag: getAmount(),
				periode: $( '#' + formId + ' :input[name="periode"]' ).val(),
				zahlweise:  $( '#zahlweise' ).val()
			};
		if ( formData.adresstyp !== 'anonym' ) {
			$.extend( formData, {
				country: $( '#' + formId + ' select[name="country"]' ).val(),
				email: $( '#' + formId + ' :input[name="email"]' ).val(),
				ort: $( '#' + formId + ' :input[name="ort"]' ).val(),
				plz: $( '#' + formId + ' :input[name="plz"]' ).val(),
				strasse: $( '#' + formId + ' :input[name="strasse"]' ).val()
			} );
		}
		if ( formData.adresstyp === 'person' ) {
			$.extend( formData, {
				anrede: $( '#' + formId + ' input[name="anrede"]:checked' ).val(),
				titel: $( '#' + formId + ' select[name="titel"]' ).val(),
				vorname: $( '#' + formId + ' :input[name="vorname"]' ).val(),
				nachname: $( '#' + formId + ' :input[name="nachname"]' ).val()
			} );
		} else if ( formData.adresstyp === 'firma' ) {
			$.extend( formData, {
				firma: $( '#' + formId + ' :input[name="firma"]' ).val(),
				anrede: 'Firma'
			} );
		}
		if ( formData.zahlweise === 'BEZ' ) {
			$.extend( formData, {
				bankname: $( '#' + formId + ' :input[name="bankname"]' ).val(),
				bic: $( '#' + formId + ' :input[name="bic"]' ).val(),
				blz: $( '#' + formId + ' :input[name="blz"]' ).val(),
				iban: $( '#' + formId + ' :input[name="iban"]' ).val(),
				konto: $( '#' + formId + ' :input[name="konto"]' ).val()
			} );
		}
		return formData;
	};

	/**
	 * Validates given data using API.
	 *
	 * @param {Object} data
	 * @return {Promise}
	 *         Resolved parameter:
	 *         {Object} object with keys:
	 *                  - validated: true or false,
	 *                  - fieldsMissingValue: array containing names of fields with invalid values,
	 *                  - fieldsWithInvalidValue: array containing names of missing obligatory fields.
	 */
	Form.prototype.validateData = function ( data ) {
		return banner.api.sendValidationRequest( data )
			.then( function ( responseData ) {
				if ( responseData.status === 'OK' ) {
					return {
						validated: true
					};
				} else {
					return {
						validated: false,
						fieldsMissingValue: responseData.missing,
						fieldsWithInvalidValue: responseData.invalid
					};
				}
			} );
	};

	/**
	 * @param {string[]} fieldsMissingValue
	 * @param {string[]} fieldsWithInvalidValue
	 */
	Form.prototype._applyValidationErrors = function ( fieldsMissingValue, fieldsWithInvalidValue ) {
		var self = this;
		this._applyAmountValidationErrors( fieldsMissingValue, fieldsWithInvalidValue );
		$( '#' + banner.config.form.formId + ' :input:not([type=hidden])' ).each( function ( index, element ) {
			if ( $.inArray( $( element ).attr( 'name' ), fieldsMissingValue ) > -1 ) {
				self._markMissing( $( element ) );
				return true;
			}
			if ( $.inArray( $( element ).attr( 'name' ), fieldsWithInvalidValue ) > -1 ) {
				self._markInvalid( $( element ) );
				return true;
			}
			self._markValid( $( element ) );
		} );
	};

	Form.prototype._applyAmountValidationErrors = function ( fieldsMissingValue, fieldsWithInvalidValue ) {
		var valueMissingIndex = $.inArray( 'betrag', fieldsMissingValue ),
			valueInvalidIndex = $.inArray( 'betrag', fieldsWithInvalidValue ),
			errorBox, errorText = '';
		if ( valueInvalidIndex > -1 ) {
			errorText = 'Bitte geben Sie einen gültigen Betrag ein.' ;
			fieldsWithInvalidValue.splice( valueInvalidIndex, 1 );
			// Invalid values cause this
			if ( valueMissingIndex > -1 ) {
				fieldsMissingValue.splice( valueMissingIndex, 1 );
			}
		}
		if ( valueMissingIndex > -1 ) {
			errorText = 'Bitte geben Sie einen Betrag ein.';
			fieldsMissingValue.splice( valueMissingIndex, 1 );
		}
		if ( errorText ) {
			errorBox = this._showError( this.amountValidationAnchor, errorText );
			this._addErrorRemovalHandler( $( '#' + banner.config.form.formId + ' .amount-radio' ), errorBox, 'click' );
			this._addErrorRemovalHandler( $( '#amount-other-input' ), errorBox );
		}
	};

	Form.prototype._markInvalid = function ( $element ) {
		this._showError( $element );
	};

	Form.prototype._markMissing = function ( $element ) {
		// Since the server-side validation does not get missing fields except for amount,
		// we use a more generic error message and handle the errors the same
		this._showError( $element );
	};

	/**
	 * Set the element parent class to "invalid", show invalid icon and add error text
	 *
	 * @param {jQuery} $element
	 * @return {jQuery} The error box
	 */
	Form.prototype._showError = function ( $element ) {
		var $parent = $element.parent(),
			errorText, $errorBox;
		if ( arguments.length > 1 ) {
			errorText = arguments[ 1 ];
		} else {
			errorText = 'Bitte korrigieren Sie dieses Feld.';
		}
		$element.addClass( 'invalid' );
		$( '.validation', $parent ).addClass( 'icon-bug' );
		if ( !$( '.form-field-error-box', $parent ).length ) {
			$errorBox = $(
				'<div class="form-field-error-box"><div class="form-field-error-arrow"></div><span class="form-field-error-text">' +
				errorText +
				'</span></div></div>'
			);
			$parent.append( $errorBox );
			this._addErrorRemovalHandler( $element, $errorBox );
			this._addErrorRemovalHandler( $( '#WMDE_BannerForm-payment button' ), $errorBox, 'click' );
		}
		return $errorBox;
	};

	Form.prototype._addErrorRemovalHandler = function ( $element, $errorBox, eventName ) {
		eventName = eventName || 'focus';
		$element.one( eventName, function () {
			$errorBox.remove();
		} );
	};

	Form.prototype._markValid = function ( $element ) {
		var $parent = $element.parent();
		$element.addClass( 'valid' );
		$( '.validation', $parent ).addClass( 'icon-ok' );
	};

	banner.form = new Form();

}( Banner, jQuery ) );
