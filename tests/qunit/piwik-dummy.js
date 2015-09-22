Piwik = {
	getTracker: function () {
		return {
			trackPageView: function ( url ) {
				return url;
			}
		};
	}
};
