<?php

namespace SRF\Tests\Unit\Formats;

use MediaWiki\Title\Title;
use SMW\Formatters\Infolink;
use SMW\Localizer\DeferredLocalizedMessage;
use SMW\Tests\QueryPrinterRegistryTestCase;
use SRF\Gallery;

/**
 * Tests for the SRF\Gallery class.
 *
 * @file
 * @since 1.8
 *
 * @ingroup SemanticResultFormats
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GalleryTest extends QueryPrinterRegistryTestCase {

	private $queryResult;
	private $title;

	protected function setUp(): void {
		parent::setUp();

		$this->queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$this->queryResult->method( 'getPrintRequests' )->willReturn( [] );
		$this->queryResult->method( 'getNext' )->willReturn( false );
		$this->queryResult->method( 'hasFurtherResults' )->willReturn( false );
		$this->queryResult->method( 'getErrors' )->willReturn( [] );

		$this->title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$this->title->expects( $this->any() )
			->method( 'getNamespace' )
			->willReturn( NS_MAIN );
	}

	/**
	 * Creates a minimal param mock returning the given name and value.
	 */
	private function createParamMock( string $name, $value ): object {
		$param = $this->getMockBuilder( \stdClass::class )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue', 'wasSetToDefault', 'getOriginalValue' ] )
			->getMock();
		$param->method( 'getName' )->willReturn( $name );
		$param->method( 'getValue' )->willReturn( $value );
		$param->method( 'wasSetToDefault' )->willReturn( true );
		$param->method( 'getOriginalValue' )->willReturn( $value );
		return $param;
	}

	/**
	 * Builds a complete set of Gallery param mocks with their defaults,
	 * allowing individual values to be overridden via $overrides.
	 *
	 * @param array<string,mixed> $overrides
	 * @return list<object>
	 */
	private function createDefaultParamMocks( array $overrides = [] ): array {
		$defaults = [
			// ResultPrinter base params
			'intro'           => '',
			'outro'           => '',
			'default'         => '',
			// Gallery-specific params
			'class'           => '',
			'widget'          => '',
			'navigation'      => 'nav',
			'overlay'         => false,
			'perrow'          => '',
			'widths'          => '',
			'heights'         => '',
			'autocaptions'    => true,
			'fileextensions'  => false,
			'captionproperty' => '',
			'redirects'       => '',
			'captiontemplate' => '',
			'imageproperty'   => '',
		];

		$merged = array_merge( $defaults, $overrides );
		$mocks  = [];
		foreach ( $merged as $name => $value ) {
			$mocks[] = $this->createParamMock( $name, $value );
		}
		return $mocks;
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'gallery' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRF\Gallery';
	}

	/**
	 * @covers Gallery getName
	 */
	public function testGetName() {
		$instance = new Gallery(
			'gallery'
		);

		$this->assertIsString( $instance->getName() );
	}

	/**
	 * @covers Gallery buildResult
	 */
	public function testBuildResult() {
		$instance = new Gallery(
			'gallery'
		);

		$widget = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$widget->expects( $this->any() )
			->method( 'getName' )
			->willReturn( 'widget' );

		$widget->expects( $this->any() )
			->method( 'getValue' )
			->willReturn( 'carousel' );

		$intro = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$intro->expects( $this->any() )
			->method( 'getName' )
			->willReturn( 'intro' );

		$intro->expects( $this->any() )
			->method( 'getValue' )
			->willReturn( '<div class="gallery-intro">' );

		$outro = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$outro->expects( $this->any() )
			->method( 'getName' )
			->willReturn( 'outro' );

		$outro->expects( $this->any() )
			->method( 'getValue' )
			->willReturn( '</div>' );

		$parameters = [
			$widget,
			$intro,
			$outro
		];

		$this->assertStringContainsString(
			'',
			$instance->getResult( $this->queryResult, $parameters, SMW_OUTPUT_HTML )
		);
	}

	/**
	 * Covers the widget parameter code paths (carousel / slideshow / empty).
	 * With no result rows the gallery is empty and the output HTML is ''.
	 * Primary purpose: ensure the widget-selection branches execute without
	 * error after == → === refactoring.
	 *
	 * @dataProvider provideWidgetValues
	 * @covers \SRF\Gallery::getResultText
	 */
	public function testGetResultTextWithWidgetParamReturnsExpectedStructure( string $widgetValue ): void {
		$instance = new Gallery( 'gallery' );
		$result = $instance->getResult(
			$this->queryResult,
			$this->createDefaultParamMocks( [ 'widget' => $widgetValue ] ),
			SMW_OUTPUT_HTML
		);
		// getResultText() returns ['html', 'nowiki' => true, 'isHTML' => true]
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'isHTML', $result );
	}

	public static function provideWidgetValues(): array {
		return [
			'no widget'        => [ '' ],
			'carousel widget'  => [ 'carousel' ],
			'slideshow widget' => [ 'slideshow' ],
		];
	}

	/**
	 * Covers the overlay=true code path in getImageOverlay() / getStandardWidget().
	 *
	 * @covers \SRF\Gallery::getResultText
	 */
	public function testGetResultTextWithOverlayEnabledReturnsExpectedStructure(): void {
		$instance = new Gallery( 'gallery' );
		$result = $instance->getResult(
			$this->queryResult,
			$this->createDefaultParamMocks( [ 'overlay' => true ] ),
			SMW_OUTPUT_HTML
		);
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'isHTML', $result );
	}

	/**
	 * Covers the class parameter code path.
	 *
	 * @covers \SRF\Gallery::getResultText
	 */
	public function testGetResultTextWithCustomClassReturnsExpectedStructure(): void {
		$instance = new Gallery( 'gallery' );
		$result = $instance->getResult(
			$this->queryResult,
			$this->createDefaultParamMocks( [ 'class' => 'my-custom-class' ] ),
			SMW_OUTPUT_HTML
		);
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'isHTML', $result );
	}

	/**
	 * SMW 7's default "further results" search label is a
	 * `DeferredLocalizedMessage` marker: a <span> resolved to text only after
	 * the parser cache. getResultText() must render the further-results link
	 * without HTML-escaping that marker, or the raw, unresolved <span> leaks
	 * into the page (#1058).
	 *
	 * @covers \SRF\Gallery::getResultText
	 */
	public function testGetResultTextResolvesDeferredLocalizedSearchLabelInsteadOfEscapingIt(): void {
		$marker = DeferredLocalizedMessage::newMarker( 'further-results' );

		$queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->method( 'getPrintRequests' )->willReturn( [] );
		$queryResult->method( 'getNext' )->willReturn( false );
		$queryResult->method( 'hasFurtherResults' )->willReturn( true );
		$queryResult->method( 'getErrors' )->willReturn( [] );
		$queryResult->method( 'getCount' )->willReturn( 1 );
		$queryResult->method( 'getQueryLink' )->willReturn(
			new Infolink( true, $marker, 'Special:Ask' )
		);

		// Gallery::getResultText() runs its further-results link through
		// Parser::recursiveTagParse(), which requires an in-progress parse
		// (as is always the case for the #ask parser function it's really
		// called from); prime one here since this test invokes it directly.
		\MediaWiki\MediaWikiServices::getInstance()->getParser()->parse(
			'', Title::newFromText( 'GalleryTestPage' ), \ParserOptions::newFromAnon()
		);

		$instance = new Gallery( 'gallery' );
		$result = $instance->getResult(
			$queryResult,
			$this->createDefaultParamMocks(),
			SMW_OUTPUT_HTML
		);

		// getResultText() returns ['html', 'nowiki' => true, 'isHTML' => true]
		$this->assertIsArray( $result );
		$this->assertStringContainsString( '<span class="' . DeferredLocalizedMessage::CLASS_NAME . '"', $result[0] );
		$this->assertStringNotContainsString( '&lt;span', $result[0] );
	}

}
