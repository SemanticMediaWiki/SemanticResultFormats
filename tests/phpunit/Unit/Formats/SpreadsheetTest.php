<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Test\QueryPrinterRegistryTestCase;
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

	public function testLink() {

		$link = $this->getMockBuilder( '\SMWInfolink' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();

		$queryResult->expects( $this->once() )
			->method( 'getQueryLink' )
			->will( $this->returnValue( $link ) );

		$queryResult->expects( $this->any() )
			->method( 'getCount' )
			->will( $this->returnValue( 1 ) );

		$queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->will( $this->returnValue( [] ) );

		$instance = new SpreadsheetPrinter( 'csv' );
		$instance->getResult( $queryResult, [], SMW_OUTPUT_WIKI );
	}

}
