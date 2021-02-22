<?php

namespace SRF\Tests\Unit\Formats;

use SRF\Gallery;
use TraditionalImageGallery;
use Title;
use SMW\Test\QueryPrinterRegistryTestCase;

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
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GalleryTest extends QueryPrinterRegistryTestCase {

	private $queryResult;
	private $title;

	protected function setUp(): void {
		parent::setUp();

		$this->queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$this->title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->title->expects( $this->any() )
			->method( 'getNamespace' )
			->will( $this->returnValue( NS_MAIN ) );
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

	public function testGetName() {

		$instance = new Gallery(
			'gallery'
		);

		$this->assertInternalType('string', $instance->getName());

	}

	public function testBuildResult(){

		$instance = new Gallery(
			'gallery'
		);

		$widget = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->setMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$widget->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'widget' ) );

		$widget->expects( $this->any() )
			->method( 'getValue' )
			->will( $this->returnValue( 'carousel' ) );


		$intro = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->setMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$intro->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'intro' ) );

		$intro->expects( $this->any() )
			->method( 'getValue' )
			->will( $this->returnValue( '<div class="gallery-intro">' ) );


		$outro = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->setMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$outro->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'outro' ) );

		$outro->expects( $this->any() )
			->method( 'getValue' )
			->will( $this->returnValue( '</div>' ) );

		$parameters = [
			$widget,
			$intro,
			$outro
		];

		$this->assertContains(
			'',
			$instance->getResult( $this->queryResult, $parameters, SMW_OUTPUT_HTML )
		);
	}

}
