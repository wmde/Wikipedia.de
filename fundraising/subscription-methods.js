$( function () {
	$( '#subscriptionForm' ).on( 'submit', function ( e ) {
		e.preventDefault();
		sendInitialServerRequest();
		return false;
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

		if ( response.status === 'OK'
			|| ( response.status === 'ERR' && response.errors.moderation )
			|| ( response.status === 'ERR' && response.errors.duplicate ) ) {
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
