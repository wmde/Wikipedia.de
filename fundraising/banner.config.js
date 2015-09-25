/**
 * Banner configuration
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
( function( Banner ) {
	'use strict';
	
	Banner.config = {
		encryption: {
			libUrl: '../fundraising/openpgp.min.js',
			publicKey: '',
			elementId: ''
		},
		tracking: {
			campaign: 'default',
			keyword: 'default',
			accessCode: 'default',
			baseUrl: 'https://spenden.wikimedia.de/',
			libUrl: 'https://spenden.wikimedia.de/piwik/piwik.js',
			trackerUrl: 'https://spenden.wikimedia.de/piwik/piwik.php',
			siteId: 1,
			events: {
				BANNER_CLOSED: {
					pathName: 'banner-closed',
					clickElement: '',
					sample: 0
				},
				BANNER_EXPANDED: {
					pathName: 'banner-expanded',
					clickElement: '',
					sample: 0
				},
				BANNER_SHOWN: {
					pathName: 'banner-shown',
					clickElement: '',
					sample: 0
				},
				LIGHTBOX_CLICKED: {
					pathName: 'lightbox-clicked',
					clickElement: '',
					sample: 0
				},
				LINK_CLICKED: {
					pathName: 'link-clicked',
					clickElement: '',
					sample: 0
				}
			}
		},
		setConfig: function( settings ) {
			$.extend( true, Banner.config, settings );
		}
	};

} )( Banner );
