/*jshint latedef: nofunc */
/*jshint unused: false */
/* globals mw, alert, GlobalBannerSettings */
var finalDateTime = new Date( 2016, 0, 1, 5, 0, 0 ),
	goalSum = 8700000,
	baseDate = replaceWikiVars( '{{{donations-date-base}}}' ),
	collectedBase = parseInt( replaceWikiVars( '{{{donations-collected-base}}}' ), 10 ),
	donorsBase = parseInt( replaceWikiVars( '{{{donators-base}}}' ), 10 ),
	donationsPerMinApproximation = parseFloat( replaceWikiVars( '{{{appr-donations-per-minute}}}' ) ),
	donorsPerMinApproximation = parseFloat( replaceWikiVars( '{{{appr-donators-per-minute}}}' ) )
	;

$( function () {
	$( '#WMDE_Banner-close' ).click( function () {
		if ( Math.random() < 0.01 ) {
			$( '#WMDE_Banner-close-ct' ).attr( 'src', replaceWikiVars( 'https://spenden.wikimedia.de/piwik/piwik.php?idsite=1&url=https://spenden.wikimedia.de/banner-closed/{{{BannerName}}}&rec=1' ) );
		}
		mw.centralNotice.hideBanner();
		removeBannerSpace();
		return false;
	} );

	$( '#amount-other-input' ).on( 'click', function () {
		$( 'input:radio[name=betrag_auswahl]' ).prop( 'checked', false );
		$( '#amount_other' ).prop( 'checked', true );
	} );
	$( '#amount_other' ).on( 'click', function () {
		$( '#amount-other-input' ).trigger( 'click' );
	} );
	$( 'input:radio[name=betrag_auswahl]' ).on( 'click', function () {
		$( '#amount_other' ).prop( 'checked', false );
	} );
	$( '#interval_onetime' ).on( 'click', function () {
		$( '.interval-options' ).addClass( 'interval-hidden' );
		$( '#interval_multiple' ).prop( 'checked', false );
	} );
	$( '#interval_multiple' ).on( 'click', function () {
		$( '.interval-options' ).removeClass( 'interval-hidden' );
		$( '#interval_onetime' ).prop( 'checked', false );
	} );
} );

function getDaysLeft() {
	var daysLeft = Math.floor( new Date( finalDateTime - new Date() ) / 1000 / 60 / 60 / 24 );
	return ( daysLeft < 0 ) ? 0 : daysLeft;
}

function getDaysRemaining() {
	var daysRemaining = getDaysLeft();
	// TODO manually hack to fix older banners from 2014
	if ( daysRemaining === 0 ) {
		$( '#donationRemaining' ).width( 0 );
		$( '#donationRemaining' ).html( '' );
	}
	return ( daysRemaining !== 1 ) ? daysRemaining + ' Tage' : '1 Tag';
}

function getSecsPassed() {
	var startDate = baseDate.split( '-' ),
		startDateObj = new Date( startDate[ 0 ], startDate[ 1 ] - 1, startDate[ 2 ] ),
		maxSecs = Math.floor( new Date( finalDateTime - startDateObj ) / 1000 ),
		secsPassed = Math.floor( ( new Date() - startDateObj ) / 1000 );

	if ( secsPassed < 0 ) {
		secsPassed = 0;
	}
	if ( secsPassed > maxSecs ) {
		secsPassed = maxSecs;
	}

	return secsPassed;
}

function getApprDonationsRaw( rand ) {
	var startDonations = collectedBase,
		secsPast = getSecsPassed();

	return startDonations + getApprDonationsFor( secsPast, rand );
}

function getApprDonatorsRaw( rand ) {
	var startDonators = donorsBase,
		secsPast = getSecsPassed();

	return startDonators + getApprDonatorsFor( secsPast, rand );
}

function getApprDonationsFor( secsPast, rand ) {
	var apprDontionsMinute = donationsPerMinApproximation,
		randFactor = 0;

	if ( rand === true ) {
		randFactor = Math.floor( ( Math.random() ) + 0.5 - 0.2 );
	}

	return ( secsPast / 60 * ( apprDontionsMinute * ( 100 + randFactor ) ) / 100 );
}

function getApprDonatorsFor( secsPast, rand ) {
	var apprDonatorsMinute = donorsPerMinApproximation,
		randFactor = 0;

	if ( rand === true ) {
		randFactor = Math.floor( ( Math.random() ) + 0.5 - 0.2 );
	}

	return ( secsPast / 60 * ( apprDonatorsMinute * ( 100 + randFactor ) ) / 100 );
}

function getCurrentGermanDay() {
	switch ( new Date().getDay() ) {
		case 0:
			return 'Sonntag';
		case 1:
			return 'Montag';
		case 2:
			return 'Dienstag';
		case 3:
			return 'Mittwoch';
		case 4:
			return 'Donnerstag';
		case 5:
			return 'Freitag';
		case 6:
			return 'Samstag';
		default:
			return '';
	}
}

function addPointsToNum( num ) {
	// jscs:disable disallowImplicitTypeConversion
	num = parseInt( num, 10 ) + '';
	// jscs:enable disallowImplicitTypeConversion
	num = num.replace( /\./g, ',' );
	return num.replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1.' );
}

function floorF( num ) {
	return Math.floor( num * 100 ) / 100;
}

function increaseImpCount() {
	var impCount = parseInt( $.cookie( 'centralnotice_banner_impression_count' ), 10 ) || 0;
	$.cookie( 'centralnotice_banner_impression_count', impCount + 1, { expires: 7, path: '/' } );
	return impCount + 1;
}

function increaseBannerImpCount( bannerId ) {
	var impCount = 0,
		impCountCookie, bannerImpCount;

	if ( $.cookie( 'centralnotice_single_banner_impression_count' ) ) {
		impCountCookie = $.cookie( 'centralnotice_single_banner_impression_count' );
		bannerImpCount = impCountCookie.split( '|' );
		if ( bannerImpCount[ 0 ] === bannerId ) {
			impCount = parseInt( bannerImpCount[ 1 ], 10 );
		}
	}
	$.cookie( 'centralnotice_single_banner_impression_count', bannerId + '|' + ( impCount + 1 ), {
		expires: 7,
		path: '/'
	} );
	return ( impCount + 1 );
}

function validateForm() {
	var chkdPayment = $( 'input[name=pay]:checked', '#WMDE_BannerForm' ).val(),
		form = document.donationForm,
		error = false,
		amount;

	switch ( chkdPayment ) {
		case 'BEZ':
			$( '#form-page' ).val( 'Formularseite2-Lastschrift' );
			break;
		case 'UEB':
			$( '#form-page' ).val( 'Formularseite2-Überweisung' );
			break;
		case 'PPL':
			$( '#form-page' ).val( 'Formularseite2-PayPal' );
			break;
		case 'MCP':
			$( '#form-page' ).val( 'Formularseite2-Micropayment' );
			break;
	}

	if ( $( '#interval_multiple' ).is( ':checked' ) ) {
		if ( $( 'input[name=interval]:checked', form ).length !== 1 ) {
			alert( 'Es wurde kein Zahlungsintervall ausgewählt.' );
			return false;
		} else {
			$( '#intervalType' ).val( '1' );
			$( '#periode' ).val( $( 'input[name=interval]:checked', form ).val() );
		}
	} else {
		$( '#periode' ).val( '0' );
	}

	amount = getAmount();

	// Check amount is at least the minimum
	if ( amount < 1 || error ) {
		alert( 'Der Mindestbetrag beträgt 1 Euro.' );
		return false;
	} else if ( amount > 99999 ) {
		alert( 'Der Spendenbetrag ist zu hoch.' );
		return false;
	}
	return amount;
}

function getAmount() {
	var amount = null,
		otherAmount = $( '#amount-other-input' ).val(),
		form = document.donationForm;

	amount = $( 'input[name=betrag_auswahl]:checked' ).val();

	if ( otherAmount !== '' ) {
		otherAmount = otherAmount.replace( /[,.](\d)$/, '\:$10' );
		otherAmount = otherAmount.replace( /[,.](\d)(\d)$/, '\:$1$2' );
		otherAmount = otherAmount.replace( /[\$,.]/g, '' );
		otherAmount = otherAmount.replace( /:/, '.' );
		$( '#amount-other-input' ).val( otherAmount );
		amount = otherAmount;
	}

	if ( amount === null || isNaN( amount ) || amount.value <= 0 ) {
		return false;
	}

	return amount;
}

function addBannerSpace() {
	var expandableBannerHeight = $( 'div#WMDE_Banner' ).height() + 44,
		bannerDivElement = $( '#WMDE_Banner' );

	switch ( 'vector' ) {
		// switch ( skin ) { TODO fix when non-static
		case 'vector':
			bannerDivElement.css( 'top', 0 - expandableBannerHeight );
			$( '#mw-panel' ).animate( { top: expandableBannerHeight + 160 }, 1000 );
			$( '#mw-head' ).animate( { top: expandableBannerHeight }, 1000 );
			$( '#mw-page-base' ).animate( { paddingTop: expandableBannerHeight }, 1000 );
			break;
		case 'monobook':
			$( '#globalWrapper' ).css( 'position', 'relative' );
			$( '#globalWrapper' ).css( 'top', expandableBannerHeight );
			bannerDivElement.css( 'top', '-20px' );
			bannerDivElement.css( 'background', 'none' );
			break;
	}
	bannerDivElement.css( 'display', 'block' );
	bannerDivElement.animate( { top: 0 }, 1000 );
	setTimeout( animateProgressBar, 1000 );
}

function removeBannerSpace() {
	switch ( 'vector' ) {
		// switch ( skin ) { TODO fix when non-static
		case 'vector':
			$( '#mw-panel' ).css( 'top', 160 );
			$( '#mw-head' ).css( 'top', 0 );
			$( '#mw-page-base' ).css( 'padding-top', 0 );
			break;
		case 'monobook':
			$( '#globalWrapper' ).css( 'position', 'relative' );
			$( '#globalWrapper' ).css( 'top', 0 );
			break;
	}
}

function animateProgressBar() {
	var donationFillElement = $( '#donationFill' ),
		daysLeftElement = $( '#daysLeft' ),
		donationValueElement = $( '#donationValue' ),
		remainingValueElement = $( '#valRem' ),
		preFillValue = 0,
		barWidth, dTarget, dCollected, dRemaining, fWidth, maxFillWidth, widthToFill,
		fillToBarRatio;

	donationFillElement.clearQueue();
	donationFillElement.stop();
	donationFillElement.width( preFillValue + 'px' );

	daysLeftElement.hide();

	barWidth = $( '#donationMeter' ).width();
	dTarget = parseInt( '8300000', 10 );
	dCollected = getApprDonationsRaw();
	dRemaining = dTarget - dCollected;

	fWidth = dCollected / dTarget * barWidth;
	maxFillWidth = barWidth - $( '#donationRemaining' ).width() - 16;
	widthToFill = ( fWidth > maxFillWidth ) ? maxFillWidth : fWidth;
	fillToBarRatio = widthToFill / barWidth;
	if ( fillToBarRatio < 0.15 ) {
			widthToFill = 0.15 * barWidth;
			if ( widthToFill > 100 ) {
					widthToFill = 100;
			}
	}

	donationFillElement.animate( { width: widthToFill + 'px' }, {
		duration: 3000,
		progress: function () {
			var dFill = donationFillElement.width() / widthToFill * fWidth,
				pFill = dFill / barWidth,

				dColl = dTarget * pFill / 1000000,
				vRem = ( dTarget - ( dTarget * pFill ) ) / 1000000;
				setCollectedAndRemaining( dColl, vRem );
		},
		complete: function () {
			setCollectedAndRemaining( dCollected / 1000000, dRemaining / 1000000 );
			$( '#donationText' ).show();
			$( '#donationRemaining' ).show();
			daysLeftElement.show();
		}
	} );

	function setCollectedAndRemaining( donationsCollected, donationsRemaining ) {
		donationsCollected = donationsCollected.toFixed( 1 );
		donationsCollected = donationsCollected.replace( '.', ',' );

		donationsRemaining = donationsRemaining.toFixed( 1 );
		donationsRemaining = donationsRemaining.replace( '.', ',' );

		remainingValueElement.html( donationsRemaining );
		donationValueElement.html( donationsCollected );
	}
}

/**
 * For instances where this JS is not generated by a Wiki, try to replace the
 * placeholders with values fram a global object
 */
function replaceWikiVars( text ) {
	var re = /\{\{\{([^\}]+)\}\}\}/,
		wikiVarMatch;
	while ( ( wikiVarMatch = re.exec( text ) ) !== null ) {
		if ( GlobalBannerSettings[ wikiVarMatch[ 1 ] ] ) {
			text = text.replace( wikiVarMatch[ 0 ], GlobalBannerSettings[ wikiVarMatch[ 1 ] ] );
		}
	}
	return text;
}
