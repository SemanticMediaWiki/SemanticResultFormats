<?php

namespace SRF\Tests\BibTex;

use PHPUnit\Framework\MockObject\MockObject;
use SMWInfolink;
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
		if ( version_compare( phpversion(), '7.4', '>=' ) ) {
			// ResultPrinterReflector creates notices on PHP 7.4+
			$this->markTestSkipped();
			return;
		}

		$parameters = [
			'filename' => $filename,
			'searchlabel' => $searchlabel
		];

		$bibTexPrinter = new BibTexFileExportPrinter( 'bibtex' );

		( new ResultPrinterReflector() )->addParameters( $bibTexPrinter, $parameters );

		$this->assertEquals(
			$expected,
			$bibTexPrinter->getFileName( $this->newQueryResultDummy() )
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

	/**
	 * @return MockObject|SMWQueryResult
	 */
	private function newQueryResultDummy() {
		return $this->createMock( SMWQueryResult::class );
	}

	public function testGetMimeType() {
		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->assertEquals(
			'text/bibtex',
			$instance->getMimeType( $this->newQueryResultDummy() )
		);
	}

	public function testGetResult_LinkOnNonFileOutput() {
		$bibTexPrinter = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->assertEquals(
			'foo_link',
			$bibTexPrinter->getResult(
				$this->newMockQueryResultWithLink(),
				[],
				SMW_OUTPUT_HTML
			)
		);
	}

	private function newMockQueryResultWithLink() {
		$queryResult = $this->newQueryResultDummy();

		$queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->will( $this->returnValue( [] ) );

		$queryResult->expects( $this->any() )
			->method( 'getCount' )
			->will( $this->returnValue( 1 ) );

		$queryResult->expects( $this->once() )
			->method( 'getQueryLink' )
			->will( $this->returnValue( $this->newInfoLinkStub() ) );

		return $queryResult;
	}

	private function newInfoLinkStub() {
		$link = $this->getMockBuilder( SMWInfolink::class )
			->disableOriginalConstructor()
			->getMock();

		$link->expects( $this->any() )
			->method( 'getText' )
			->will( $this->returnValue( 'foo_link' ) );

		return $link;
	}

}
