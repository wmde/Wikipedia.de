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

	function checkMailAddress( submit ) {
		$.ajax( {
			url: ajaxURL,
			type: 'GET',
			dataType: 'jsonp',
			data: { action: 'checkEmail', eaddr: $( '#email' ).val() },
			timeout: 8000,
			success: function ( response ) {
				checkMailResponse( response, submit, false );
			},
			error: function () {
				checkMailResponse( response, submit, true );
			}
		} );
	}

	function checkMailResponse( response, submit, error ) {
		var $email = $( '#email' );
		if ( response.status === 'OK' || error ) {
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
			$( '#subscriptionFormSubmit' ).trigger( 'click' );
		}
	}
} );
