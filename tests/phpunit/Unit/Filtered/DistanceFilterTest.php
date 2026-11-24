<?php

namespace SRF\Tests\Filtered;

use SMW\DataValues\PropertyChainValue;
use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SRF\Filtered\Filter\DistanceFilter;
use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;

/**
 * @covers \SRF\Filtered\Filter\DistanceFilter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 */
class DistanceFilterTest extends \PHPUnit\Framework\TestCase {

	public function testPositionsOfATextPropertyAreParsedOnTheServer() {
		$printRequest = $this->newPrintRequest( 'hash-Pos' );

		$data = $this->newFilter( $printRequest )->getJsDataForRow(
			$this->newRow( $printRequest, [ new \SMWDIBlob( '52.5, 13.4' ), new \SMWDIBlob( '48.1, 11.6' ) ] )
		);

		$this->assertSame(
			[ 'positions' => [ [ 'lat' => 52.5, 'lng' => 13.4 ], [ 'lat' => 48.1, 'lng' => 11.6 ] ] ],
			$data
		);
	}

	public function testGeographicCoordinatesAreLeftToTheSharedPrintoutValues() {
		$printRequest = $this->newPrintRequest( 'hash-Pos' );

		$this->assertNull(
			$this->newFilter( $printRequest )->getJsDataForRow(
				$this->newRow( $printRequest, [ $this->createStub( \SMWDIGeoCoord::class ) ] )
			)
		);
	}

	public function testPropertyChainsAreSupported() {
		$printRequest = $this->newPrintRequest( 'hash-City.Pos', true );

		$data = $this->newFilter( $printRequest )->getJsDataForRow(
			$this->newRow( $printRequest, [ new \SMWDIBlob( '52.5, 13.4' ) ] )
		);

		$this->assertSame( [ 'positions' => [ [ 'lat' => 52.5, 'lng' => 13.4 ] ] ], $data );
	}

	public function testAnInvalidCoordinateIsReportedAndTheNextValueIsStillRead() {
		$printRequest = $this->newPrintRequest( 'hash-Pos' );
		$printer = $this->newRecordingPrinter();

		$data = $this->newFilter( $printRequest, $printer )->getJsDataForRow(
			$this->newRow( $printRequest, [ new \SMWDIBlob( 'not a coordinate' ), new \SMWDIBlob( '52.5, 13.4' ) ] )
		);

		$this->assertCount( 1, $printer->errors );
		$this->assertSame( [ 'positions' => [ [ 'lat' => 52.5, 'lng' => 13.4 ] ] ], $data );
	}

	public function testPrintoutsOfOtherFiltersAreIgnored() {
		$data = $this->newFilter( $this->newPrintRequest( 'hash-Pos' ) )->getJsDataForRow(
			$this->newRow( $this->newPrintRequest( 'hash-Other' ), [ new \SMWDIBlob( '52.5, 13.4' ) ] )
		);

		$this->assertNull( $data );
	}

	private function newPrintRequest( string $hash, bool $chain = false ): PrintRequest {
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getHash' )->willReturn( $hash );

		if ( $chain ) {
			$printRequest->method( 'getData' )->willReturn( $this->createStub( PropertyChainValue::class ) );
		}

		return $printRequest;
	}

	private function newFilter( PrintRequest $printRequest, ?Filtered $printer = null ): DistanceFilter {
		$results = [];
		$printer ??= $this->newRecordingPrinter();

		return new DistanceFilter( $results, $printRequest, $printer );
	}

	private function newRecordingPrinter(): Filtered {
		return new class( null ) extends Filtered {
			public array $errors = [];

			public function addError( $errorMessage ): void {
				$this->errors[] = $errorMessage;
				if ( count( $this->errors ) > 3 ) {
					throw new \LogicException( 'the same error is reported again and again' );
				}
			}
		};
	}

	/**
	 * getNextDataItem() hands out one data item per call and false at the end.
	 */
	private function newRow( PrintRequest $printRequest, array $dataItems ): ResultItem {
		$field = $this->createStub( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printRequest );
		$field->method( 'getNextDataItem' )->willReturnOnConsecutiveCalls( ...[ ...$dataItems, false ] );

		$row = $this->createStub( ResultItem::class );
		$row->method( 'getValue' )->willReturn( [ $field ] );

		return $row;
	}
}
