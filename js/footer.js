$( document ).ready( function() {
	$( '.selection-input' ).bind( 'click', function() {
		$( this ).addClass( 'active' );
		$( this ).find( '.selection-input-field' ).select();
	} );

	$( '.selection-input-field' ).bind( 'blur', function() {
		$( this ).parent().removeClass( 'active' );
	} )
} );
