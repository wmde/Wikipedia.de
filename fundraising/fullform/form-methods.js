var bankCheckPending = false;
var mailCheckPending = false;

$( document ).ready( function() {
	/* pass form field changes to lightbox form */
	var formFieldMapping = {
		/* amounts */
		amount5: '#amount-1',
		amount15: '#amount-2',
		amount25: '#amount-3',
		amount50: '#amount-4',
		amount75: '#amount-5',
		amount100: '#amount-6',
		amount250: '#amount-7',
		
		/* intervals */
		interval0: '#periode-1',
		interval1: '#periode-2, #periode-2-1',
		interval3: '#periode-2, #periode-2-2',
		interval6: '#periode-2, #periode-2-3',
		interval12: '#periode-2, #periode-2-4',
		
		/* buttons */
		btnTransfer: '#payment-type-1',
		btnCreditCard: '#payment-type-2',
		btnDirectDebit: '#payment-type-3',
		btnPayPal: '#payment-type-4'
	};

	$( 'input[type=radio], button' ).click( function( evt ) {
		var clickedId = evt.target.id;
		if( formFieldMapping[evt.target.id] ) {
			$( formFieldMapping[clickedId] ).click();
		}
	} );

	$( 'input[name=amountGiven]' ).change( function() {
		console.log( "amount changed, triggering" );
		$( '#amount-8' ).val( $( 'input[name=amountGiven]' ).val() ).change();
	} );

	$( document ).bind( 'change', function( e ) {
		if ( ( $( e.target ).hasClass( "required" ) || $( e.target ).hasClass( "optional" ) )
				&& !$( e.target ).hasClass( "bank-check" ) ) {

			var vIcon = $( e.target ).next();
			if( e.target.validity.valid ){
				$( e.target ).addClass( 'valid' );
        		$( e.target ).removeClass( 'invalid' );
        		vIcon.addClass( 'icon-ok' );
				vIcon.removeClass( 'icon-bug' );
				vIcon.removeClass( 'icon-placeholder' );
      		} else {
        		$( e.target ).addClass( 'invalid' );
        		$( e.target ).removeClass( 'valid' );
        		vIcon.addClass( 'icon-bug' );
        		vIcon.removeClass( 'icon-ok' );
        		vIcon.removeClass( 'icon-placeholder' );
      		}
    	}
  	});

	$( '#donFormSubmit, #memFormSubmit' ).on( 'click', function( e ) {
		if ( mailCheckPending ) {
			checkMailAddress( true );
			return false;
		}
		if ( bankCheckPending ) {
			checkBankData( true );
			return false;
		}
		if( $( '#donForm' )[0].checkValidity ) {
			console.log( "checkValidity seems to be a method" );
			if( $('#donForm')[0].checkValidity() ) {
				console.log( "form is valid, closing overlay" );
				$( '#lBoxForm' ).dialog( 'close' );
				//$( '#lBoxForm' )[0].reset();
			}
		} else {
			console.log( "form validity cannot be checked, closing overlay" );
			$( '#lBoxForm' ).dialog( 'close' );
			$( '#donForm' )[0].reset();
		}
	});
	
	$( '#payment-type-1, #payment-type-2, #payment-type-4' ).on( 'click', function() {
		$( 'section#donation-payment' ).find( 'input[type=text]' ).each( function() {
			$( this )[0].setCustomValidity( "" );
			$( this ).val( "" );
		} );
	} );

	$( ".iban-check, .bank-check" ).on( 'change', function( evt ) {
		checkBankData( evt, false );
	});

	$( "#email" ).on( 'change', function( evt ) {
		mailCheckPending = true;
		checkMailAddress( false );
	});

	function checkBankData( evt, submit ) {
		bankCheckPending = true;
		var url = '';

		if ( evt.target.id === 'account-number' || evt.target.id === 'bank-code' ) {
			if( $( '#account-number' ).val() === '' || $( '#bank-code' ).val() === '' ) {
				return;
			}
			url = "https://spenden.wikimedia.de/ajax.php?action=generateIban&bankCode=" + $( "#bank-code" ).val() + "&accNum=" + $( "#account-number" ).val() + "&callback=?";
			$( '#iban, #bic' ).val( '' );
			$( '#bank-name' ).text( '' );
		} else if ( evt.target.id === 'iban' ) {
			url = "https://spenden.wikimedia.de/ajax.php?action=checkIban&iban=" + $( "#iban" ).val() + "&callback=?";
			$( '#account-number, #bank-code' ).val( '' );
			$( '#bank-name' ).text( '' );
		}

		$.getJSON( url, function( data ) {
			if ( data.status === "OK" ) {
				$( '#iban' ).val( data.iban ? data.iban : '' );
				$( '#bank-name' ).text( data.bankName ? data.bankName : '' );
				$( '#field-bank-name' ).val( data.bankName ? data.bankName : '' );
				$( '#account-number' ).val( data.account ? data.account : '' );
				$( '#bank-code' ).val( data.bankCode ? data.bankCode : '' );

				setFieldsValid( $( '#bank-code, #account-number, #iban' ) );

				var $bic = $( '#bic' );
				if( $bic.hasClass( 'invalid' ) || data.bic ) {
					$bic.val( data.bic );
					setFieldsValid( $bic );
				} else if( !$bic.hasClass( 'valid' ) ) {
					$( "#bic" ).next().removeClass( "icon-bug icon-ok" );
				}
			} else {
				var $bankFields = $( "#bank-code, #account-number" );
				$bankFields.removeClass( "valid" ).addClass( "invalid" );
				$bankFields.next().removeClass( "icon-ok icon-placeholder" ).addClass( "icon-bug" );
				if( $( "#non-sepa" ).css( 'display' ) === 'block' ) {
					$bankFields.each( function( index, elmId ) {
						$( elmId )[0].setCustomValidity( "Die angegebene Bankverbindung ist nicht korrekt." );
					} );
				} else {
					$( "#bic, #iban" ).each( function( index, elmId ) {
						$( elmId )[0].setCustomValidity( "Die angegebene Bankverbindung ist nicht korrekt." );
					} );
				}
			}
			bankCheckPending = false;

			if( submit ) {
				$( '#donFormSubmit, #memFormSubmit' ).submit();
			}
		});
	}
	
	function setFieldsValid( $fields ) {
		$fields.removeClass( "invalid" ).addClass( "valid" );
		$fields.next().removeClass( "icon-bug icon-placeholder" ).addClass( "icon-ok" );
		$fields.each( function( index, elmId ) {
			$( elmId )[0].setCustomValidity( "" );
		} );
	}

	function checkMailAddress( submit ) {
		var url = "https://spenden.wikimedia.de/ajax.php?action=checkEmail&eaddr=" + $( "#email" ).val() + "&callback=?";
		$.getJSON( url, function( response ) {
			var $email = $( "#email" );
			if ( response.status === "OK" ) {
				$email.removeClass( "invalid" ).addClass( "valid" );
				$email.next().removeClass( "icon-bug" ).addClass( "icon-ok" );
				$email.get( 0 ).setCustomValidity( "" );
			} else {
				$email.removeClass( "valid" ).addClass( "invalid" );
				$email.next().removeClass( "icon-ok" ).addClass( "icon-bug" );
				$email.get( 0 ).setCustomValidity( "E-Mail-Adresse nicht korrekt" );
			}
			mailCheckPending = false;
			if ( submit ) {
				$( "#donFormSubmit" ).trigger( "click" );
			}
		});
	}
});
