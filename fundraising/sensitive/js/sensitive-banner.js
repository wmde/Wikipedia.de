var isOpen = false;
var paySEPA = true;
var addressType = 'private';

$( function() {
	var paymentButtons = $( '#WMDE_BannerForm-payment button' ),
		fundsBox = new BannerModalInfobox( 'funds' ),
		taxesBox = new BannerModalInfobox( 'taxes' ),
		bitcoinBox = new BannerModalInfobox( 'bitcoin' ),
		dataProtectionBox = new BannerModalInfobox( 'dataprotection' );

	unlockForm();

	$( '#interval_onetime' ).on( 'click', function() {
		$( '#WMDE_BannerForm-wrapper' ).css( 'height', '158px' );
		$( '.interval-options input[name=interval]' ).prop( 'checked', false );
	} );
	$( '#interval_multiple' ).on( 'click', function() {
		$( '#WMDE_BannerForm-wrapper' ).css( 'height', '204px' );
		$( '#interval1' ).prop( 'checked', 'checked' );
	} );

	paymentButtons.on( 'click', function( e ) {
		e.preventDefault();
	} );

	$( '#WMDE_BannerFullForm-finish' ).on( 'click', function( e ) {
		$( this ).trigger( "blur" );
		$( this ).addClass( 'waiting' );
		lockForm();
	} );

	$( '#WMDE_BannerFullForm-finish-sepa' ).on( 'click', function( e ) {
		e.preventDefault();
		if ( $( '#confirm_sepa').prop( 'checked' ) && $( '#confirm_shortterm' ).prop( 'checked' ) ) {
			$( '#donationForm' ).submit();
		}
		else {
			$( '#confirm_sepa, #confirm_shortterm' ).each( function (index, element ) {
				var $element = $( element ), p;
				if ( $element.prop( 'checked' ) ) {
					return;
				}
				p = $element.parent();
				p.css( { border: 'red 1px solid' } );
				$element.on( 'click', function () {
					p.css( { border: 'none' } );
				});
			} );
		}
	} );

	$( '#WMDE_BannerFullForm-close-step1' ).on( 'click', function() {
		hideFullForm();
		unlockForm();
	} );
	$( '#WMDE_BannerFullForm-close-step2' ).on( 'click', function() {
		$( '#WMDE_BannerFullForm-step2' ).slideToggle( 400, function() {
			$( '#WMDE_BannerFullForm-step1' ).slideToggle();
		} );
		hideFullForm();
		unlockForm();
	} );


	paymentButtons.hover( function() {
			if ( !isOpen ) $( '#WMDE_BannerFullForm-arrow' ).show();
		},
		function() {
			$( '#WMDE_BannerFullForm-arrow' ).hide();
		} );

	$( 'input[name=\'debit-type\']' ).on( 'click', function() {
		toggleDebitType();
	} );

	$( '#address-type-1' ).on( 'click', function() {
		$( '#WMDE_BannerFullForm-company' ).slideUp();
		$( '#WMDE_Banner-person' ).slideDown();
		$( '#WMDE_Banner-address' ).slideDown();
		addressType = 'private';
	} );

	$( '#address-type-2' ).on( 'click', function() {
		$( '#WMDE_Banner-person' ).slideUp();
		$( '#WMDE_BannerFullForm-company' ).slideDown();
		$( '#WMDE_Banner-address' ).slideDown();
		addressType = 'company';
	} );

	$( '#address-type-3' ).on( 'click', function() {
		$( '#WMDE_BannerFullForm-company' ).slideUp();
		$( '#WMDE_Banner-person' ).slideUp();
		$( '#WMDE_Banner-address' ).slideUp();
		addressType = 'anonymous';
	} );

	// set validation event handlers
	$( '#donationForm' ).on( 'banner:validationFailed', function() {
		unlockForm();
		$( '#WMDE_BannerFullForm-finish' ).removeClass( 'waiting' );
	} );

	$( '#donationForm' ).on( 'banner:validationSucceeded', function( evt ) {
		unlockForm();
		if ( $( '#zahlweise' ).val() === 'BEZ' ) {
			debitNextStep();
			evt.preventDefault();
		}
		else {
			this.submit();
		}
	} );

} );

function lockForm() {
	$( 'button' ).prop( 'disabled', true );
	$( 'input' ).prop( 'disabled', true );
	$( 'select' ).prop( 'disabled', true );
}

function unlockForm() {
	$( 'button' ).prop( 'disabled', false );
	$( 'input' ).prop( 'disabled', false );
	$( 'select' ).prop( 'disabled', false );
}

function toggleDebitType() {
	$( '#WMDE_Banner-sepa' ).slideToggle();
	$( '#WMDE_BannerFullForm-nosepa' ).slideToggle();
	paySEPA = !paySEPA;
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
	isOpen = false;
	$( '#WMDE_BannerFullForm-details' ).slideUp( 400, function() {
		$( '#WMDE_Banner' ).css( 'position', 'fixed' );
		resetButtons();
	} );
	$( '#WMDE_BannerFullForm-info' ).hide();
	$( '#WMDE_BannerFullForm-shadow' ).fadeOut();
}

function debitNextStep() {
	$( '#WMDE_BannerFullForm-step1' ).slideToggle( 400, function() {
		$( '#WMDE_BannerFullForm-step2' ).slideToggle();
	} );

	fillConfirmationValues();

	$( "html, body" ).animate( {
		scrollTop: 0
	}, "slow" );
}

function fillConfirmationValues() {
	$( '#WMDE_BannerFullForm-confirm-amount' ).text( getAmount() );
	$( '#WMDE_BannerFullForm-confirm-salutation' ).text( getSalutation() );
	$( '#WMDE_BannerFullForm-confirm-street' ).text( $( '#street' ).val() );
	$( '#WMDE_BannerFullForm-confirm-city' ).text( $( '#post-code' ).val() + ' ' + $( '#city' ).val() );
	$( '#WMDE_BannerFullForm-confirm-country' ).text( getCountryByCode ( $( '#country' ).val() ) );
	$( '#WMDE_BannerFullForm-confirm-mail' ).text( $( '#email' ).val() );
	$( '#WMDE_BannerFullForm-confirm-IBAN' ).text( $( '#iban' ).val() );
	$( '#WMDE_BannerFullForm-confirm-BIC' ).text( $( '#bic' ).val() );
	$( '#WMDE_BannerFullForm-confirm-bankname' ).text( $( '#bank-name' ).val() );
}

function getSalutation() {
	var companyName = $( '#company-name' ).val();
	if ( companyName !== '' ) {
		return companyName;
	}

	var firstName = $( '#first-name' ).val();
	var lastName = $( '#last-name' ).val();
	var title = $( '#personal-title' ).val();
	var salutation = '';

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

/* Payment methods show and hide */

function showDebitDonation( button ) {
	if ( $( '#zahlweise' ).val() === 'BEZ' ) {
		hideFullForm();
	} else {
		$( '#zahlweise' ).val( 'BEZ' );
		$( '#form_action' ).prop( 'name', 'go_prepare--pay:einzug' );
		$( '#WMDE_Banner-debit-type' ).slideDown();
		$( '#WMDE_Banner-anonymous' ).slideUp();
		$( '#WMDE_BannerFullForm-finish' ).hide();
		$( '#WMDE_BannerFullForm-next' ).show();
		resetAddressType();
		resetButtons();
		$( button ).addClass( 'active' );
		showFullForm();
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
		showNonDebitParts( button );
	}
}

function showCreditDonation( button ) {
	if ( $( '#zahlweise' ).val() === 'MCP' ) {
		hideFullForm();
	} else {
		$( '#zahlweise' ).val( 'MCP' );
		$( '#form_action' ).prop( 'name', 'go_prepare--pay:micropayment-i' );
		showNonDebitParts( button );
	}
}

function showPayPalDonation( button ) {
	if ( $( '#zahlweise' ).val() === 'PPL' ) {
		hideFullForm();
	} else {
		$( '#zahlweise' ).val( 'PPL' );
		$( '#form_action' ).prop( 'name', 'go_prepare--pay:paypal' );
		showNonDebitParts( button );
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
	$( '#WMDE_BannerForm-payment button' ).trigger( "blur" );
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

BannerModalInfobox.prototype.toggle = function( e ) {
	if ( this.$box.hasClass( 'opened' ) ) {
		this.$box.trigger( 'banner:closeInfobox' );
	}
	else {
		this.$box.trigger( 'banner:openInfobox' );
	}
}

BannerModalInfobox.prototype.open = function( e ) {
	// close other banners
	$( '.banner-unique' ).trigger( 'banner:closeInfobox' );

	// wait for the slide-out to be done before showing banner
	$( '.banner-unique' ).promise().done( function() {
		$( '#WMDE_BannerFullForm-info' ).addClass( this.boxName );
		this.$box.addClass( 'opened' );
		this.$box.slideDown();
	}.bind( this ) );
}

BannerModalInfobox.prototype.close = function( e ) {
	if ( !this.$box.hasClass( 'opened' ) ) {
		return;
	}
	this.$box.slideUp( 400, function() {
		this.$box.removeClass( 'opened' );
		this.$link.removeClass( 'opened' );
		$( '#WMDE_BannerFullForm-info' ).removeClass( this.boxName );
	}.bind( this ) );
}
