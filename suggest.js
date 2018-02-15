var ajaxCallTimeout = 5000;
var suggestTimeout = null;
var delay = 500;
var searchLang = "de";
var lastSearch = "";

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

function handleSearchSuggest( response ) {
	var searchString = lastSearch;
	if( response == null ) return;

	var ss = $( '#search_suggest' ).empty().show();
	var str = response.split( "\n" );
	ss.append( '<div class="suggest_link">Treffer f&uuml;r "' + searchString + '"</div>' );

	$.each( str, function( index, row ) {
		// skip first row, it's the search string itself
		if( index === 0 ) return true;

		var entry = row.split( "\t" );
		var suggest = '<div class="suggest_link"><a href="go?l='+ searchLang +'&q=' + entry[0] + '">';

		if ( searchString == entry[0] ) {
			suggest += '<b>' + entry[0] + '</b>';
		} else {
			suggest += entry[0];
		}

		suggest += '</a> ';
		suggest += '</div>';
		ss.append( suggest );
	} );
	ss.append( '<hr noshade size=1 style="background-color:#ffffff;">' );
	ss.append( '<div class="suggest_link"><img src="favicon.ico" width="16" height="16" title="Suchen mit Wikipedia" border="0" >&nbsp;&nbsp;<a href="go?l=' + searchLang + '&e=wikipedia&s=search&q=' + searchString + '">auf wikipedia.org suchen</a></div>' );

	if (searchLang == "de") {
		var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
		suggest += 'onmouseout="javascript:suggestOut(this);" ';
		suggest += 'class="suggest_link"><img src="img/t-online.ico" width="16" height="16" title="Suchen mit T-Online" border="0" />&nbsp;&nbsp;<a href="go?l=' + searchLang + '&q=' + searchString + '&e=t-online&s=search">t-online.de</a></div>';
		ss.append( suggest );

		var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
		suggest += 'onmouseout="javascript:suggestOut(this);" ';
		suggest += 'class="suggest_link"><img src="img/web.de.ico" width="16" height="16" title="Suchen mit web.de" border="0" />&nbsp;&nbsp;<a href="go?l=' + searchLang + '&q=' + searchString + '&e=web.de&s=search">web.de</a></div>';
		ss.append( suggest );

		var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
		suggest += 'onmouseout="javascript:suggestOut(this);" ';
		suggest += 'class="suggest_link"><img src="img/exalead.ico" width="16" height="16" title="Suchen mit exalead" border="0" />&nbsp;&nbsp;<a href="go?l=' + searchLang + '&q=' + searchString + '&e=exalead&s=search">exalead.de</a></div>';
		ss.append( suggest );

		var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
		suggest += 'onmouseout="javascript:suggestOut(this);" ';
		suggest += 'class="suggest_link"><img src="img/wikiwix.ico" width="16" height="16" title="Suchen mit Wikiwix" border="0" />&nbsp;&nbsp;<a href="go?l=' + searchLang + '&q=' + searchString + '&e=wikiwix&s=search">wikiwix.com</a></div>';
		ss.append( suggest );

		var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
		suggest += 'onmouseout="javascript:suggestOut(this);" ';
		suggest += 'class="suggest_link"><img src="img/eyeplorer.ico" width="16" height="16" title="Suchen mit EyePlorer" border="0" />&nbsp;&nbsp;<a href="go?l=' + searchLang + '&q=' + searchString + '&e=eyeplorer&s=search">eyeplorer.com</a></div>';
		ss.append( suggest );
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
	var piwikImgUrl = 'https://spenden.wikimedia.de/piwik/piwik.php?idsite=1&url=https://www.wikipedia.de/link-clicked/' + code + '&rec=1';
	$(element).prepend( '<img src="' + piwikImgUrl + '" width="0" height="0" border="0" />' );
}
