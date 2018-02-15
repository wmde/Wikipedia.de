var ajaxCallTimeout = 5000;
var suggestTimeout = null;
var delay = 500;
var searchLang = "de";
var lastSearch = "";
var searchProviders = {
	default: [
		{
			title: 'Wikipedia',
			anchor: 'auf wikipedia.org suchen',
			linkId: 'wikipedia',
			icon: 'favicon.ico'
		}
	],
	languageExtras: {
		de: [
			{
				title: 'T-Online',
				anchor: 't-online.de',
				linkId: 't-online',
				icon: 'img/t-online.ico'
			},
			{
				title: 'web.de',
				anchor: 'web.de',
				linkId: 'web.de',
				icon: 'img/web.de.ico'
			},
			{
				title: 'exalead',
				anchor: 'exalead.de',
				linkId: 'exalead',
				icon: 'img/exalead.ico'
			},
			{
				title: 'Wikiwix',
				anchor: 'wikiwix.com',
				linkId: 'wikiwix',
				icon: 'img/wikiwix.ico'
			},
			{
				title: 'EyePlorer',
				anchor: 'eyeplorer.com',
				linkId: 'eyeplorer',
				icon: 'img/eyeplorer.ico'
			}
		]
	}
};

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

function getSearchProvidersForLanguage( language ) {
	var activeSearchProviders = searchProviders.default;
	if ( searchProviders.languageExtras.hasOwnProperty( language ) ) {
		activeSearchProviders = activeSearchProviders.concat( searchProviders.languageExtras[language] );
	}
	return activeSearchProviders;
}

function handleSearchSuggest( response ) {
	var searchString = lastSearch;
	if( response == null ) return;

	var ss = $( '#search_suggest' ).empty().show(),
		str = response.split( "\n" ),
		searchProviders = getSearchProvidersForLanguage( searchLang )
	;

	ss.append(
		$( '<div></div>' )
			.addClass( 'suggest_link' )
			.text( 'Treffer für ' + searchString )
	);

	$.each( str, function( index, row ) {
		// skip first row, it's the search string itself
		if( index === 0 ) return true;

		var entry = row.split( "\t" );

		ss.append(
			$( '<div></div>' )
				.addClass( 'suggest_link' )
				.append(
					$( '<a></a>' )
						.attr( 'href', 'go?l=' + searchLang + '&q=' + entry[0] )
						.addClass( searchString === entry[0] ? 'exact-match' : 'partial-match' )
						.text( entry[0] )
				)
		);
	} );
	ss.append( '<hr noshade size=1 style="background-color:#ffffff;">' );

	$.each( searchProviders, function ( index, provider ) {
		ss.append(
			$( '<div></div>' )
				.addClass( 'suggest_link' )
				.append(
					$( '<img>' )
						.attr( 'src', provider.icon )
						.attr( 'title', 'Suchen mit ' + provider.title ),
					$( '<a></a>' )
						.attr( 'href', 'go?l=' + searchLang + '&q=' + searchString + '&e=' + provider.linkId + '&s=search' )
						.text( provider.anchor )
				)
		);
	} );
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
	var piwikImgUrl = 'https://spenden.wikimedia.de/piwik/piwik.php?idsite=1&url=https://www.wikipedia.de/link-clicked/' + code + '&rec=1';
	$(element).prepend( '<img src="' + piwikImgUrl + '" width="0" height="0" border="0" />' );
}
