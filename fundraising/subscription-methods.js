var mailCheckPending = false;

$( function () {
	$( document ).bind( 'change', function ( e ) {
		if ( $( e.target ).hasClass( "required" ) || $( e.target ).hasClass( "optional" ) ) {

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

	$( "#email" ).on( 'change', function ( evt ) {
		mailCheckPending = true;
		checkMailAddress( false );
	} );

	function setFieldsValid( $fields ) {
		$fields.removeClass( "invalid" ).addClass( "valid" );
		$fields.next().removeClass( "icon-bug icon-placeholder" ).addClass( "icon-ok" );
		$fields.each( function ( index, elmId ) {
			$( elmId )[0].setCustomValidity( "" );
		} );
	}

	function checkMailAddress( submit ) {
		var url = "//test.wikimedia.de/ajax.php";
		$.ajax( {
			url: url,
			type: "GET",
			dataType: "jsonp",
			data: { action: "checkEmail", eaddr: $( "#email" ).val() },
			success: checkMailResponse
		} );
	}

	function checkMailResponse( response ) {
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
