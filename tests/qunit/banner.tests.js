Banner.config.setConfig(
	{
		tracking: {
			campaign: 'some-campaign-name',
			events: {
				BANNER_CLOSED: {
					sample: 0.8,
					clickElement: '#qunit-fixture'
				},
				BANNER_EXPANDED: {
					sample: 0
				}
			}
		}
	}
);

QUnit.test( 'setConfig() overrides configuration properly', function ( assert ) {
	assert.equal(
		Banner.config.tracking.events.BANNER_CLOSED.sample,
		0.8,
		'configuration overwritten'
	);

	assert.equal(
		Banner.config.tracking.campaign,
		'some-campaign-name',
		'configuration overwritten'
	);
} );

QUnit.test( 'Banner.tracking.shouldTrack() returns correct values', function ( assert ) {
	assert.equal( Banner.tracking.shouldTrack( 'BANNER_CLOSED', 0.05 ), true );
	assert.equal( Banner.tracking.shouldTrack( 'BANNER_CLOSED', 0.85 ), false );
	assert.equal( Banner.tracking.shouldTrack( 'BANNER_EXPANDED', 0.05 ), false );
} );

QUnit.test( 'Click handlers are set', function( assert ) {
	assert.ok( $._data( $( '#qunit-fixture' ).get( 0 ), 'events' ) );
	assert.notOk( $._data( $( '#qunit' ).get( 0 ), 'events' ) );
} );
