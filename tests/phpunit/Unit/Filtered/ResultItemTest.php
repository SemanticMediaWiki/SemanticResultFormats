<?php

namespace SRF\Tests\Filtered;

use Collation;
use SMW\MediaWiki\Collator;
use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SMWDataValue;
use SMWDIWikiPage;
use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;

/**
 * @covers \SRF\Filtered\ResultItem
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 4.0.0
 *
 * @author gesinn-it-wam
 */
class ResultItemTest extends \PHPUnit_Framework_TestCase {

	public function testArray_representation_is_JSON_serializable_for_UCA_collation_Issue568() {
		// arrange
		$sortKey = ( new Collator( Collation::factory( "uca-de-u-kn" ) ) )->getSortKey( 'A' );

		$resultArray = $this->createStub( ResultArray::class );

		$dataItem = $this->createStub( SMWDIWikiPage::class );
		$dataItem->method( 'getSortKey' )->willReturn( $sortKey );
		$dataValue = $this->createStub( SMWDataValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $dataItem );
		$dataValue->method( 'getShortWikiText' )->willReturn( null );
		$resultArray->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( $dataValue );

		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getHash' )->willReturn( null );
		$resultArray->method( 'getPrintRequest' )->willReturn( $printRequest );

		$queryPrinter = new Filtered( null );
		$instance = new ResultItem( [ $resultArray ], $queryPrinter );

		// act
		$representation = $instance->getArrayRepresentation();

		// assert
		$this->assertNotFalse( json_encode( $representation ) );
	}
}
