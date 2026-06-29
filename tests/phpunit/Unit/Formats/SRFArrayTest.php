<?php

namespace SRF\Tests\Unit\Formats;

use PHPUnit\Framework\TestCase;
use SMW\Query\QueryResult;
use SMW\Query\Result\ResultArray;
use SRF\ArrayFormat\ArrayPrinter;

/**
 * Unit tests for ArrayPrinter domain logic (separator composition, gap-hiding,
 * header rendering, and initializeCfgValue fallback).
 *
 * Uses an anonymous subclass to:
 *  - widen all tested protected methods to public (PHP allows this)
 *  - stub infrastructure methods so no MediaWiki parser bootstrap is required
 *
 * @covers \SRF\ArrayFormat\ArrayPrinter
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @license GPL-2.0-or-later
 */
class SRFArrayTest extends TestCase {

	/**
	 * Returns an anonymous ArrayPrinter subclass with:
	 *  - all tested protected methods widened to public
	 *  - separator properties pre-set to known test values
	 *  - infrastructure methods (getCfgSepText, createArray) stubbed
	 *
	 * @param array $overrides Optional property overrides, e.g. ['mHideRecordGaps' => true]
	 */
	private function newInstance( array $overrides = [] ): ArrayPrinter {
		$instance = new class( 'array' ) extends ArrayPrinter {

			/** Stub: return scalar values directly; null for anything else. */
			protected function getCfgSepText( $obj ) {
				return is_string( $obj ) ? $obj : null;
			}

			/** Stub to avoid ArrayExtension global dependency. */
			protected function createArray( $array ) {
				return true;
			}

			/**
			 * Stub to avoid Sanitizer::decodeCharReferences MW dependency.
			 * Returns the DataValue's short wiki text trimmed, without HTML decoding.
			 */
			protected function deliverSingleValue( $value, $link = false ) {
				return trim( $value->getShortWikiText( $link ) );
			}

			/** Expose protected properties for direct test setup. */
			public function set( string $property, $value ): void {
				$this->$property = $value;
			}

			public function get( string $property ) {
				return $this->$property;
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

			public function deliverPropertiesManyValues( $manyValue_items, $isMissingProperty, $isPageTitle, ResultArray $data ) {
				return parent::deliverPropertiesManyValues( $manyValue_items, $isMissingProperty, $isPageTitle, $data );
			}

			public function deliverPageProperties( $perProperty_items ) {
				return parent::deliverPageProperties( $perProperty_items );
			}

			public function deliverQueryResultPages( $perPage_items ) {
				return parent::deliverQueryResultPages( $perPage_items );
			}

			public function applyArrayParameters( array $params ): void {
				parent::applyArrayParameters( $params );
			}

			public function getResultText( \SMW\Query\QueryResult $res, $outputmode ) {
				return parent::getResultText( $res, $outputmode );
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

	/**
	 * Returns a minimal params array for applyArrayParameters() with safe defaults.
	 * Individual keys can be overridden via $overrides.
	 */
	private function defaultParams( array $overrides = [] ): array {
		return array_merge( [
			'sep'       => ', ',
			'propsep'   => '<PROP>',
			'manysep'   => '<MANY>',
			'recordsep' => '<RCRD>',
			'headersep' => ': ',
			'name'      => false,
			'mainlabel' => '',
			'titles'    => 'show',
			'hidegaps'  => 'none',
		], $overrides );
	}

	protected function setUp(): void {
		parent::setUp();
		// Reset the static separator cache so tests are independent of execution order.
		$ref = new \ReflectionProperty( ArrayPrinter::class, 'mDefaultSeps' );
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

	public function testApplyArrayParametersSeparatorsAreAssigned(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [
			'sep'       => '|',
			'propsep'   => '::',
			'manysep'   => ';;',
			'recordsep' => ',,',
			'headersep' => '=',
		] ) );
		$this->assertSame( '|', $instance->get( 'mSep' ) );
		$this->assertSame( '::', $instance->get( 'mPropSep' ) );
		$this->assertSame( ';;', $instance->get( 'mManySep' ) );
		$this->assertSame( ',,', $instance->get( 'mRecordSep' ) );
		$this->assertSame( '=', $instance->get( 'mHeaderSep' ) );
	}

	public function testApplyArrayParametersNameFalseDoesNotSetArrayName(): void {
		$instance = $this->newInstance( [ 'mArrayName' => null ] );
		$instance->applyArrayParameters( $this->defaultParams( [ 'name' => false ] ) );
		$this->assertNull( $instance->get( 'mArrayName' ) );
	}

	public function testApplyArrayParametersNameSetsSetsArrayName(): void {
		$instance = $this->newInstance();
		$instance->set( 'mInline', false );
		$instance->applyArrayParameters( $this->defaultParams( [ 'name' => 'myArr' ] ) );
		$this->assertSame( 'myArr', $instance->get( 'mArrayName' ) );
	}

	public function testApplyArrayParametersMainlabelDashSetsMainLabelHackTrue(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'mainlabel' => '-' ] ) );
		$this->assertTrue( $instance->get( 'mMainLabelHack' ) );
	}

	public function testApplyArrayParametersMainlabelNormalLeavesMainLabelHackFalse(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'mainlabel' => 'My label' ] ) );
		$this->assertFalse( $instance->get( 'mMainLabelHack' ) );
	}

	public function testApplyArrayParametersTitlesHideSetsShowPageTitlesFalse(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'titles' => 'hide' ] ) );
		$this->assertFalse( $instance->get( 'mShowPageTitles' ) );
	}

	public function testApplyArrayParametersTitlesShowSetsShowPageTitlesTrue(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'titles' => 'show' ] ) );
		$this->assertTrue( $instance->get( 'mShowPageTitles' ) );
	}

	public function testApplyArrayParametersHidegapsNoneSetsAllFalse(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'hidegaps' => 'none' ] ) );
		$this->assertFalse( $instance->get( 'mHideRecordGaps' ) );
		$this->assertFalse( $instance->get( 'mHidePropertyGaps' ) );
	}

	public function testApplyArrayParametersHidegapsAllSetsBothTrue(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'hidegaps' => 'all' ] ) );
		$this->assertTrue( $instance->get( 'mHideRecordGaps' ) );
		$this->assertTrue( $instance->get( 'mHidePropertyGaps' ) );
	}

	public function testApplyArrayParametersHidegapsPropertySetsPropertyGapTrue(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'hidegaps' => 'property' ] ) );
		$this->assertFalse( $instance->get( 'mHideRecordGaps' ) );
		$this->assertTrue( $instance->get( 'mHidePropertyGaps' ) );
	}

	public function testApplyArrayParametersHidegapsRecordSetsRecordGapTrue(): void {
		$instance = $this->newInstance();
		$instance->applyArrayParameters( $this->defaultParams( [ 'hidegaps' => 'record' ] ) );
		$this->assertTrue( $instance->get( 'mHideRecordGaps' ) );
		$this->assertFalse( $instance->get( 'mHidePropertyGaps' ) );
	}

	public function testDeliverPropertiesManyValuesReturnsNullForEmptyItems(): void {
		$field = $this->createMock( ResultArray::class );
		$this->assertNull( $this->newInstance()->deliverPropertiesManyValues( [], false, false, $field ) );
	}

	public function testDeliverPropertiesManyValuesJoinsWithManySep(): void {
		$field = $this->createMock( ResultArray::class );
		$instance = $this->newInstance( [ 'mShowHeaders' => SMW_HEADERS_HIDE ] );
		$result = $instance->deliverPropertiesManyValues( [ 'val1', 'val2' ], false, false, $field );
		$this->assertSame( 'val1<MANY>val2', $result );
	}

	public function testDeliverPropertiesManyValuesWithHeadersHideSkipsHeader(): void {
		$field = $this->createMock( ResultArray::class );
		$field->expects( $this->never() )->method( 'getPrintRequest' );
		$instance = $this->newInstance( [ 'mShowHeaders' => SMW_HEADERS_HIDE ] );
		$result = $instance->deliverPropertiesManyValues( [ 'val' ], false, false, $field );
		$this->assertSame( 'val', $result );
	}

	public function testDeliverPropertiesManyValuesWithHeadersPlainPrependsLabelNoLinker(): void {
		$printRequest = $this->getMockBuilder( \SMW\Query\PrintRequest::class )
			->disableOriginalConstructor()
			->getMock();
		$printRequest->method( 'getText' )
			->with( SMW_OUTPUT_WIKI, null )
			->willReturn( 'PropName' );

		$field = $this->createMock( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printRequest );

		$instance = $this->newInstance( [ 'mShowHeaders' => SMW_HEADERS_PLAIN ] );
		$result = $instance->deliverPropertiesManyValues( [ 'val' ], false, false, $field );
		$this->assertSame( 'PropName: val', $result );
	}

	public function testDeliverPropertiesManyValuesWithHeadersShowPrependsLabelWithLinker(): void {
		$linker = $this->getMockBuilder( \MediaWiki\Linker\Linker::class )
			->disableOriginalConstructor()
			->getMock();

		$printRequest = $this->getMockBuilder( \SMW\Query\PrintRequest::class )
			->disableOriginalConstructor()
			->getMock();
		$printRequest->method( 'getText' )
			->with( SMW_OUTPUT_WIKI, $linker )
			->willReturn( 'PropName' );

		$field = $this->createMock( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printRequest );

		$instance = $this->newInstance( [ 'mShowHeaders' => SMW_HEADERS_SHOW, 'mLinker' => $linker ] );
		$result = $instance->deliverPropertiesManyValues( [ 'val' ], false, false, $field );
		$this->assertSame( 'PropName: val', $result );
	}

	public function testDeliverPropertiesManyValuesIsPageTitleSkipsHeaderEvenIfHeadersEnabled(): void {
		$field = $this->createMock( ResultArray::class );
		$field->expects( $this->never() )->method( 'getPrintRequest' );
		$instance = $this->newInstance( [ 'mShowHeaders' => SMW_HEADERS_PLAIN ] );
		$result = $instance->deliverPropertiesManyValues( [ 'PageTitle' ], false, true, $field );
		$this->assertSame( 'PageTitle', $result );
	}

	public function testGetResultTextEmptyResultReturnsEmptyString(): void {
		$res = $this->createMock( QueryResult::class );
		$res->method( 'getNext' )->willReturn( false );

		$result = $this->newInstance()->getResultText( $res, SMW_OUTPUT_WIKI );
		$this->assertSame( '', $result );
	}

	/**
	 * Regression test for B6: with titles hidden, properties on the same row
	 * must still appear in output. The old `continue 2` skipped the entire
	 * remaining row; the fixed `continue` skips only the title field.
	 */
	public function testGetResultTextWithTitlesHiddenStillIncludesOtherProperties(): void {
		$titleDataValue = $this->getMockBuilder( \SMWDataValue::class )
			->disableOriginalConstructor()
			->getMock();
		$titleDataValue->method( 'getShortWikiText' )->willReturn( 'PageTitle' );

		$titleField = $this->createMock( ResultArray::class );
		$titleField->method( 'getContent' )->willReturn( [ $titleDataValue ] );
		$titleField->method( 'getNextDataValue' )
			->willReturnOnConsecutiveCalls( $titleDataValue, false );

		$propDataValue = $this->getMockBuilder( \SMWDataValue::class )
			->disableOriginalConstructor()
			->getMock();
		$propDataValue->method( 'getShortWikiText' )->willReturn( 'PropValue' );

		$propField = $this->createMock( ResultArray::class );
		$propField->method( 'getContent' )->willReturn( [ $propDataValue ] );
		$propField->method( 'getNextDataValue' )
			->willReturnOnConsecutiveCalls( $propDataValue, false );

		$res = $this->createMock( QueryResult::class );
		$res->method( 'getNext' )
			->willReturnOnConsecutiveCalls( [ $titleField, $propField ], false );

		$instance = $this->newInstance( [
			'mShowPageTitles' => false,
			'mShowHeaders'    => SMW_HEADERS_HIDE,
			'mMainLabelHack'  => false,
		] );

		$result = $instance->getResultText( $res, SMW_OUTPUT_WIKI );
		$this->assertStringContainsString( 'PropValue', $result );
	}

}
