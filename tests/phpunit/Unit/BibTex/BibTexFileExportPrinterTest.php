<?php

namespace SRF\Tests\BibTex;

use SRF\BibTex\BibTexFileExportPrinter;
use SRF\Tests\ResultPrinterReflector;

/**
 * @covers \SRF\BibTex\BibTexFileExportPrinter
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class BibTexFileExportPrinterTest extends \PHPUnit_Framework_TestCase {

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
			BibTexFileExportPrinter::class,
			new BibTexFileExportPrinter( 'bibtex' )
		);
	}

	/**
	 * @dataProvider filenameProvider
	 */
	public function testGetFileName( $filename, $expected ) {

		$parameters = [
			'filename' => $filename
		];

		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->resultPrinterReflector->addParameters( $instance, $parameters );

		$this->assertEquals(
			$expected,
			$instance->getFileName( $this->queryResult )
		);
	}

	public function testGetMimeType() {

		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->assertEquals(
			'text/bibtex',
			$instance->getMimeType( $this->queryResult )
		);
	}

	public function filenameProvider() {

		yield[
			'',
			'BibTeX.bib'
		];

		yield[
			'foo',
			'foo.bib'
		];

		yield[
			'foo.bib',
			'foo.bib'
		];
	}

}