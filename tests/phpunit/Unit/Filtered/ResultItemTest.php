<?php

namespace SRF\Tests\Filtered;

use Collation;
use MediaWiki\MediaWikiServices;
use SMW\DIWikiPage;
use SMW\MediaWiki\Collator;
use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SMWDataValue;
use SMWDIGeoCoord;
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
		if ( method_exists( '\MediaWiki\MediaWikiServices', 'getCollationFactory' ) ) {
			$collation = MediaWikiServices::getInstance()->getCollationFactory()->makeCollation( "uca-de-u-kn" );
		} else {
			$collation = Collation::factory( "uca-de-u-kn" );
		}
		// arrange
		$sortKey = ( new Collator( $collation ) )->getSortKey( 'A' );

		$resultArray = $this->createStub( ResultArray::class );

		$dataItem = $this->createStub( DIWikiPage::class );
		$dataItem->method( 'getSortKey' )->willReturn( $sortKey );
		$dataValue = $this->createStub( SMWDataValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $dataItem );
		$dataValue->method( 'getShortWikiText' )->willReturn( null );
		$resultArray->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( $dataValue );

		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getHash' )->willReturn( '' );
		$resultArray->method( 'getPrintRequest' )->willReturn( $printRequest );

		$queryPrinter = new Filtered( null );
		$instance = new ResultItem( [ $resultArray ], $queryPrinter );

		// act
		$representation = $instance->getArrayRepresentation();

		// assert
		$this->assertNotFalse( json_encode( $representation ) );
	}

	public function testGetData_returnsNullForUnsetKey() {
		$queryPrinter = new Filtered( null );
		$instance = new ResultItem( [], $queryPrinter );

		$this->assertNull( $instance->getData( 'nonexistent-id' ) );
	}

	public function testGetData_returnsSetValue() {
		$queryPrinter = new Filtered( null );
		$instance = new ResultItem( [], $queryPrinter );
		$instance->setData( 'my-id', [ 'foo' => 'bar' ] );

		$this->assertSame( [ 'foo' => 'bar' ], $instance->getData( 'my-id' ) );
	}

	public function testGetData_returnsNullAfterUnset() {
		$queryPrinter = new Filtered( null );
		$instance = new ResultItem( [], $queryPrinter );
		$instance->setData( 'my-id', 'value' );
		$instance->unsetData( 'my-id' );

		$this->assertNull( $instance->getData( 'my-id' ) );
	}

	public function testWikiPageValueKeepsDistinctFormattedAndSortValues() {
		$printer = new Filtered( null );
		$field = $this->newField( [
			$this->newWikiPageValue( 'Foo', '<a href="/Foo">Foo</a>', 'Foo' ),
		] );

		$representation = ( new ResultItem( [ $field ], $printer ) )->getArrayRepresentation();

		$this->assertSame(
			[
				'v' => [ 'Foo' ],
				'f' => [ '<a href="/Foo">Foo</a>' ],
				's' => [ '466f6f' ],
			],
			$representation['p'][0]
		);
	}

	public function testPlainTextValueOmitsFormattedAndSortWhenEqualToValue() {
		$printer = new Filtered( null );
		$field = $this->newField( [ $this->newPlainValue( 'abc', 'abc' ) ] );

		$representation = ( new ResultItem( [ $field ], $printer ) )->getArrayRepresentation();

		$this->assertSame( [ 'v' => [ 'abc' ] ], $representation['p'][0] );
	}

	public function testGeoCoordinateIsSerializedAsLatLngPairWithSortKey() {
		$printer = new Filtered( null );
		$field = $this->newField( [
			$this->newGeoValue( 1.5, 2.5, '1.5,2.5', 'GEOHTML' ),
		] );

		$representation = ( new ResultItem( [ $field ], $printer ) )->getArrayRepresentation();

		$this->assertSame(
			[
				'v' => [ [ 'lat' => 1.5, 'lng' => 2.5 ] ],
				'f' => [ 'GEOHTML' ],
				's' => [ '1.5,2.5' ],
			],
			$representation['p'][0]
		);
	}

	public function testEmptyPrintoutIsSerializedAsNull() {
		$printer = new Filtered( null );
		$field = $this->newField( [] );

		$representation = ( new ResultItem( [ $field ], $printer ) )->getArrayRepresentation();

		$this->assertNull( $representation['p'][0] );
	}

	public function testPrintoutsAreAPositionalArrayInFieldOrder() {
		$printer = new Filtered( null );
		$fields = [
			$this->newField( [ $this->newWikiPageValue( 'Foo', 'Foo', 'Foo' ) ] ),
			$this->newField( [ $this->newGeoValue( 1.5, 2.5, '1.5,2.5', 'GEOHTML' ) ] ),
			$this->newField( [] ),
		];

		$representation = ( new ResultItem( $fields, $printer ) )->getArrayRepresentation();

		$this->assertSame( [ 0, 1, 2 ], array_keys( $representation['p'] ) );
		$this->assertNull( $representation['p'][2] );
	}

	public function testPerRowDataIsIncludedUnderDKey() {
		$printer = new Filtered( null );
		$item = new ResultItem( [], $printer );
		$item->setData( 'view-1', [ 'positions' => [ [ 'lat' => 1.5, 'lng' => 2.5 ] ] ] );

		$representation = $item->getArrayRepresentation();

		$this->assertSame(
			[ 'view-1' => [ 'positions' => [ [ 'lat' => 1.5, 'lng' => 2.5 ] ] ] ],
			$representation['d']
		);
	}

	public function testDKeyIsOmittedWhenThereIsNoPerRowData() {
		$printer = new Filtered( null );
		$field = $this->newField( [ $this->newPlainValue( 'abc', 'abc' ) ] );

		$representation = ( new ResultItem( [ $field ], $printer ) )->getArrayRepresentation();

		$this->assertArrayNotHasKey( 'd', $representation );
	}

	/**
	 * @param SMWDataValue[] $dataValues
	 */
	private function newField( array $dataValues ): ResultArray {
		$field = $this->createStub( ResultArray::class );
		$field->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( ...[ ...$dataValues, false ] );

		return $field;
	}

	private function newPlainValue( string $text, string $html ): SMWDataValue {
		$dataValue = $this->createStub( SMWDataValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $this->createStub( \SMWDIBlob::class ) );
		$dataValue->method( 'getShortWikiText' )->willReturn( $text );
		$dataValue->method( 'getShortHTMLText' )->willReturn( $html );

		return $dataValue;
	}

	private function newWikiPageValue( string $text, string $html, string $sortKey ): SMWDataValue {
		$dataItem = $this->createStub( DIWikiPage::class );
		$dataItem->method( 'getSortKey' )->willReturn( $sortKey );

		$dataValue = $this->createStub( SMWDataValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $dataItem );
		$dataValue->method( 'getShortWikiText' )->willReturn( $text );
		$dataValue->method( 'getShortHTMLText' )->willReturn( $html );

		return $dataValue;
	}

	private function newGeoValue( float $lat, float $lng, string $sortKey, string $html ): SMWDataValue {
		$dataItem = $this->createStub( SMWDIGeoCoord::class );
		$dataItem->method( 'getLatitude' )->willReturn( $lat );
		$dataItem->method( 'getLongitude' )->willReturn( $lng );
		$dataItem->method( 'getSortKey' )->willReturn( $sortKey );

		$dataValue = $this->createStub( SMWDataValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $dataItem );
		$dataValue->method( 'getShortHTMLText' )->willReturn( $html );

		return $dataValue;
	}
}
