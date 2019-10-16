var WikimediaTracker = null;
var FundraisingTracker = null;

const trackingData = {
	WikimediaTracker: {
		url: '//stats.wikimedia.de/',
		siteID: 3
	},
	FundraisingTracker: {
		url: '//tracking.wikimedia.de/',
		siteID: 3
	}
};

window.piwikAsyncInit = function () {
	WikimediaTracker = Matomo.getTracker( trackingData.WikimediaTracker.url + 'matomo.php', trackingData.WikimediaTracker.siteID );
	FundraisingTracker = Matomo.getTracker( trackingData.FundraisingTracker.url + 'matomo.php', trackingData.FundraisingTracker.siteID );

	// FundraisingTracker only tracks events
	WikimediaTracker.trackPageView();
	WikimediaTracker.enableLinkTracking();
};

( function() {
	var d = document, g = d.createElement( 'script' ), s = d.getElementsByTagName( 'script' )[ 0 ];
	g.type = 'text/javascript';
	g.async = true;
	g.defer = true;
	g.src = trackingData.WikimediaTracker.url + 'matomo.js';
	s.parentNode.insertBefore( g, s );
} )();