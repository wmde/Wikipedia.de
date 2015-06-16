$( function () {
	$( ':radio' ).change(
		function ( evt ) {
			$( 'input[name="' + $( this ).attr( 'name' ) + '"]' ).removeClass( '__checked' );

			if ( $( this ).is( ':checked' ) ) {
				$( this ).addClass( '__checked' );
			} else {
				$( this ).removeClass( '__checked' );
			}

			$( this ).blur();
		}
	);
} );
