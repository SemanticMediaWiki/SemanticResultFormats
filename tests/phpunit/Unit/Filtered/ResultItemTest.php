<?php

namespace SRF\Tests\Filtered;

use Collation;
use MediaWiki\MediaWikiServices;
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
class ResultItemTest extends \PHPUnit\Framework\TestCase {

	public function testArray_representation_is_JSON_serializable_for_UCA_collation_Issue568() {
		// TODO When SRF will only support MW 1.37+, remove this backward compatibility switch
		if( method_exists( '\MediaWiki\MediaWikiServices', 'getCollationFactory' ) ) {
			$collation = MediaWikiServices::getInstance()->getCollationFactory()->makeCollation( "uca-de-u-kn" );
		} else {
			$collation = Collation::factory( "uca-de-u-kn" );
		}
		// arrange
		$sortKey = ( new Collator( $collation ) )->getSortKey( 'A' );

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
