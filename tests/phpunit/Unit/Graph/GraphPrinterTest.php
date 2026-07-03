<?php

declare( strict_types=1 );

namespace SRF\Tests\Unit\Graph;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SMWDataValue;
use SMWWikiPageValue;
use SRF\Graph\GraphPrinter;

/**
 * @covers \SRF\Graph\GraphPrinter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 */
class GraphPrinterTest extends TestCase {

	private const BASE_PARAMS = [
		'graphname'        => 'Test',
		'graphsize'        => '',
		'graphfontsize'    => 10,
		'nodeshape'        => false,
		'nodelabel'        => '',
		'arrowdirection'   => 'LR',
		'arrowhead'        => 'normal',
		'wordwraplimit'    => 25,
		'relation'         => 'child',
		'graphlink'        => false,
		'graphlabel'       => false,
		'graphcolor'       => false,
		'graphlegend'      => false,
		'graphfields'      => false,
		'graphfieldspages' => false,
	];

	private function makePrinter(): GraphPrinter {
		$printer = new GraphPrinter( 'graph' );

		$ref = new ReflectionMethod( GraphPrinter::class, 'handleParameters' );
		$ref->setAccessible( true );
		$ref->invoke( $printer, self::BASE_PARAMS, SMW_OUTPUT_HTML );

		return $printer;
	}

	private function makePrintRequest( string $typeId ): PrintRequest {
		$request = $this->getMockBuilder( PrintRequest::class )
			->disableOriginalConstructor()
			->getMock();

		$request->method( 'getTypeID' )->willReturn( $typeId );
		$request->method( 'getCanonicalLabel' )->willReturn( 'TestProp' );
		$request->method( 'getLabel' )->willReturn( 'TestProp' );
		$request->method( 'isMode' )->willReturn( false );

		return $request;
	}

	private function makeResultArray( PrintRequest $request, array $dataValues ): ResultArray {
		$resultArray = $this->getMockBuilder( ResultArray::class )
			->disableOriginalConstructor()
			->getMock();

		$resultArray->method( 'getPrintRequest' )->willReturn( $request );

		$callCount = 0;
		$resultArray->method( 'getNextDataValue' )
			->willReturnCallback( static function () use ( $dataValues, &$callCount ) {
				return $dataValues[$callCount++] ?? false;
			} );

		return $resultArray;
	}

	/**
	 * Regression test for https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/988
	 *
	 * Non-page-type data values (e.g. _dat for Modification date) must not have
	 * getDisplayTitle() called on them — SMWTimeValue does not have that method.
	 * The fix gates getDisplayTitle() behind $isPageType and uses getWikiValue() otherwise.
	 */
	public function testProcessResultRowUsesWikiValueForNonPageType(): void {
		$request = $this->makePrintRequest( '_dat' );

		// Use addMethods to allow stubbing concrete methods on the abstract base class.
		// getDisplayTitle() is intentionally NOT added: calling it would throw
		// "Call to undefined method", reproducing the original bug.
		// getPreferredCaption() returns a non-empty string so getText() (which also
		// doesn't exist on the base class) is never reached via short-circuit evaluation.
		$dv = $this->getMockBuilder( SMWDataValue::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getPreferredCaption', 'getWikiValue' ] )
			->getMockForAbstractClass();

		$dv->method( 'getWikiValue' )->willReturn( '1 January 2024' );
		$dv->method( 'getPreferredCaption' )->willReturn( '1 January 2024' );

		$resultArray = $this->makeResultArray( $request, [ $dv ] );

		$printer = $this->makePrinter();
		$ref = new ReflectionMethod( GraphPrinter::class, 'processResultRow' );
		$ref->setAccessible( true );

		// Must not throw — prior to the fix this would be a fatal "Call to undefined method"
		$ref->invoke( $printer, [ $resultArray ] );

		$this->addToAssertionCount( 1 );
	}

	/**
	 * Page-type data values (_wpg) must still use getDisplayTitle(), falling back to getWikiValue().
	 */
	public function testProcessResultRowUsesDisplayTitleForPageType(): void {
		$request = $this->makePrintRequest( '_wpg' );

		$dv = $this->getMockBuilder( SMWWikiPageValue::class )
			->disableOriginalConstructor()
			->getMock();

		$dv->method( 'getWikiValue' )->willReturn( 'SomePage' );
		$dv->method( 'getPreferredCaption' )->willReturn( 'Some Page' );
		$dv->method( 'getText' )->willReturn( 'SomePage' );
		$dv->expects( $this->atLeastOnce() )
			->method( 'getDisplayTitle' )
			->willReturn( 'Some Page' );

		$resultArray = $this->makeResultArray( $request, [ $dv ] );

		$printer = $this->makePrinter();
		$ref = new ReflectionMethod( GraphPrinter::class, 'processResultRow' );
		$ref->setAccessible( true );

		$ref->invoke( $printer, [ $resultArray ] );

		$this->addToAssertionCount( 1 );
	}
}
