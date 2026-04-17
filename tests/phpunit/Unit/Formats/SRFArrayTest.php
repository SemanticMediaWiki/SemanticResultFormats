<?php

namespace SRF\Tests\Unit\Formats;

use PHPUnit\Framework\TestCase;
use SMW\Query\Result\ResultArray;
use SRFArray;

/**
 * Unit tests for SRFArray domain logic (separator composition, gap-hiding,
 * header rendering, and initializeCfgValue fallback).
 *
 * Uses an anonymous subclass to:
 *  - widen all tested protected methods to public (PHP allows this)
 *  - stub infrastructure methods so no MediaWiki parser bootstrap is required
 *
 * @covers SRFArray
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @license GPL-2.0-or-later
 */
class SRFArrayTest extends TestCase {

	/**
	 * Returns an anonymous SRFArray subclass with:
	 *  - all tested protected methods widened to public
	 *  - separator properties pre-set to known test values
	 *  - infrastructure methods (getCfgSepText, createArray) stubbed
	 *
	 * @param array $overrides Optional property overrides, e.g. ['mHideRecordGaps' => true]
	 */
	private function newInstance( array $overrides = [] ): SRFArray {
		$instance = new class( 'array' ) extends SRFArray {

			/** Stub: return scalar values directly; null for anything else. */
			protected function getCfgSepText( $obj ) {
				return is_string( $obj ) ? $obj : null;
			}

			/** Stub to avoid ArrayExtension global dependency. */
			protected function createArray( $array ) {
				return true;
			}

			/** Expose protected properties for direct test setup. */
			public function set( string $property, $value ): void {
				$this->$property = $value;
			}

			public function fillDeliveryArray( $array = [], $value = null ) {
				return parent::fillDeliveryArray( $array, $value );
			}

			public function deliverRecordField( $value, $link = false ) {
				return parent::deliverRecordField( $value, $link );
			}

			public function deliverMissingProperty( ResultArray $field ) {
				return parent::deliverMissingProperty( $field );
			}

			public function deliverSingleManyValuesData( $value_items, $containsRecord, $isPageTitle ) {
				return parent::deliverSingleManyValuesData( $value_items, $containsRecord, $isPageTitle );
			}

			public function deliverPageProperties( $perProperty_items ) {
				return parent::deliverPageProperties( $perProperty_items );
			}

			public function deliverQueryResultPages( $perPage_items ) {
				return parent::deliverQueryResultPages( $perPage_items );
			}

			public function initializeCfgValue( $dfltVal, $dfltCacheKey ) {
				return parent::initializeCfgValue( $dfltVal, $dfltCacheKey );
			}
		};

	// Default separators used by most tests
		$instance->set( 'mSep', ', ' );
		$instance->set( 'mPropSep', '<PROP>' );
		$instance->set( 'mManySep', '<MANY>' );
		$instance->set( 'mRecordSep', '<RCRD>' );
		$instance->set( 'mHeaderSep', ': ' );

		foreach ( $overrides as $prop => $value ) {
			$instance->set( $prop, $value );
		}

		return $instance;
	}

	protected function setUp(): void {
		parent::setUp();
		// Reset the static separator cache so tests are independent of execution order.
		$ref = new \ReflectionProperty( SRFArray::class, 'mDefaultSeps' );
		$ref->setAccessible( true );
		$ref->setValue( null, [] );
	}

	public function testFillDeliveryArrayAddsNonNullValue(): void {
		$result = $this->newInstance()->fillDeliveryArray( [], 'hello' );
		$this->assertSame( [ 'hello' ], $result );
	}

	public function testFillDeliveryArrayIgnoresNullValue(): void {
		$result = $this->newInstance()->fillDeliveryArray( [ 'a' ], null );
		$this->assertSame( [ 'a' ], $result );
	}

	public function testFillDeliveryArrayStartsEmptyByDefault(): void {
		$result = $this->newInstance()->fillDeliveryArray();
		$this->assertSame( [], $result );
	}

	public function testDeliverRecordFieldReturnsEmptyStringForNullWhenHideRecordGapsFalse(): void {
		$this->assertSame( '', $this->newInstance( [ 'mHideRecordGaps' => false ] )->deliverRecordField( null ) );
	}

	public function testDeliverRecordFieldReturnsNullForNullWhenHideRecordGapsTrue(): void {
		$this->assertNull( $this->newInstance( [ 'mHideRecordGaps' => true ] )->deliverRecordField( null ) );
	}

	public function testDeliverMissingPropertyReturnsEmptyStringWhenHidePropertyGapsFalse(): void {
		$field = $this->createMock( ResultArray::class );
		$this->assertSame( '', $this->newInstance( [ 'mHidePropertyGaps' => false ] )->deliverMissingProperty( $field ) );
	}

	public function testDeliverMissingPropertyReturnsNullWhenHidePropertyGapsTrue(): void {
		$field = $this->createMock( ResultArray::class );
		$this->assertNull( $this->newInstance( [ 'mHidePropertyGaps' => true ] )->deliverMissingProperty( $field ) );
	}

	public function testDeliverSingleManyValuesDataReturnsNullForEmptyInput(): void {
		$this->assertNull( $this->newInstance()->deliverSingleManyValuesData( [], false, false ) );
	}

	public function testDeliverSingleManyValuesDataJoinsWithRecordSep(): void {
		$result = $this->newInstance()->deliverSingleManyValuesData( [ 'a', 'b', 'c' ], false, false );
		$this->assertSame( 'a<RCRD>b<RCRD>c', $result );
	}

	public function testDeliverSingleManyValuesDataSingleValue(): void {
		$result = $this->newInstance()->deliverSingleManyValuesData( [ 'only' ], false, false );
		$this->assertSame( 'only', $result );
	}

	public function testDeliverPagePropertiesReturnsNullForEmptyInput(): void {
		$this->assertNull( $this->newInstance()->deliverPageProperties( [] ) );
	}

	public function testDeliverPagePropertiesJoinsWithPropSep(): void {
		$result = $this->newInstance()->deliverPageProperties( [ 'Prop1: val1', 'Prop2: val2' ] );
		$this->assertSame( 'Prop1: val1<PROP>Prop2: val2', $result );
	}

	public function testDeliverQueryResultPagesJoinsWithSepWhenNoArrayName(): void {
		$result = $this->newInstance( [ 'mArrayName' => null ] )->deliverQueryResultPages( [ 'Page1', 'Page2', 'Page3' ] );
		$this->assertSame( 'Page1, Page2, Page3', $result );
	}

	public function testDeliverQueryResultPagesReturnsEmptyStringWhenArrayNameSet(): void {
		$result = $this->newInstance( [ 'mArrayName' => 'myArray' ] )->deliverQueryResultPages( [ 'Page1', 'Page2' ] );
		$this->assertSame( '', $result );
	}

	public function testInitializeCfgValueReturnsCfgTextWhenAvailable(): void {
		$result = $this->newInstance()->initializeCfgValue( ', ', 'sep' );
		$this->assertSame( ', ', $result );
	}

	/**
	 * When getCfgSepText returns null (simulating "no active parser" / runJobs),
	 * the method must fall back to the textual fallback global and must NOT
	 * trigger an "array offset on null" notice — regression for #916.
	 */
	public function testInitializeCfgValueFallsBackToGlobalWhenCfgTextIsNull(): void {
		$GLOBALS['wgSrfgArraySepTextualFallbacks'] = [ 'propsep' => '<PROP>' ];
		// Passing an array (non-string) causes the stub to return null.
		$result = $this->newInstance()->initializeCfgValue( [ 'SomePage' ], 'propsep' );
		$this->assertSame( '<PROP>', $result );
	}

	/**
	 * When the fallback global is also missing the key, the result must be an
	 * empty string (not null, and no notice).
	 */
	public function testInitializeCfgValueReturnsEmptyStringWhenFallbackKeyMissing(): void {
		$GLOBALS['wgSrfgArraySepTextualFallbacks'] = [];
		$result = $this->newInstance()->initializeCfgValue( [ 'SomePage' ], 'headersep' );
		$this->assertSame( '', $result );
	}

	/**
	 * The result is cached: a second call with the same key must return the
	 * cached value without invoking getCfgSepText again.
	 */
	public function testInitializeCfgValueCachesResult(): void {
		$instance = $this->newInstance();
		$first = $instance->initializeCfgValue( 'cached', 'sep' );
		// Change global to verify the cache is not bypassed on the second call.
		$GLOBALS['wgSrfgArraySepTextualFallbacks'] = [ 'sep' => 'SHOULD_NOT_BE_USED' ];
		$second = $instance->initializeCfgValue( 'different', 'sep' );
		$this->assertSame( $first, $second );
	}

}
