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

		$this->queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
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
			->willReturn( 'foo_link' );

		$this->queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->willReturn( [] );

		$this->queryResult->expects( $this->any() )
			->method( 'getCount' )
			->willReturn( 1 );

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
			->willReturn( 'outlineproperties' );

		$outlineproperties->expects( $this->any() )
			->method( 'getValue' )
			->willReturn( [] );

		$template = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$template->expects( $this->any() )
			->method( 'getName' )
			->willReturn( 'template' );

		$template->expects( $this->any() )
			->method( 'getValue' )
			->willReturn( '' );

		$introtemplate = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$introtemplate->expects( $this->any() )
			->method( 'getName' )
			->willReturn( 'introtemplate' );

		$introtemplate->expects( $this->any() )
			->method( 'getValue' )
			->willReturn( '' );

		$outrotemplate = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'getName', 'getValue' ] )
			->getMock();

		$outrotemplate->expects( $this->any() )
			->method( 'getName' )
			->willReturn( 'outrotemplate' );

		$outrotemplate->expects( $this->any() )
			->method( 'getValue' )
			->willReturn( '' );

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
