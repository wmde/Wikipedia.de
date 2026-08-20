var ajaxCallTimeout = 5000;
var suggestTimeout = null;
var delay = 500; // debounce delay between keypresses
var searchLang = "de";
var lastSearch = "";
var searchPath = 'go';

function triggerSuggestLater( lang ) {
	if ( suggestTimeout ) clearTimeout( suggestTimeout ); //kill suggestion timer
	suggestTimeout = setTimeout( "searchSuggest('" + lang + "')", delay );
}

function searchSuggest( lang ) {
	searchLang = lang;
	var str = $( '#txtSearch' ).val();

	if ( str == lastSearch ) return;
	lastSearch = str;

	if ( str == "" ) {
		hideSuggest();
	} else {
 		// See https://en.wikipedia.org/w/api.php?action=help&modules=opensearch
		$.ajax( 'https://' + lang + '.wikipedia.org/w/api.php', {
			data: {
				action: 'opensearch',
				search: str,
				format: 'json',
				origin: '*'
			},
			dataType: 'json',
			success: function( response ) {
				handleSearchSuggest( response )
			},
			timeout: ajaxCallTimeout
		} );
	}
}

function hideSuggest() {
	$( '#search_suggest' ).hide();
	lastSearch = "";
}

/**
 * The response is a 4-element array
 *  - search term
 *  - array of titles
 *  - array of excerpts (always empty strings)
 *  - array of URLs
 * All arrays have the same length.
 *
 * See https://en.wikipedia.org/w/api.php?action=help&modules=opensearch
 */
function handleSearchSuggest( response ) {
	if( response == null ) return;
	if( !Array.isArray(response) || response.length < 4) {
		console.log("Malformed search response", response);
		return;
	}
	var searchString = response[0];
	var [titles, urls] = [ response[1], response[3] ];

	var ss = $( '#search_suggest' ).empty().show();

	$.each( titles, function( index, title ) {

		ss.append(
			$( '<div class="suggest_link"></div>' )
				.append( $( '<a></a>' ).attr( 'href', urls[index] )
					.append(
						$( '<span class="search_result"></span>' )
							.addClass( searchString.toLowerCase() === title.toLowerCase() ? 'exact-match' : 'partial-match' )
							.text( title )
					)
				)
		);
	} );

	if( titles.length === 0 ) {
		ss.append(
			$( '<div class="suggest_link"></div>' )
				.append(
					$( '<span class="search_result"></span>' )
						.text( 'Es wurden keine Artikel gefunden.' )
				)
		);
	}
}

$( document ).ready( function() {
	$( 'body' ).on( 'mouseover', 'div.suggest_link', function() {
		$( this ).addClass( 'suggest_link_over' );
	} );
	$( 'body' ).on( 'mouseout', 'div.suggest_link', function() {
		$( this ).removeClass( 'suggest_link_over' );
	} )
} );
