<?php

namespace SRF\Tests\Filtered;

use SMW\DataValues\PropertyChainValue;
use SMW\DataValues\PropertyValue;
use SMW\DIProperty;
use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SRF\Filtered\Filter\NumberFilter;
use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;

/**
 * @covers \SRF\Filtered\Filter\NumberFilter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 4.1.0
 */
class NumberFilterTest extends \PHPUnit\Framework\TestCase {

	private function makeFilter( string $typeID ): NumberFilter {
		$results = [];
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getTypeID' )->willReturn( $typeID );
		$queryPrinter = new Filtered( null );
		return new NumberFilter( $results, $printRequest, $queryPrinter );
	}

	public function testIsValidFilterForPropertyType_returnsTrue_forNumericType() {
		$this->assertTrue( $this->makeFilter( '_num' )->isValidFilterForPropertyType() );
	}

	public function testIsValidFilterForPropertyType_returnsTrue_forQuantityType() {
		$this->assertTrue( $this->makeFilter( '_qty' )->isValidFilterForPropertyType() );
	}

	public function testIsValidFilterForPropertyType_returnsTrue_forDateType() {
		$this->assertTrue( $this->makeFilter( '_dat' )->isValidFilterForPropertyType() );
	}

	public function testIsValidFilterForPropertyType_returnsFalse_forTextType() {
		$this->assertFalse( $this->makeFilter( '_txt' )->isValidFilterForPropertyType() );
	}

	public function testIsValidFilterForPropertyType_returnsFalse_forWikiPageType() {
		$this->assertFalse( $this->makeFilter( '_wpg' )->isValidFilterForPropertyType() );
	}

	public function testCaptionOfADateIsNotItsOutputFormat() {
		$config = $this->newConfigFilter( '_dat', '-F[Y]' )->getJsConfig();

		$this->assertArrayNotHasKey( 'caption', $config );
	}

	public function testCaptionOfAQuantityIsItsUnit() {
		$config = $this->newConfigFilter( '_qty', 'kg' )->getJsConfig();

		$this->assertSame( 'kg', $config['caption'] );
	}

	public function testGivenLabelIsTheCaptionOfADateToo() {
		$config = $this->newConfigFilter( '_dat', '-F[Y]', [ 'number filter label' => 'Year' ] )->getJsConfig();

		$this->assertSame( 'Year', $config['caption'] );
	}

	public function testGetJsDataForRow_returnsAllValuesOfAMultiValuedNumberProperty() {
		$filter = $this->makeFilterOn( 'Size', '_num' );

		$data = $filter->getJsDataForRow( $this->newRow( 'Size', [ $this->newNumber( 3 ), $this->newNumber( 9 ) ], new \SMWDINumber( 3 ) ) );

		$this->assertSame( [ 'values' => [ 3, 9 ] ], $data );
	}

	public function testGetJsDataForRow_returnsAllYearsOfAMultiValuedDateProperty() {
		$filter = $this->makeFilterOn( 'Built', '_dat' );

		$data = $filter->getJsDataForRow(
			$this->newRow( 'Built', [ $this->newYear( 2020 ), $this->newYear( 2024 ) ], $this->createStub( \SMWDITime::class ) )
		);

		$this->assertSame( [ 'values' => [ 2020, 2024 ] ], $data );
	}

	public function testGetJsDataForRow_leavesOutDatesWithoutAYear() {
		$filter = $this->makeFilterOn( 'Built', '_dat' );

		// getYear() is false for dates the calendar model has no year number for
		$data = $filter->getJsDataForRow(
			$this->newRow( 'Built', [ $this->newYear( 2020 ), $this->newYear( false ), $this->newYear( 2024 ) ], $this->createStub( \SMWDITime::class ) )
		);

		$this->assertSame( [ 'values' => [ 2020, 2024 ] ], $data );
	}

	public function testGetJsDataForRow_ignoresPrintoutsOfOtherProperties() {
		$filter = $this->makeFilterOn( 'Size', '_num' );

		$this->assertNull(
			$filter->getJsDataForRow( $this->newRow( 'Other', [ $this->newNumber( 3 ) ], new \SMWDINumber( 3 ) ) )
		);
	}

	public function testGetJsDataForRow_supportsPropertyChains() {
		$filter = $this->makeFilterOn( 'Has city.Population', '_num', true );

		$data = $filter->getJsDataForRow(
			$this->newRow( 'Has city.Population', [ $this->newNumber( 3 ), $this->newNumber( 9 ) ], new \SMWDINumber( 3 ), true )
		);

		$this->assertSame( [ 'values' => [ 3, 9 ] ], $data );
	}

	public function testGetJsDataForRow_doesNotMixUpTwoPrintoutsOfTheSameProperty() {
		$filter = $this->makeFilterOn( 'Size', '_num' );

		$printRequest = $this->newPrintRequest( 'Size', '_num' );
		$otherPrintRequest = $this->createStub( PrintRequest::class );
		$otherPrintRequest->method( 'getData' )->willReturn( $printRequest->getData() );
		$otherPrintRequest->method( 'getHash' )->willReturn( 'hash-Size-with-other-label' );

		$field = $this->createStub( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $otherPrintRequest );
		$field->method( 'reset' )->willReturn( new \SMWDINumber( 3 ) );
		$row = $this->createStub( ResultItem::class );
		$row->method( 'getValue' )->willReturn( [ $field ] );

		$this->assertNull( $filter->getJsDataForRow( $row ) );
	}

	private function newConfigFilter( string $typeID, string $outputFormat, array $parameters = [] ): NumberFilter {
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getTypeID' )->willReturn( $typeID );
		$printRequest->method( 'getOutputFormat' )->willReturn( $outputFormat );
		$printRequest->method( 'getParameters' )->willReturn( $parameters );

		$parser = $this->createStub( \Parser::class );
		$parser->method( 'recursiveTagParse' )->willReturnArgument( 0 );
		$printer = new class( null, $parser ) extends Filtered {
			public function __construct( $format, private $stubParser ) {
				parent::__construct( $format );
			}

			public function getParser() {
				return $this->stubParser;
			}
		};
		$results = [];

		return new NumberFilter( $results, $printRequest, $printer );
	}

	private function newPrintRequest( string $propertyName, string $typeID, bool $chain = false ): PrintRequest {
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getTypeID' )->willReturn( $typeID );
		$printRequest->method( 'getHash' )->willReturn( 'hash-' . $propertyName );

		if ( $chain ) {
			// a property chain has no inceptive property, only the last link of the chain
			$printRequest->method( 'getData' )->willReturn( $this->createStub( PropertyChainValue::class ) );
		} else {
			$property = $this->createStub( DIProperty::class );
			$property->method( 'getKey' )->willReturn( $propertyName );

			$data = $this->createStub( PropertyValue::class );
			$data->method( 'getInceptiveProperty' )->willReturn( $property );
			$printRequest->method( 'getData' )->willReturn( $data );
		}

		return $printRequest;
	}

	private function makeFilterOn( string $propertyName, string $typeID, bool $chain = false ): NumberFilter {
		$results = [];
		$printRequest = $this->newPrintRequest( $propertyName, $typeID, $chain );
		$queryPrinter = new Filtered( null );

		return new NumberFilter( $results, $printRequest, $queryPrinter );
	}

	/**
	 * Mirrors SMW: getNextDataValue() and getNextDataItem() share one pointer, and
	 * getNextDataItem() hands out data items, never data values.
	 */
	private function newRow( string $propertyName, array $dataValues, $firstDataItem, bool $chain = false ): ResultItem {
		$field = $this->createStub( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $this->newPrintRequest( $propertyName, '_num', $chain ) );
		$field->method( 'reset' )->willReturn( $firstDataItem );

		$pending = $dataValues;
		$field->method( 'getNextDataValue' )->willReturnCallback( static function () use ( &$pending ) {
			return array_shift( $pending ) ?? false;
		} );
		$field->method( 'getNextDataItem' )->willReturnCallback( static function () use ( &$pending ) {
			$next = array_shift( $pending );

			return $next ? $next->getDataItem() : false;
		} );

		$row = $this->createStub( ResultItem::class );
		$row->method( 'getValue' )->willReturn( [ $field ] );

		return $row;
	}

	private function newNumber( int $number ): \SMWNumberValue {
		$value = $this->createStub( \SMWNumberValue::class );
		$value->method( 'getCanonicalMainUnit' )->willReturn( 'm' );
		$value->method( 'getConvertedUnitValues' )->willReturn( [ 'm' => $number ] );
		$value->method( 'getDataItem' )->willReturn( new \SMWDINumber( $number ) );

		return $value;
	}

	private function newYear( int|false $year ): \SMWTimeValue {
		$value = $this->createStub( \SMWTimeValue::class );
		$value->method( 'getYear' )->willReturn( $year );
		$value->method( 'getDataItem' )->willReturn( $this->createStub( \SMWDITime::class ) );

		return $value;
	}
}
