/**
 * JavaScript library for tracking functionalities
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
( function( Banner ) {
	'use strict';
	
	var TP;

	function Tracking() {
		var self = this;
		this._tracker = null;

		$( document ).ready( function() {
			self.initTrackingLib();
			self.initClickHandlers();
		} );
	}

	TP = Tracking.prototype;
	
	TP.setTracker = function ( tracker ) {
		this._tracker = tracker;
	};

	TP.trackVirtualPageView = function ( eventName ) {
		if ( this.shouldTrack( eventName, this.getRandomNumber() ) ) {
			this._tracker.trackPageView(
				Banner.config.tracking.baseUrl +
				Banner.config.tracking.events[eventName].pathName +
				'/' +
				Banner.config.tracking.keyword
			);
		}
	};

	TP.shouldTrack = function ( eventName, randomNumber ) {
		return this._tracker &&
			Banner.config.tracking.events[eventName] &&
			Banner.config.tracking.events[eventName].sample > randomNumber;
	};
	
	TP.getRandomNumber = function () {
		return Math.random() * ( 1 - 0.01 ) + 0.01;
	};

	/**
	 * fetch the piwik library and get the specified tracker from it
	 */
	TP.initTrackingLib = function() {
		var self = this;
		$.ajax( {
			url: Banner.config.tracking.libUrl,
			dataType: 'script',
			cache: true,
			success: function() {
				var trackingConfig = Banner.config.tracking;
				self.setTracker( Piwik.getTracker( trackingConfig.trackerUrl, trackingConfig.siteId ) );
			}
		} );
	};

	/**
	 * bind click events to elements as configured
	 */
	TP.initClickHandlers = function() {
		$.each( Banner.config.tracking.events, function ( key, settings ) {
			$( settings.clickElement ).click( function () {
				Banner.tracking.trackVirtualPageView( key );
			} );
		} );
	};

	Banner.tracking = new Tracking();

} )( Banner );
