<?php

namespace SRF\Tests\vCard;

use SRF\vCard\vCardFileExportPrinter;
use SRF\Tests\ResultPrinterReflector;

/**
 * @covers \SRF\vCard\vCardFileExportPrinter
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class vCardFileExportPrinterTest extends \PHPUnit_Framework_TestCase {

	private $queryResult;
	private $resultPrinterReflector;

	protected function setUp() {
		parent::setUp();

		$this->resultPrinterReflector = new ResultPrinterReflector();

		$this->queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			vCardFileExportPrinter::class,
			new vCardFileExportPrinter( 'vcard' )
		);
	}

	/**
	 * @dataProvider filenameProvider
	 */
	public function testGetFileName( $filename, $searchlabel, $expected ) {

		$parameters = [
			'filename' => $filename,
			'searchlabel' => $searchlabel
		];

		$instance = new vCardFileExportPrinter(
			'vcard'
		);

		$this->resultPrinterReflector->addParameters( $instance, $parameters );

		$this->assertEquals(
			$expected,
			$instance->getFileName( $this->queryResult )
		);
	}

	public function testGetMimeType() {

		$instance = new vCardFileExportPrinter(
			'vcard'
		);

		$this->assertEquals(
			'text/x-vcard',
			$instance->getMimeType( $this->queryResult )
		);
	}

	public function testGetResult_LinkOnNonFileOutput() {

		$link = $this->getMockBuilder( '\SMWInfolink' )
			->disableOriginalConstructor()
			->getMock();

		$link->expects( $this->any() )
			->method( 'getText' )
			->will( $this->returnValue( 'foo_link' ) );

		$this->queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->will( $this->returnValue( [] ) );

		$this->queryResult->expects( $this->any() )
			->method( 'getCount' )
			->will( $this->returnValue( 1 ) );

		$this->queryResult->expects( $this->once() )
			->method( 'getQueryLink' )
			->will( $this->returnValue( $link ) );

		$instance = new vCardFileExportPrinter(
			'vcard'
		);

		$this->assertEquals(
			'foo_link',
			$instance->getResult( $this->queryResult, [], SMW_OUTPUT_HTML )
		);
	}

	public function filenameProvider() {

		yield[
			'',
			'foo bar',
			'foo_bar.vcf'
		];

		yield[
			'foo',
			'',
			'foo.vcf'
		];

		yield[
			'foo.vcf',
			'',
			'foo.vcf'
		];

		yield[
			'foo bar.vcf',
			'',
			'foo_bar.vcf'
		];
	}

}
