<?php

namespace SRF\Tests\Filtered;

use SMW\Query\PrintRequest;
use SRF\Filtered\Filter\NumberFilter;
use SRF\Filtered\Filtered;

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
}
