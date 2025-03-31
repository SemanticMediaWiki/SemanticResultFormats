<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\PHPUnitCompat;
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

	use PHPUnitCompat;

	private $queryResult;
	private $title;

	protected function setUp(): void {
		parent::setUp();

		$this->queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$this->title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$this->title->expects( $this->any() )
			->method( 'getNamespace' )
			->willReturn( NS_MAIN );
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

		$this->assertContains(
			'',
			$instance->getResult( $this->queryResult, $parameters, SMW_OUTPUT_HTML )
		);
	}

}
