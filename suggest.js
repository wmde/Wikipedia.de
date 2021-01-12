var ajaxCallTimeout = 5000;
var suggestTimeout = null;
var delay = 500;
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
		$.ajax( 'suggest.php', {
			data: {
				lang: searchLang,
				search: str
			},
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

function getSearchLink( query, language, provider ) {
	var queryParams = {
		l: language,
		q: query
	};

	if ( typeof provider === 'string' ) {
		queryParams.e = provider;
		queryParams.s = 'search';
	}

	return searchPath + '?' + $.param( queryParams );
}

function handleSearchSuggest( response ) {
	var searchString = lastSearch;
	if( response == null ) return;

	var ss = $( '#search_suggest' ).empty().show();
	var searchResults = response.split( "\n" );

	// Removing first element because it's the search string itself
	searchResults.shift();

	// Removing the last element because it is always an empty string
	searchResults.pop();

	$.each( searchResults, function( index, row ) {

		var entry = row.split( "\t" );
		ss.append(
			$( '<div></div>' )
				.addClass( 'suggest_link' )
				.append( $( '<a></a>' ).attr( 'href', getSearchLink( entry[0], searchLang ) )
					.append(
						$( '<span></span>' )
							.addClass( searchString.toLowerCase() === entry[0].toLowerCase() ? 'exact-match' : 'partial-match' )
							.addClass( 'search_result' )
							.text( entry[0] )
					)
				)
		);
	} );

	if( searchResults.length === 0 ) {
		ss.append(
			$( '<div></div>' )
				.addClass( 'suggest_link' )
				.append(
					$( '<span></span>' )
						.addClass( 'search_result' )
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

function triggerPiwikTrack( element, code ) {
	var piwikImgUrl = 'https://tracking.wikimedia.de/piwik.php?idsite=1&url=https://www.wikipedia.de/link-clicked/' + code + '&rec=1';
	$(element).append( '<img src="' + piwikImgUrl + '" width="0" height="0" border="0" />' );
}
