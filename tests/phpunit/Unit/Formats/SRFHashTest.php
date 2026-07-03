<?php

namespace SRF\Tests\Unit\Formats;

use PHPUnit\Framework\TestCase;
use SRF\ArrayFormat\ArrayPrinter;
use SRF\ArrayFormat\HashPrinter;

/**
 * Unit tests for Hash domain logic.
 *
 * Uses an anonymous subclass to:
 *  - widen all tested protected methods to public
 *  - stub infrastructure methods so no MediaWiki parser or extension bootstrap is required
 *
 * @covers \SRF\ArrayFormat\HashPrinter
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @license GPL-2.0-or-later
 */
class SRFHashTest extends TestCase {

	/**
	 * Returns an anonymous Hash subclass with infrastructure stubbed out.
	 *
	 * @param array $overrides Optional property overrides, e.g. ['mPropSep' => '|']
	 */
	private function newInstance( array $overrides = [] ): HashPrinter {
		$instance = new class( 'hash' ) extends HashPrinter {

			/** Stub: return scalars directly; null for anything else. */
			protected function getCfgSepText( $obj ) {
				return is_string( $obj ) ? $obj : null;
			}

			/** Stub to avoid HashTables/ArrayExtension global dependency. */
			protected function createArray( $hash ) {
				return true;
			}

			/**
			 * Stub to avoid Sanitizer::decodeCharReferences MW dependency.
			 * Returns the DataValue's short wiki text trimmed.
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

			public function deliverPageTitle( $value, $link = false ) {
				return parent::deliverPageTitle( $value, $link );
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

			public function createArrayPublic( $hash ) {
				return parent::createArray( $hash );
			}
		};

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
		$ref = new \ReflectionProperty( ArrayPrinter::class, 'mDefaultSeps' );
		$ref->setAccessible( true );
		$ref->setValue( null, [] );
	}

	/**
	 * Returns a minimal params array for applyArrayParameters() with safe defaults.
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

	public function testDeliverPageTitleStoresLastPageTitleAndReturnsNull(): void {
		$dataValue = $this->getMockBuilder( \SMWDataValue::class )
			->disableOriginalConstructor()
			->getMock();
		$dataValue->method( 'getShortWikiText' )->willReturn( 'My Page' );

		$instance = $this->newInstance();
		$result = $instance->deliverPageTitle( $dataValue );

		$this->assertNull( $result );
		$this->assertSame( 'My Page', $instance->get( 'mLastPageTitle' ) );
	}

	public function testDeliverPagePropertiesReturnsNullForEmptyItems(): void {
		$this->assertNull( $this->newInstance()->deliverPageProperties( [] ) );
	}

	public function testDeliverPagePropertiesReturnsArrayWithTitleAndJoinedProps(): void {
		$instance = $this->newInstance();
		$instance->set( 'mLastPageTitle', 'MyPage' );

		$result = $instance->deliverPageProperties( [ 'Prop1: val1', 'Prop2: val2' ] );

		$this->assertIsArray( $result );
		$this->assertSame( 'MyPage', $result[0] );
		$this->assertSame( 'Prop1: val1<PROP>Prop2: val2', $result[1] );
	}

	public function testDeliverQueryResultPagesBuildHashAndJoinsValuesWithSep(): void {
		$instance = $this->newInstance( [ 'mArrayName' => null, 'mSep' => '|' ] );

		// SRFHash::deliverQueryResultPages builds ['TitleA' => 'val1', 'TitleB' => 'val2']
		// then passes it to SRFArray::deliverQueryResultPages which calls implode(mSep, hash).
		// implode on an associative array uses values only.
		$result = $instance->deliverQueryResultPages( [
			[ 'TitleA', 'val1' ],
			[ 'TitleB', 'val2' ],
		] );

		$this->assertSame( 'val1|val2', $result );
	}

	public function testDeliverQueryResultPagesWithArrayNameReturnsEmptyString(): void {
		$instance = $this->newInstance( [ 'mArrayName' => 'myHash' ] );

		$result = $instance->deliverQueryResultPages( [
			[ 'TitleA', 'val1' ],
		] );

		$this->assertSame( '', $result );
	}

	public function testApplyArrayParametersAlwaysSetsShowPageTitlesTrue(): void {
		$instance = $this->newInstance();
		// Even with titles=hide, SRFHash forces mShowPageTitles = true
		// because the page title is always used as the hash key.
		$instance->applyArrayParameters( $this->defaultParams( [ 'titles' => 'hide' ] ) );
		$this->assertTrue( $instance->get( 'mShowPageTitles' ) );
	}

	public function testCreateArrayReturnsFalseWhenNoExtensionInstalled(): void {
		// ExtHashTables is not defined in the test environment, and $wgHashTables is unset.
		unset( $GLOBALS['wgHashTables'] );
		$instance = $this->newInstance( [ 'mArrayName' => 'test' ] );
		$result = $instance->createArrayPublic( [] );
		$this->assertFalse( $result );
	}

}
