<?php

namespace SRF\Tests\BibTex;

use PHPUnit\Framework\MockObject\MockObject;
use SMWQueryResult;
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

	private $resultPrinterReflector;

	protected function setUp() {
		parent::setUp();

		$this->resultPrinterReflector = new ResultPrinterReflector();
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
	public function testGetFileName( $filename, $searchlabel, $expected ) {

		$parameters = [
			'filename' => $filename,
			'searchlabel' => $searchlabel
		];

		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->resultPrinterReflector->addParameters( $instance, $parameters );

		$this->assertEquals(
			$expected,
			$instance->getFileName( $this->newMockQueryResult() )
		);
	}

	/**
	 * @return MockObject|SMWQueryResult
	 */
	private function newMockQueryResult() {
		return $this->getMockBuilder( SMWQueryResult::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testGetMimeType() {

		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->assertEquals(
			'text/bibtex',
			$instance->getMimeType( $this->newMockQueryResult() )
		);
	}

	public function testGetResult_LinkOnNonFileOutput() {

		$link = $this->getMockBuilder( '\SMWInfolink' )
			->disableOriginalConstructor()
			->getMock();

		$link->expects( $this->any() )
			->method( 'getText' )
			->will( $this->returnValue( 'foo_link' ) );

		$queryResult = $this->newMockQueryResult();

		$queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->will( $this->returnValue( [] ) );

		$queryResult->expects( $this->any() )
			->method( 'getCount' )
			->will( $this->returnValue( 1 ) );

		$queryResult->expects( $this->once() )
			->method( 'getQueryLink' )
			->will( $this->returnValue( $link ) );

		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->assertEquals(
			'foo_link',
			$instance->getResult( $queryResult, [], SMW_OUTPUT_HTML )
		);
	}

	public function filenameProvider() {

		yield[
			'',
			'',
			'BibTeX.bib'
		];

		yield[
			'',
			'foo bar',
			'foo_bar.bib'
		];

		yield[
			'foo',
			'',
			'foo.bib'
		];

		yield[
			'foo.bib',
			'',
			'foo.bib'
		];

		yield[
			'foo bar.bib',
			'',
			'foo_bar.bib'
		];
	}

}
