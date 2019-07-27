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
	public function testGetFileName( $filename, $expected ) {

		$parameters = [
			'filename' => $filename
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

	public function filenameProvider() {

		yield[
			'',
			'vCard.vcf'
		];

		yield[
			'foo',
			'foo.vcf'
		];

		yield[
			'foo.vcf',
			'foo.vcf'
		];
	}

}
