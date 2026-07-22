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
	 * Regression test for https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1096
	 *
	 * When the first page-type printout's value has a property (so it is not eligible to
	 * become the node) and a second page-type printout is seen (pageTypeSeen > 1, i.e.
	 * skipNode=true), the node must NOT be created from that second, skip-eligible value.
	 * Prior to the fix, the node-creation check inside the `showAsEdge` branch omitted the
	 * `!$skipNode` guard applied to the first node-creation check, so it created a node
	 * from data that should have been skipped.
	 */
	public function testProcessResultRowDoesNotCreateNodeFromSkippedPageTypeValue(): void {
		$firstRequest = $this->makePrintRequest( '_wpg' );
		$dvWithProperty = $this->getMockBuilder( SMWWikiPageValue::class )
			->disableOriginalConstructor()
			->getMock();
		$dvWithProperty->method( 'getWikiValue' )->willReturn( 'Subject' );
		$dvWithProperty->method( 'getDisplayTitle' )->willReturn( 'Subject' );
		$dvWithProperty->method( 'getProperty' )->willReturn( $this->createMock( \SMW\DIProperty::class ) );
		$firstResultArray = $this->makeResultArray( $firstRequest, [ $dvWithProperty ] );

		$secondRequest = $this->makePrintRequest( '_wpg' );
		$dvSkippable = $this->getMockBuilder( SMWWikiPageValue::class )
			->disableOriginalConstructor()
			->getMock();
		$dvSkippable->method( 'getWikiValue' )->willReturn( 'ShouldBeSkipped' );
		$dvSkippable->method( 'getDisplayTitle' )->willReturn( 'ShouldBeSkipped' );
		$dvSkippable->method( 'getProperty' )->willReturn( null );
		$secondResultArray = $this->makeResultArray( $secondRequest, [ $dvSkippable ] );

		$printer = $this->makePrinter();
		$ref = new ReflectionMethod( GraphPrinter::class, 'processResultRow' );
		$ref->setAccessible( true );
		$ref->invoke( $printer, [ $firstResultArray, $secondResultArray ] );

		$nodesRef = new \ReflectionProperty( GraphPrinter::class, 'nodes' );
		$nodesRef->setAccessible( true );
		$nodes = $nodesRef->getValue( $printer );

		$this->assertSame( [], $nodes, 'No node should be created when the only node-eligible value is skip-eligible.' );
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
