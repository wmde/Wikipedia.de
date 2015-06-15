var mailCheckPending = false;
var ajaxURL = '//test.wikimedia.de/ajax.php';

$( function () {
	$( document ).bind( 'change', function ( e ) {
		if ( $( e.target ).hasClass( 'required' ) || $( e.target ).hasClass( 'optional' ) ) {

			var vIcon = $( e.target ).next();
			if ( e.target.validity.valid ) {
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
	} );

	$( '#subscriptionFormSubmit' ).on( 'click', function ( e ) {
		if ( mailCheckPending ) {
			checkMailAddress( true );
			return false;
		}
	} );

	$( '#email' ).on( 'change', function ( evt ) {
		mailCheckPending = true;
		checkMailAddress( false );
	} );

	$( '#subscriptionForm' ).on( 'submit', function ( e ) {
		e.preventDefault();
		sendInitialServerRequest();
	} );

	function sendInitialServerRequest() {
		var data = $( '#subscriptionForm' ).serialize();

		startSpinner();
		resetErrorBox();

		$.ajax( {
			url: 'fundraising/proxy.php',
			type: 'POST',
			dataType: 'json',
			data: data,
			timeout: 8000,
			success: checkServerResponse,
			error: handleAjaxError
		} );
	}

	function checkServerResponse( response ) {
		stopSpinner();

		if ( response.status === 'OK' || ( response.status === 'ERR' && response.errors.duplicate ) ) {
			showConfirmation();
		} else {
			handleErrorResponse( response );
		}
	}

	function handleAjaxError() {
		stopSpinner();

		showErrorBox( 'Server nicht erreichbar.' );
	}

	function showConfirmation() {
		$( '#form-section' ).hide();
		$( '#thanks-section' ).show()
	}

	function handleErrorResponse( response ) {
		if ( response.errors ) {
			var errorMessage = '';
			Object.keys( response.errors ).forEach( function ( key ) {
				errorMessage += response.errors[key] + '</br>';
			} );
			showErrorBox( errorMessage );
		} else {
			showErrorBox( JSON.stringify( response ) );
		}
	}

	function showErrorBox( html ) {
		$( '#error-reason' ).html( html );
		$( '#errorbox' ).show();
	}

	function resetErrorBox() {
		$( '#errorbox' ).hide();
		$( '#error-reason' ).text( '' )
	}

	function startSpinner() {
		$( '#ajax-overlay' ).show();
		$( '#subscriptionFormSubmit' ).attr( 'disabled', 'disabled' );
	}

	function stopSpinner() {
		$( '#ajax-overlay' ).hide();
		$( '#subscriptionFormSubmit' ).removeAttr( 'disabled' );
	}

	function setFieldsValid( $fields ) {
		$fields.removeClass( 'invalid' ).addClass( 'valid' );
		$fields.next().removeClass( 'icon-bug icon-placeholder' ).addClass( 'icon-ok' );
		$fields.each( function ( index, elmId ) {
			$( elmId )[0].setCustomValidity( '' );
		} );
	}

	function checkMailAddress( submit ) {
		$.ajax( {
			url: ajaxURL,
			type: 'GET',
			dataType: 'jsonp',
			data: { action: 'checkEmail', eaddr: $( '#email' ).val() },
			timeout: 8000,
			success: function ( response ) {
				checkMailResponse( response, submit );
			},
			error: handleAjaxError
		} );
	}

	function checkMailResponse( response, submit ) {
		var $email = $( '#email' );
		if ( response.status === 'OK' ) {
			$email.removeClass( 'invalid' ).addClass( 'valid' );
			$email.next().removeClass( 'icon-bug' ).addClass( 'icon-ok' );
			$email.get( 0 ).setCustomValidity( '' );
		} else {
			$email.removeClass( 'valid' ).addClass( 'invalid' );
			$email.next().removeClass( 'icon-ok' ).addClass( 'icon-bug' );
			$email.get( 0 ).setCustomValidity( 'E-Mail-Adresse nicht korrekt' );
		}
		mailCheckPending = false;
		if ( submit ) {
			$( '#donFormSubmit' ).trigger( 'click' );
		}
	}

	function initStyledSelect() {
		$( 'select' ).selectmenu( {
			positionOptions: {
				collision: 'none'
			}
		} )
			.on( 'change', function ( evt, params ) {
				var $option = $( this ).find( '[value="' + $( this ).find( 'option:selected' ).val() + '"]' );

				if ( $option.attr( 'data-behavior' ) == 'placeholder' ) {
					$( '#' + $( this ).attr( 'id' ) + '-button' ).addClass( 'placeholder' );
				} else {
					$( '#' + $( this ).attr( 'id' ) + '-button' ).removeClass( 'placeholder' );
				}
			} )
			.change();

		// adjust position, margins & dimension
		$( '.ui-selectmenu' ).each( function () {
			var newWidth = $( this ).width() * 2 - $( this ).outerWidth();
			$( this ).width( newWidth );
		} );
		$( '.ui-selectmenu-menu' ).each( function () {
			var $dropDown = $( this ).find( '.ui-selectmenu-menu-dropdown' );
			$dropDown.width( $dropDown.width() - 2 );
		} );
	}

	initStyledSelect();
} );
