<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\QueryPrinterRegistryTestCase;
use SRF\SpreadsheetPrinter;

/**
 * @ingroup SemanticResultFormats
 * @ingroup Test
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 */
class SpreadsheetTest extends QueryPrinterRegistryTestCase {

	public function getFormats() {
		return [ 'spreadsheet' ];
	}

	public function getClass() {
		return 'SRF\SpreadsheetPrinter';
	}

	/**
	 * @covers Spreadsheet
	 *
	 */
	public function testLink() {
		$link = $this->getMockBuilder( '\SMWInfolink' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->once() )
			->method( 'getQueryLink' )
			->willReturn( $link );

		$queryResult->expects( $this->any() )
			->method( 'getCount' )
			->willReturn( 1 );

		$queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->willReturn( [] );

		$instance = new SpreadsheetPrinter( 'csv' );
		$instance->getResult( $queryResult, [], SMW_OUTPUT_WIKI );
	}

}
