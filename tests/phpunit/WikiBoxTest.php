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

		$wikibox->expects( $this->at( 1 ) )
				->method( 'rip_page' )
				->with( $expectedPageTitle, 'render' );

		$wikibox->expects( $this->exactly( 2 ) )
				->method( 'rip_page' );

		$wikibox->pick_page( 'Web:Banner', true, false );
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

	private function createNewWikiBox() {
		return $this->getMockBuilder( 'WikiBox' )
				->setConstructorArgs( array( 'test.url' ) )
				->setMethods( array( 'rip_page' ) )
				->getMock();
	}

}
