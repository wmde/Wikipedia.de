<?php
require_once __DIR__ . "/../../inc/wikibox.class.php";

/**
 * @covers WikiBox
 *
 * @licence GNU GPL v2+
 * @author Kai Nissen
 */
class WikiBoxTest extends \PHPUnit_Framework_TestCase {

	public function listProvider() {
		return array(
				array(
						'* [[Web:Banner/Testbanner]]',
						'Web:Banner/Testbanner'
				),
				array(
						'# [[Web:Banner/Testbanner]]',
						null
				),
				array(
						<<<'NOW'
# [[Web:Banner/Testbanner]]
* [[Web:Banner/Some_other_banner]]
NOW
				,
						'Web:Banner/Some_other_banner'
				),
		);
	}

	/** @dataProvider listProvider */
	public function testPickPageReturnsPageContent( $listContent, $expectedPageTitle ) {
		$wikibox = $this->createNewWikiBox();

		$wikibox->expects( $this->at( 0 ) )
				->method( 'rip_page' )
				->with( 'Web:Banner', 'raw' )
				->willReturn( $listContent );

		if ( $expectedPageTitle ) {
			$wikibox->expects( $this->at( 1 ) )
					->method( 'rip_page' )
					->with( $expectedPageTitle, 'render' );
		}

		$wikibox->pick_page( 'Web:Banner' );
	}

	public function testWhenPassingTestPageTitleNotInListAndMarkedAsTest_pickPageReturnsPageContent() {
		$wikibox = $this->createNewWikiBox();

		$wikibox->expects( $this->any() )
				->method( 'rip_page' )
				->will( $this->onConsecutiveCalls( '', 'wikibox-test' ) );

		$test = $wikibox->pick_page( 'Web:Banner', true, 'Web:Banner/Some_banner' );
		$this->assertEquals( 'wikibox-test', $test );
	}

	public function testWhenPassingTestPageTitleNotInList_pickPageReturnsErrorMessage() {
		$wikibox = $this->createNewWikiBox();

		$wikibox->expects( $this->at( 0 ) )
				->method( 'rip_page' )
				->with( 'Web:Banner', 'raw' )
				->willReturn( '' );

		$wikibox->expects( $this->at( 1 ) )
				->method( 'rip_page' )
				->with( 'Web:Banner/Some_banner', 'render' )
				->willReturn( 'page content' );

		$test = $wikibox->pick_page( 'Web:Banner', true, 'Banner/Some_banner' );
		$this->assertEquals(
				'<i class="error">inactive feature page not marked with "wikibox-test": Web:Banner/Some_banner</i>',
				$test
		);
	}

	public function templateListProvider() {
		return array(
				array(
						'* {{BannerDefinition|title=Some_banner|campaign=unittest|secondaryTitle=Some_other_banner|maxImp=5|maxOverallImp=10}}',
						'Some_banner',
						0,
						0
				),
				array(
						'* {{BannerDefinition|title=Some_banner|campaign=unittest|secondaryTitle=Some_other_banner|maxImp=5|maxOverallImp=10}}',
						'Some_other_banner',
						'unittest|7',
						7
				),
				array(
						'* {{BannerDefinition|title=Some_banner|campaign=unittest|secondaryTitle=Some_other_banner|maxImp=5|maxOverallImp=10}}',
						false,
						'unittest|12',
						12
				)
		);
	}

	/** @dataProvider templateListProvider */
	public function testWhenPassingTemplate_pickPageReturnsPageBasedOnParameters(
			$listContent, $expectedPageTitle, $impCount, $overallImpCount
	) {
		$cookieJar = $this->getMockBuilder( '\WMDE\wpde\CookieJar' )
				->disableOriginalConstructor()
				->setMethods( array( 'getCookie' ) )
				->getMock();

		$map = array(
				array( 'impCount', $impCount ),
				array( 'overallImpCount', $overallImpCount )
		);

		$cookieJar->expects( $this->any() )
				->method( 'getCookie' )
				->will( $this->returnValueMap( $map ) );

		$wikibox = $this->createNewWikiBox( $cookieJar );

		$wikibox->expects( $this->at( 0 ) )
				->method( 'rip_page' )
				->with( 'Web:Banner', 'raw' )
				->willReturn( $listContent );

		if ( $expectedPageTitle ) {
			$wikibox->expects( $this->at( 1 ) )
					->method( 'rip_page' )
					->with( $expectedPageTitle, 'render' );
		}

		$wikibox->pick_page( 'Web:Banner' );
	}

	private function createNewWikiBox( $cookieJar = null ) {
		if ( !$cookieJar ) {
			$cookieJar = $this->getMockBuilder( '\WMDE\wpde\CookieJar' )
					->setMethods( array( 'getCookie', 'setCookie' ) )
					->getMock();
		}
		return $this->getMockBuilder( 'WikiBox' )
				->setConstructorArgs( array( 'test.url', null, null, $cookieJar ) )
				->setMethods( array( 'rip_page' ) )
				->getMock();
	}

}
