var isOpen = false;
var paySEPA = true;
var addressType = 'private';

$( function() {
	var paymentButtons = $( '#WMDE_BannerForm-payment button' );

	unlockForm();

	$( '#interval_onetime' ).on( 'click', function() {
		$( '#WMDE_BannerForm-wrapper' ).css( 'height', '158px' );
		$( '.interval-options input[name=interval]').prop( 'checked', false );
	} );
	$( '#interval_multiple' ).on( 'click', function() {
		$( '#WMDE_BannerForm-wrapper' ).css( 'height', '204px' );
		$( '#interval1').prop( 'checked', 'checked' );
	} );

	paymentButtons.on( 'click', function( e ) {
		e.preventDefault();
	} );

	$( '#WMDE_BannerFullForm-next' ).on( 'click', function( e ) {
		e.preventDefault();
		debitNextStep();
	} );

	$( '#WMDE_BannerFullForm-finish' ).on( 'click', function( e ) {
		e.preventDefault();
		$( this ).trigger( "blur" );
		$( this ).addClass( 'waiting' );
		lockForm();
	} );

	$( '#WMDE_BannerFullForm-finish-sepa' ).on( 'click', function( e ) {
		e.preventDefault();
		$( '#WMDE_BannerFullForm-step2' ).slideToggle( 400, function() {
			$( '#WMDE_BannerFullForm-step1' ).slideToggle();
		} );
		hideFullForm();
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
	$( '#WMDE_BannerFullForm-details' ).slideUp( 400, function() {
		$( '#WMDE_Banner' ).css( 'position', 'fixed' );
		resetButtons();
		isOpen = false;
	} );
	$( '#WMDE_BannerFullForm-info' ).hide();
	$( '#WMDE_BannerFullForm-shadow' ).fadeOut();
}

function debitNextStep() {
	$( '#WMDE_BannerFullForm-step1' ).slideToggle( 400, function() {
		$( '#WMDE_BannerFullForm-step2' ).slideToggle();
	} );
	$( "html, body" ).animate( {
		scrollTop: 0
	}, "slow" );
}

/* Payment methods show and hide */

function showDebitDonation( button ) {
	resetButtons();
	$( button ).addClass( 'active' );
	$( '#zahlweise' ).val( 'BEZ' );
	$( '#WMDE_Banner-debit-type' ).slideDown();
	$( '#WMDE_Banner-anonymous' ).slideUp();
	$( '#WMDE_BannerFullForm-finish' ).hide();
	$( '#WMDE_BannerFullForm-next' ).show();
	resetAddressType();
	showFullForm();
}

function resetAddressType() {
	$( '#address-type-1' ).trigger( 'click' );
}

function showDepositDonation( button ) {
	$( '#zahlweise' ).val( 'UEB' );
	showNonDebitParts( button );
}

function showCreditDonation( button ) {
	$( '#zahlweise' ).val( 'MCP' );
	showNonDebitParts( button );
}

function showPayPalDonation( button ) {
	$( '#zahlweise' ).val( 'PPL' );
	showNonDebitParts( button );
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

function toggleFundsBox() {
	if ( $( '#WMDE_BannerFullForm-funds-link' ).hasClass( 'opened' ) ) {
		hideFundsBox();
	} else {
		showFundsBox();
	}
}

function toggleBitCoinBox() {
	if ( $( '#WMDE_BannerFullForm-bitcoin-link' ).hasClass( 'opened' ) ) {
		hideBitCoinBox();
	} else {
		showBitCoinBox();
	}
}

function toggleTaxBox() {
	if ( $( '#WMDE_BannerFullForm-taxes-link' ).hasClass( 'opened' ) ) {
		hideTaxBox();
	} else {
		showTaxBox();
	}
}

function showFundsBox() {
	hideBitCoinBox( function() {
		hideTaxBox( function() {
			$( '#WMDE_BannerFullForm-info' ).addClass( 'funds' );
			$( '#WMDE_BannerFullForm-funds' ).slideDown();
		} );
	} );
}

function showBitCoinBox() {
	hideFundsBox( function() {
		hideTaxBox( function() {
			$( '#WMDE_BannerFullForm-bitcoin' ).slideDown();
		} );
	} );
}

function showTaxBox() {
	hideFundsBox( function() {
		hideBitCoinBox( function() {
			$( '#WMDE_BannerFullForm-info' ).addClass( 'taxes' );
			$( '#WMDE_BannerFullForm-taxes' ).slideDown();
		} );
	} );
}

function hideFundsBox( whenDone ) {
	$( '#WMDE_BannerFullForm-funds' ).slideUp( 400, function() {
		$( '#WMDE_BannerFullForm-info' ).removeClass( 'funds' );
		$( '#WMDE_BannerFullForm-funds-link' ).removeClass( 'opened' );
		if ( $.isFunction( whenDone ) ) {
			whenDone();
		}
	} );

}

function hideBitCoinBox( whenDone ) {
	$( '#WMDE_BannerFullForm-bitcoin' ).slideUp( 400, function() {
		$( '#WMDE_BannerFullForm-bitcoin-link' ).removeClass( 'opened' );
		if ( $.isFunction( whenDone ) ) {
			whenDone();
		}

	} );
}

function hideTaxBox( whenDone ) {

	$( '#WMDE_BannerFullForm-taxes' ).slideUp( 400, function() {
		$( '#WMDE_BannerFullForm-info' ).removeClass( 'taxes' );
		$( '#WMDE_BannerFullForm-taxes-link' ).removeClass( 'opened' );
		if ( $.isFunction( whenDone ) ) {
			whenDone();
		}
	} );

}
