/**
 * Piwik tracker dummy
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
Piwik = {
	getTracker: function () {
		return {
			trackPageView: function ( url ) {
				return url;
			}
		};
	}
};
