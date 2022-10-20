<?php

namespace SRF\Tests\Outline;

use SMW\Tests\PHPUnitCompat;
use SRF\Outline\OutlineResultPrinter;

/**
 * @covers \SRF\Outline\OutlineResultPrinter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class OutlineResultPrinterTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $queryResult;

	protected function setUp(): void {
		parent::setUp();

		$this->queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			OutlineResultPrinter::class,
			new OutlineResultPrinter( 'outline' )
		);
	}

	public function testGetResult_LinkOnNonFileOutput() {
		$link = $this->createMock( \SMWInfolink::class );

		$link->expects( $this->any() )
			->method( 'getText' )
			->will( $this->returnValue( 'foo_link' ) );

		$this->queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->will( $this->returnValue( [] ) );

		$this->queryResult->expects( $this->any() )
			->method( 'getCount' )
			->will( $this->returnValue( 1 ) );

		$instance = new OutlineResultPrinter(
			'outline'
		);

		// IParam is an empty interface !!! so we use stdClass
		$outlineproperties = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$outlineproperties->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'outlineproperties' ) );

		$outlineproperties->expects( $this->any() )
			->method( 'getValue' )
			->will( $this->returnValue( [] ) );

		$template = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$template->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'template' ) );

		$template->expects( $this->any() )
			->method( 'getValue' )
			->will( $this->returnValue( '' ) );

		$introtemplate = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$introtemplate->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'introtemplate' ) );

		$introtemplate->expects( $this->any() )
			->method( 'getValue' )
			->will( $this->returnValue( '' ) );

		$outrotemplate = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$outrotemplate->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'outrotemplate' ) );

		$outrotemplate->expects( $this->any() )
			->method( 'getValue' )
			->will( $this->returnValue( '' ) );

		$parameters = [
			$outlineproperties,
			$template,
			$introtemplate,
			$outrotemplate
		];

		$this->assertContains(
			"<ul>\n</ul>\n",
			$instance->getResult( $this->queryResult, $parameters, SMW_OUTPUT_HTML )
		);
	}

}
