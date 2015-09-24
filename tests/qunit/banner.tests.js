/**
 * JavaScript library for tracking functionalities
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */
Banner.config.setConfig(
	{
		encryption: {
			publicKey:
				'-----BEGIN PGP PUBLIC KEY BLOCK-----\n' +
				'Version: GnuPG v1\n' +
				'\n' +
				'mQENBFYD6z4BCAC7Z2l4MQYLBEmkxxaYURUcF3+9SejFqU21IKlmF33vD3PyA4uA\n' +
				'QReUd0Ri/9dWs09ugxzzvAHlbdenAQRMnITtS+XMrwGmHzDCikVqJXja422d1oIw\n' +
				'G/pY7z8V0hL+RkgjXoyGsgcCjXBghnTm3b7c344jtjommamXZf/lkFDSgF9zGruY\n' +
				'Q09LjGjoDYLfmiZjK0nGqtdgimKuv/RjgxZcRqUTDqeDaeU6TgQll9Hewq40jzNE\n' +
				'pPhUGlDsehYh0VA4mP6+u05L2LCU+PGAJhH4gmQWvbUmA5AmgY6kct+BZzj0B2Iq\n' +
				'eKAcMWtaYoJqJZ6WQ4Qj8EP7V+AUGrw/devZABEBAAG0T1dpa2ltZWRpYSBGw7Zy\n' +
				'ZGVyZ2VzZWxsc2NoYWZ0IG1iSCAoZm9ybSBkYXRhIGVuY3J5cHRpb24pIDxzcGVu\n' +
				'ZGVuQHdpa2ltZWRpYS5kZT6JATgEEwECACIFAlYD6z4CGwMGCwkIBwMCBhUIAgkK\n' +
				'CwQWAgMBAh4BAheAAAoJEG+cB9pKWGBZAPgH/0yk4W/ANmi4TNC+eNa/H6PIrYhy\n' +
				'lszUuV/eBQMdTq/cISHEzCVE7nGrNnVTNAYNhIQsXcxmLZLb/N4VbMeOi8oWKjhL\n' +
				'wOGp54q0wcn+j+PX6DOb76PlrmD0bPfBcBVlZjomFDG7G47FatBwlMUiJ5F3afla\n' +
				's9WTOT0G4QKxONizQuFn/SKDtQiWXAE/g3CNQEV9IkF2/C7vrjd42XvMMbFUBL0p\n' +
				'eUaRezwWQCzMGZyZpkyx2TvHy3AuL40FKjEP0q8EwU9rOKIkHhU6ZDZwa7c/NInm\n' +
				'YnAmTgu4PWd8WkQqcySM4WB4wuJkL8NI9HsJ6uhkqo5RYaJ1P8v8ObBex6G5AQ0E\n' +
				'VgPrPgEIANQjvo49krunfnbZiFKMw4UxEcLxvRR8YwimSqSLLrVRSpg4qhVpy/+U\n' +
				'MnsyCHdM39cqXB07UgeaD46bNyOJdD8KAi2Dx8fDsqnB0fWX0ugMzXI5/fpX/0gf\n' +
				'JBPzk+DLNhMRDErn6vL1ccMmL3owt2k+lhBke0YkwbAwd1IXKEslx3e8KdATAhUy\n' +
				'E7Rzxe0zGBICQvHr6WLJFolOUaU9dxOQg1x4bfAl6HzjIaPLfnCztmxwghraVfnE\n' +
				'5PG/KGkzk0Hcx+NneViB3Z7wphdww+/ME7a9zo3YMHjJRxAdE2nR30opEtxSJBDs\n' +
				'WCKPaef42Q7edn7UzWeE1WDJ1t4zARUAEQEAAYkBHwQYAQIACQUCVgPrPgIbDAAK\n' +
				'CRBvnAfaSlhgWQP1B/kBC3ToX8YjF2JTWRHFbpIOeyx4fLYDlfFoXcfAC73F+poW\n' +
				'+WOuYZPL+nj9EbW66O+1GmuDE/lwTfAOjaUO70Mhdlqkv80Qs6R3ZkPY7lFpd8Ty\n' +
				'qyCfzX9hMcfCF6tzDqgLtraZqn+IneeJsNAWwlci6hVBii5ViO4h5Xnotf0N4uPk\n' +
				'gNBfjvxAjyqlGXNdrherLDWnO8duGkweljawLsJs5i9kjdeX2rRSeQk8v4cTryHk\n' +
				'i3PXWsOFIfIAlCm6l1rqYc21hM0n5mmixG9wob+VHK9tpBtR9tBkz6cbrMH6KCD9\n' +
				'BajtiwDB5VEi/cAO8Bc7lsgXsMbuy06fGuro8ZY+\n' +
				'=9ldq\n' +
				'-----END PGP PUBLIC KEY BLOCK-----\n'
		},
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

QUnit.test( 'Encryption', function( assert ) {
	var $inputField = $( '<input id="enc" />' );
	var done = assert.async();

	$( '#qunit-fixture' ).append( $inputField );
	Banner.encryption.encrypt( 'Hello, Dexter Morgan!', $( '#enc' ) );

	setTimeout( function() {
		assert.ok( $inputField.val() !== '' );
		done();
	}, 0 );
} );