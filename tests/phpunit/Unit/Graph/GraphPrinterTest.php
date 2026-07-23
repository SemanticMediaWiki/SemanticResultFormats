<?php

declare( strict_types=1 );

namespace SRF\Tests\Unit\Graph;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use SMW\DataItems\Property;
use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SMWDataValue;
use SMWWikiPageValue;
use SRF\Graph\GraphFormatter;
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

	private function makePrinter( array $paramOverrides = [] ): GraphPrinter {
		$printer = new GraphPrinter( 'graph' );

		$ref = new ReflectionMethod( GraphPrinter::class, 'handleParameters' );
		$ref->setAccessible( true );
		$ref->invoke( $printer, $paramOverrides + self::BASE_PARAMS, SMW_OUTPUT_HTML );

		return $printer;
	}

	/**
	 * @param string|null $explicitLabel Overrides what getLabel() returns, independent of
	 *   $label/getCanonicalLabel() - used to simulate a suppressed "?Property=" printout
	 *   label, which SMW represents as an empty string from getLabel() while
	 *   getCanonicalLabel() keeps returning the property's real name.
	 */
	private function makePrintRequest(
		string $typeId,
		string $label = 'TestProp',
		bool $isChain = false,
		?int $mode = null,
		?string $explicitLabel = null
	): PrintRequest {
		$request = $this->getMockBuilder( PrintRequest::class )
			->disableOriginalConstructor()
			->getMock();

		$request->method( 'getTypeID' )->willReturn( $typeId );
		$request->method( 'getCanonicalLabel' )->willReturn( $label );
		$request->method( 'getLabel' )->willReturn( $explicitLabel ?? $label );
		$request->method( 'isMode' )->willReturnCallback(
			static function ( $queriedMode ) use ( $isChain, $mode ) {
				if ( $mode !== null ) {
					return $queriedMode === $mode;
				}
				return $isChain && $queriedMode === PrintRequest::PRINT_CHAIN;
			}
		);

		return $request;
	}

	/**
	 * Convenience wrapper for a PRINT_THIS ("?=") print request, e.g. an
	 * explicit subject column.
	 */
	private function makeThisPrintRequest( string $label = 'Subject' ): PrintRequest {
		return $this->makePrintRequest( '_wpg', $label, false, PrintRequest::PRINT_THIS );
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

	private function makeProperty(): Property {
		return $this->getMockBuilder( Property::class )
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @param string $id Used for getWikiValue()/getDisplayTitle()/getShortWikiText().
	 * @param bool $hasProperty Whether getProperty() should return a truthy value
	 *   (marks the value as a chained property value rather than a plain page).
	 */
	private function makePageValue( string $id, bool $hasProperty = false, ?string $caption = null ): SMWWikiPageValue {
		$dv = $this->getMockBuilder( SMWWikiPageValue::class )
			->disableOriginalConstructor()
			->getMock();

		$dv->method( 'getWikiValue' )->willReturn( $id );
		$dv->method( 'getDisplayTitle' )->willReturn( $id );
		$dv->method( 'getShortWikiText' )->willReturn( $id );
		$dv->method( 'getPreferredCaption' )->willReturn( $caption ?? $id );
		$dv->method( 'getText' )->willReturn( $id );
		$dv->method( 'getProperty' )->willReturn( $hasProperty ? $this->makeProperty() : null );

		return $dv;
	}

	/**
	 * @param string $value Used for getWikiValue().
	 */
	private function makeTextValue( string $value, bool $hasProperty = false ): SMWDataValue {
		$dv = $this->getMockBuilder( SMWDataValue::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getPreferredCaption', 'getWikiValue', 'getProperty' ] )
			->getMockForAbstractClass();

		$dv->method( 'getWikiValue' )->willReturn( $value );
		$dv->method( 'getPreferredCaption' )->willReturn( $value );
		$dv->method( 'getProperty' )->willReturn( $hasProperty ? $this->makeProperty() : null );

		return $dv;
	}

	/**
	 * Invokes the private processResultRow() and returns the resulting nodes.
	 *
	 * @return \SRF\Graph\GraphNode[]
	 */
	private function processRow( GraphPrinter $printer, array $row ): array {
		$ref = new ReflectionMethod( GraphPrinter::class, 'processResultRow' );
		$ref->setAccessible( true );
		$ref->invoke( $printer, $row );

		$nodesProp = new ReflectionProperty( GraphPrinter::class, 'nodes' );
		$nodesProp->setAccessible( true );

		return $nodesProp->getValue( $printer );
	}

	/**
	 * Processes one or more rows and returns the resulting GraphFormatter, with
	 * buildGraph() already called against the real GraphPrinter -> GraphFormatter
	 * pipeline. Skips only the Diagrams/GraphViz dependency check and the wikitext
	 * <graphviz> tag call in getResultText(), which require a full parser.
	 *
	 * @param GraphPrinter $printer
	 * @param array[] $rows One or more rows, each in the format processResultRow() expects.
	 */
	private function buildFormatter( GraphPrinter $printer, array $rows ): GraphFormatter {
		$processRowRef = new ReflectionMethod( GraphPrinter::class, 'processResultRow' );
		$processRowRef->setAccessible( true );
		foreach ( $rows as $row ) {
			$processRowRef->invoke( $printer, $row );
		}

		$nodesRef = new ReflectionProperty( GraphPrinter::class, 'nodes' );
		$nodesRef->setAccessible( true );
		$nodes = $nodesRef->getValue( $printer );

		$optionsRef = new ReflectionProperty( GraphPrinter::class, 'options' );
		$optionsRef->setAccessible( true );
		$options = $optionsRef->getValue( $printer );

		$formatter = new GraphFormatter( $options );
		$formatter->buildGraph( $nodes );

		return $formatter;
	}

	/**
	 * Same as buildFormatter(), but returns the DOT source (GraphFormatter::getGraph())
	 * directly - exactly what gets handed to the `<graphviz>` tag / the `dot` binary.
	 *
	 * @param GraphPrinter $printer
	 * @param array[] $rows One or more rows, each in the format processResultRow() expects.
	 */
	private function buildDot( GraphPrinter $printer, array $rows ): string {
		return $this->buildFormatter( $printer, $rows )->getGraph();
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

	/**
	 * @return array
	 */
	public static function provideGraphFieldsPagesPageTypeCounts(): array {
		return [
			'0 page-type printouts: plain text value becomes the node itself, no parent, one field' => [
				'requests' => [ [ '_txt', false ] ],
				'expectId' => 'Text1',
				'expectParents' => [],
				'expectFields' => [ [ 'name' => 'TestProp', 'value' => 'Text1' ] ],
			],
			'1 page-type printout: becomes the node, no parent, no field' => [
				'requests' => [ [ '_wpg', false ] ],
				'expectId' => 'Page1',
				'expectParents' => [],
				'expectFields' => [],
			],
			'2 page-type printouts: first is the node, second becomes a parent' => [
				'requests' => [ [ '_wpg', false ], [ '_wpg', false ] ],
				'expectId' => 'Page1',
				'expectParents' => [ [ 'predicate' => 'TestProp', 'object' => 'Page2' ] ],
				'expectFields' => [],
			],
			'2 page-type printouts, second has a property: still a parent, not a field (pins the pageTypeSeen > 2 field threshold)' => [
				'requests' => [ [ '_wpg', false ], [ '_wpg', true ] ],
				'expectId' => 'Page1',
				'expectParents' => [ [ 'predicate' => 'TestProp', 'object' => 'Page2' ] ],
				'expectFields' => [],
			],
			'3+ page-type printouts: 3rd printout (with a property) is added as a field, not a parent' => [
				'requests' => [ [ '_wpg', false ], [ '_wpg', false ], [ '_wpg', true ] ],
				'expectId' => 'Page1',
				'expectParents' => [ [ 'predicate' => 'TestProp', 'object' => 'Page2' ] ],
				'expectFields' => [ [ 'name' => 'TestProp', 'value' => 'Page3' ] ],
			],
		];
	}

	/**
	 * Covers processResultRow() under graphfieldspages=true (and graphfields=true), for
	 * rows with 0, 1, 2, and 3+ page-type printouts. See
	 * https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1124
	 *
	 * @dataProvider provideGraphFieldsPagesPageTypeCounts
	 */
	public function testProcessResultRowWithGraphFieldsPages(
		array $requests,
		string $expectId,
		array $expectParents,
		array $expectFields
	): void {
		$printer = $this->makePrinter( [ 'graphfields' => true, 'graphfieldspages' => true ] );

		$row = [];
		foreach ( $requests as $i => [ $typeId, $hasProperty ] ) {
			$n = $i + 1;
			if ( $typeId === '_wpg' ) {
				$value = $this->makePageValue( "Page$n", $hasProperty );
			} else {
				$value = $this->makeTextValue( "Text$n", $hasProperty );
			}
			$row[] = $this->makeResultArray( $this->makePrintRequest( $typeId ), [ $value ] );
		}

		$nodes = $this->processRow( $printer, $row );

		$this->assertCount( 1, $nodes );
		$this->assertSame( $expectId, $nodes[0]->getID() );
		$this->assertSame( $expectParents, $nodes[0]->getParentNode() );

		$fields = array_map(
			static fn ( array $f ) => [ 'name' => $f['name'], 'value' => $f['value'] ],
			$nodes[0]->getFields()
		);
		$this->assertSame( $expectFields, $fields );
	}

	/**
	 * Covers the case where $node stays null for the entire row (e.g. because every
	 * value's getProperty() is truthy) — the row must be silently dropped, i.e. no
	 * GraphNode is added to $this->nodes.
	 */
	public function testProcessResultRowDropsRowWhenNodeIsNeverCreated(): void {
		$printer = $this->makePrinter();

		$value = $this->makePageValue( 'Page1', true );
		$row = [ $this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $value ] ) ];

		$nodes = $this->processRow( $printer, $row );

		$this->assertSame( [], $nodes );
	}

	/**
	 * Regression test for the $skipNode gap described in
	 * https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1124
	 * (originally reported in issue #1096's comments).
	 *
	 * With graphfields=false (graphfieldspages=false), a row with 2 page-type
	 * printouts where the first has a property (so no node is created for it)
	 * and the second does not: the node-creation guard at the top of the loop
	 * body correctly refuses to create a node from the second value because
	 * $skipNode is true ($pageTypeSeen > 1, i.e. from the second page-type
	 * printout onward). The `elseif ( $showAsEdge )` branch duplicated that
	 * same node-creation check without the guard, so it could still create a
	 * node from it — that duplicate has been removed rather than patched,
	 * since the check at the top of the loop body already governs $node for
	 * every value once execution reaches this branch.
	 */
	public function testProcessResultRowDoesNotCreateNodeFromSkippedPageTypeViaEdgeBranch(): void {
		$printer = $this->makePrinter();

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $this->makePageValue( 'Page1', true ) ] ),
			$this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $this->makePageValue( 'Page2', false ) ] ),
		];

		$nodes = $this->processRow( $printer, $row );

		$this->assertSame( [], $nodes );
	}

	/**
	 * Regression test for the PRINT_THIS ("?=") case raised in review of #1127:
	 * an explicit subject column (PRINT_THIS) must always become the node,
	 * regardless of its position among other page-type printouts, instead of
	 * being skipped by position-based $skipNode and dropping the whole row.
	 *
	 * `{{#ask: ... |?Located in |?=Page }}`: the property-bearing "Located in"
	 * column (pageTypeSeen=1) creates no node; without PRINT_THIS-awareness,
	 * "?=" (pageTypeSeen=2) would then be skipped too and the row would vanish.
	 */
	public function testProcessResultRowUsesThisPrintoutAsNodeRegardlessOfPosition(): void {
		$printer = $this->makePrinter();

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg', 'Located in' ), [ $this->makePageValue( 'Place1', true ) ] ),
			$this->makeResultArray( $this->makeThisPrintRequest(), [ $this->makePageValue( 'Subject', false ) ] ),
		];

		$nodes = $this->processRow( $printer, $row );

		$this->assertCount( 1, $nodes );
		$this->assertSame( 'Subject', $nodes[0]->getID() );
	}

	/**
	 * Mirror case from the same review comment: without PRINT_THIS-awareness, a
	 * property-less page-type column placed *before* "?=" would win the node
	 * slot by position and produce a node named after an unrelated value. The
	 * PRINT_THIS column must take precedence even when it isn't first.
	 */
	public function testProcessResultRowPrefersThisPrintoutOverEarlierPageTypeColumn(): void {
		$printer = $this->makePrinter();

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg', 'Category' ), [ $this->makePageValue( 'UnrelatedValue', false ) ] ),
			$this->makeResultArray( $this->makeThisPrintRequest(), [ $this->makePageValue( 'Subject', false ) ] ),
		];

		$nodes = $this->processRow( $printer, $row );

		$this->assertCount( 1, $nodes );
		$this->assertSame( 'Subject', $nodes[0]->getID() );
	}

	/**
	 * Builds the actual DOT source (GraphFormatter::getGraph()) that gets handed to the
	 * `<graphviz>` tag / the `dot` binary, from the same row as
	 * testProcessResultRowUsesThisPrintoutAsNodeRegardlessOfPosition(). This exercises the
	 * real GraphPrinter -> GraphFormatter pipeline end to end (skipping only the
	 * Diagrams/GraphViz dependency check and the wikitext <graphviz> tag call in
	 * getResultText(), which require a full parser), so a regression in either
	 * processResultRow()'s node selection or GraphFormatter's DOT rendering would show up
	 * here as a wrong node/edge declaration in the generated graph source.
	 */
	public function testProcessResultRowFeedsCorrectDotSourceForThisPrintout(): void {
		$printer = $this->makePrinter();

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg', 'Located in' ), [ $this->makePageValue( 'Place1', true ) ] ),
			$this->makeResultArray( $this->makeThisPrintRequest(), [ $this->makePageValue( 'Subject', false ) ] ),
		];

		$dot = $this->buildDot( $printer, [ $row ] );

		$this->assertStringContainsString( '"Subject"', $dot );
		$this->assertStringContainsString( '"Subject" -> "Place1"', $dot );
		$this->assertStringNotContainsString( '"Place1" [', $dot, 'Place1 must only appear as an edge target, never as its own node declaration.' );
	}

	/**
	 * Same DOT-source check for the mirror case: the property-less category column must
	 * not leak into the generated graph as a bogus node - only as an edge from the
	 * PRINT_THIS subject, exactly like any other page-type value in the row.
	 */
	public function testProcessResultRowFeedsCorrectDotSourceWhenThisPrintoutIsNotFirst(): void {
		$printer = $this->makePrinter();

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg', 'Category' ), [ $this->makePageValue( 'UnrelatedValue', false ) ] ),
			$this->makeResultArray( $this->makeThisPrintRequest(), [ $this->makePageValue( 'Subject', false ) ] ),
		];

		$dot = $this->buildDot( $printer, [ $row ] );

		$this->assertStringContainsString( '"Subject"', $dot );
		$this->assertStringContainsString( '"Subject" -> "UnrelatedValue"', $dot );
		$this->assertStringNotContainsString( '"UnrelatedValue" [', $dot, 'UnrelatedValue must only appear as an edge target, never as its own node declaration.' );
	}

	/**
	 * Covers the showGraphFields=true / showGraphFieldsPages=false combination: a
	 * non-page-type value falls into the `!$showGraphFieldsPages && !$showAsEdge`
	 * field-collection branch and is added as a field without going through
	 * $includeAsField at all.
	 */
	public function testProcessResultRowAddsNonPageTypeValueAsFieldWhenGraphFieldsEnabled(): void {
		$printer = $this->makePrinter( [ 'graphfields' => true, 'graphfieldspages' => false ] );

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $this->makePageValue( 'Page1' ) ] ),
			$this->makeResultArray( $this->makePrintRequest( '_txt' ), [ $this->makeTextValue( 'Text1' ) ] ),
		];

		$nodes = $this->processRow( $printer, $row );

		$this->assertCount( 1, $nodes );
		$this->assertSame( 'Page1', $nodes[0]->getID() );
		$fields = array_map(
			static fn ( array $f ) => [ 'name' => $f['name'], 'value' => $f['value'] ],
			$nodes[0]->getFields()
		);
		$this->assertSame( [ [ 'name' => 'TestProp', 'value' => 'Text1' ] ], $fields );
	}

	/**
	 * Regression test for https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1131
	 *
	 * An explicitly suppressed printout label (e.g. "?Property=") must stay empty rather
	 * than falling back to the canonical property name. getLabel() returns '' in that case
	 * while getCanonicalLabel() keeps returning the real property name.
	 */
	public function testProcessResultRowKeepsFieldNameEmptyWhenLabelIsExplicitlySuppressed(): void {
		$printer = $this->makePrinter( [ 'graphfields' => true, 'graphfieldspages' => false ] );

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $this->makePageValue( 'Page1' ) ] ),
			$this->makeResultArray(
				$this->makePrintRequest( '_txt', 'ExampleProperty', false, null, '' ),
				[ $this->makeTextValue( 'ExampleValue' ) ]
			),
		];

		$nodes = $this->processRow( $printer, $row );

		$this->assertCount( 1, $nodes );
		$fields = array_map(
			static fn ( array $f ) => [ 'name' => $f['name'], 'value' => $f['value'] ],
			$nodes[0]->getFields()
		);
		$this->assertSame( [ [ 'name' => '', 'value' => 'ExampleValue' ] ], $fields );
	}

	/**
	 * Covers the graphfields=false / graphfieldspages=true combination (reachable via
	 * `|graphfieldspages=yes` alone): $includeAsField is always false because it requires
	 * $showGraphFields, so a second page-type printout still becomes a parent via the
	 * $showGraphFieldsPages edge branch, but no field is ever added for it.
	 */
	public function testProcessResultRowWithGraphFieldsPagesButNotGraphFields(): void {
		$printer = $this->makePrinter( [ 'graphfields' => false, 'graphfieldspages' => true ] );

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $this->makePageValue( 'Page1' ) ] ),
			$this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $this->makePageValue( 'Page2' ) ] ),
		];

		$nodes = $this->processRow( $printer, $row );

		$this->assertCount( 1, $nodes );
		$this->assertSame( 'Page1', $nodes[0]->getID() );
		$this->assertSame(
			[ [ 'predicate' => 'TestProp', 'object' => 'Page2' ] ],
			$nodes[0]->getParentNode()
		);
		$this->assertSame( [], $nodes[0]->getFields() );
	}

	/**
	 * Basic sanity check for getParamDefinitions(): the Graph-specific params exist
	 * with the documented default values.
	 */
	public function testGetParamDefinitionsReturnsGraphParamsWithDefaults(): void {
		$printer = new GraphPrinter( 'graph' );

		$params = $printer->getParamDefinitions( [] );

		$this->assertArrayHasKey( 'graphname', $params );
		$this->assertSame( 'QueryResult', $params['graphname']['default'] );

		$this->assertArrayHasKey( 'graphfields', $params );
		$this->assertFalse( $params['graphfields']['default'] );

		$this->assertArrayHasKey( 'graphfieldspages', $params );
		$this->assertFalse( $params['graphfieldspages']['default'] );

		$this->assertArrayHasKey( 'arrowdirection', $params );
		$this->assertSame( 'LR', $params['arrowdirection']['default'] );
		$this->assertSame( [ 'LR', 'RL', 'TB', 'BT' ], $params['arrowdirection']['values'] );
	}

	/**
	 * handleParameters() must construct a GraphOptions instance that reflects the
	 * given params, so that processResultRow() consults the intended configuration.
	 */
	public function testHandleParametersBuildsGraphOptionsFromParams(): void {
		$printer = $this->makePrinter( [ 'graphfields' => true, 'graphfieldspages' => true ] );

		$optionsProp = new ReflectionProperty( GraphPrinter::class, 'options' );
		$optionsProp->setAccessible( true );
		$options = $optionsProp->getValue( $printer );

		$this->assertInstanceOf( \SRF\Graph\GraphOptions::class, $options );
		$this->assertTrue( $options->showGraphFields() );
		$this->assertTrue( $options->showGraphFieldsPages() );
	}

	/**
	 * Single-node row used by the DOT-source parameter tests below: they only care about
	 * how the graph-wide/node-level settings ($this->makePrinter( $paramOverrides )) shape
	 * the output, not about node-selection edge cases (already covered above).
	 *
	 * @return array[] A one-row, one-value row for makeResultArray()/processRow().
	 */
	private function singleNodeRow(): array {
		return [
			$this->makeResultArray( $this->makePrintRequest( '_wpg', 'Located in' ), [ $this->makePageValue( 'Place1' ) ] ),
		];
	}

	/**
	 * graphname is filtered through GraphOptions::getGraphName()'s
	 * preg_replace('/[^A-Za-z0-9 ]/', '', ...), which strips characters that would
	 * otherwise make the digraph declaration invalid DOT syntax.
	 */
	public function testGraphNameParamIsSanitizedInDotSource(): void {
		$printer = $this->makePrinter( [ 'graphname' => 'My-Graph #1!' ] );

		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );

		$this->assertStringStartsWith( 'digraph "MyGraph 1" {', $dot );
	}

	public function testGraphFontSizeParamSetsFontsizeInDotSource(): void {
		$printer = $this->makePrinter( [ 'graphfontsize' => 18 ] );

		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );

		$this->assertStringContainsString( 'graph [fontsize=18, fontname="Verdana"]', $dot );
		$this->assertStringContainsString( 'node [fontsize=18, fontname="Verdana"];', $dot );
		$this->assertStringContainsString( 'edge [fontsize=18, fontname="Verdana"];', $dot );
	}

	/**
	 * graphsize is only emitted when non-empty (GraphFormatter::buildGraph()'s
	 * `if ( $this->options->getGraphSize() !== '' )` guard).
	 */
	public function testGraphSizeParamSetsSizeInDotSourceOnlyWhenNonEmpty(): void {
		$printer = $this->makePrinter( [ 'graphsize' => '' ] );
		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );
		$this->assertStringNotContainsString( 'size="', $dot );

		$printer = $this->makePrinter( [ 'graphsize' => '8,5' ] );
		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );
		$this->assertStringContainsString( 'size="8,5";', $dot );
	}

	/**
	 * nodeshape is only emitted when non-empty (GraphFormatter::buildGraph()'s
	 * `if ( $this->options->getNodeShape() != '' )` guard).
	 */
	public function testNodeShapeParamSetsShapeInDotSourceOnlyWhenSet(): void {
		$printer = $this->makePrinter( [ 'nodeshape' => false ] );
		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );
		$this->assertStringNotContainsString( 'node [shape=', $dot );

		$printer = $this->makePrinter( [ 'nodeshape' => 'diamond' ] );
		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );
		$this->assertStringContainsString( 'node [shape=diamond];', $dot );
	}

	/**
	 * @dataProvider provideArrowDirections
	 */
	public function testArrowDirectionParamSetsRankdirInDotSource( string $arrowDirection ): void {
		$printer = $this->makePrinter( [ 'arrowdirection' => $arrowDirection ] );

		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );

		$this->assertStringContainsString( "rankdir=$arrowDirection;", $dot );
	}

	/**
	 * @return array
	 */
	public static function provideArrowDirections(): array {
		return [
			'Left to right' => [ 'LR' ],
			'Right to left' => [ 'RL' ],
			'Top to bottom' => [ 'TB' ],
			'Bottom to top' => [ 'BT' ],
		];
	}

	/**
	 * graphlink adds a clickable URL (a wikilink to the node's page) to the node
	 * declaration, only when the node has no fields (the field-table branch always
	 * hyperlinks the top row itself instead, see GraphFormatter::buildGraph()).
	 */
	public function testGraphLinkParamAddsUrlToNodeInDotSource(): void {
		$printer = $this->makePrinter( [ 'graphlink' => false ] );
		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );
		$this->assertStringNotContainsString( 'URL = ', $dot );

		$printer = $this->makePrinter( [ 'graphlink' => true ] );
		$dot = $this->buildDot( $printer, [ $this->singleNodeRow() ] );
		$this->assertStringContainsString( 'URL = "[[Place1]]"', $dot );
	}

	/**
	 * Regression test for https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1131
	 *
	 * A field whose printout label is explicitly suppressed (e.g. "?ExampleProperty=")
	 * must render as a bare value in the node's HTML field table, with neither the
	 * canonical property name nor a leftover "name: " / ": " prefix in the DOT source.
	 */
	public function testSuppressedFieldLabelOmitsNameAndColonInDotSource(): void {
		$printer = $this->makePrinter( [ 'graphfields' => true, 'graphfieldspages' => false ] );

		$row = [
			$this->makeResultArray( $this->makePrintRequest( '_wpg' ), [ $this->makePageValue( 'Page1' ) ] ),
			$this->makeResultArray(
				$this->makePrintRequest( '_txt', 'ExampleProperty', false, null, '' ),
				[ $this->makeTextValue( 'ExampleValue' ) ]
			),
		];

		$dot = $this->buildDot( $printer, [ $row ] );

		$this->assertStringContainsString( '<td align="right" href="[[Property:ExampleProperty]]"></td>', $dot );
		$this->assertStringNotContainsString( 'ExampleProperty: ', $dot );
		$this->assertStringNotContainsString( '>: </td>', $dot );
	}

	/**
	 * nodelabel=displaytitle word-wraps the node's label via
	 * GraphFormatter::getWordWrappedText(); anything else leaves it untouched.
	 *
	 * Whether the wrapped label renders as an HTML label (<...>) or a quoted string label
	 * ("...") depends on whether the Diagrams extension is loaded (see
	 * GraphFormatter::__construct()/buildGraph(), and issue #846): under Diagrams, wrapped
	 * lines are joined with a literal '<br />' and rendered as an HTML label so GraphViz
	 * interprets it as a line break; otherwise they're joined with PHP_EOL and rendered as
	 * a quoted string label. Built with a placeholder and substituted at assertion time,
	 * same approach as GraphFormatterTest::getWordWrappedText().
	 */
	public function testNodeLabelDisplaytitleWordWrapsNodeLabelInDotSource(): void {
		$makeRow = fn () => [
			$this->makeResultArray(
				$this->makePrintRequest( '_wpg', 'Located in' ),
				[ $this->makePageValue( 'Place1', false, 'A somewhat long node label' ) ]
			),
		];

		$printer = $this->makePrinter( [ 'nodelabel' => '', 'wordwraplimit' => 10 ] );
		$dot = $this->buildDot( $printer, [ $makeRow() ] );
		$this->assertStringContainsString( 'label = "A somewhat long node label"', $dot );

		$printer = $this->makePrinter( [ 'nodelabel' => 'displaytitle', 'wordwraplimit' => 10 ] );
		$dot = $this->buildDot( $printer, [ $makeRow() ] );
		$lines = [ 'A somewhat', 'long node', 'label' ];
		if ( \ExtensionRegistry::getInstance()->isLoaded( 'Diagrams' ) ) {
			$expected = 'label = <' . implode( '<br />', $lines ) . '>';
		} else {
			$expected = 'label = "' . implode( "\n", $lines ) . '"';
		}
		$this->assertStringContainsString( $expected, $dot );
	}

	/**
	 * graphlabel/graphcolor/arrowhead only apply to edges, so a two-node row (a node with a
	 * parent) is needed. graphlabel adds the predicate as an edge label, graphcolor adds
	 * fontcolor/color, and arrowhead sets the edge's arrowhead style - but only once
	 * graphlabel or graphcolor is enabled (GraphFormatter::buildGraph()'s
	 * `if ( isGraphLabel() || isGraphColor() )` guard around the whole `[...]` block).
	 */
	private function twoNodeRowWithEdge(): array {
		return [
			$this->makeResultArray( $this->makePrintRequest( '_wpg', 'Located in' ), [ $this->makePageValue( 'Place1' ) ] ),
			$this->makeResultArray( $this->makePrintRequest( '_wpg', 'Located in' ), [ $this->makePageValue( 'Place2' ) ] ),
		];
	}

	public function testEdgeAttributesAreOmittedWhenGraphLabelAndGraphColorAreOff(): void {
		$printer = $this->makePrinter( [ 'graphlabel' => false, 'graphcolor' => false ] );

		$dot = $this->buildDot( $printer, [ $this->twoNodeRowWithEdge() ] );

		$this->assertStringContainsString( '"Place1" -> "Place2";', $dot );
	}

	public function testGraphLabelParamAddsPredicateLabelToEdgeInDotSource(): void {
		$printer = $this->makePrinter( [ 'graphlabel' => true, 'graphcolor' => false ] );

		$dot = $this->buildDot( $printer, [ $this->twoNodeRowWithEdge() ] );

		$this->assertStringContainsString( 'label="Located in"', $dot );
	}

	public function testGraphColorParamAddsColorToEdgeInDotSource(): void {
		$printer = $this->makePrinter( [ 'graphlabel' => false, 'graphcolor' => true ] );

		$dot = $this->buildDot( $printer, [ $this->twoNodeRowWithEdge() ] );

		$this->assertStringContainsString( 'color=black', $dot );
	}

	public function testArrowHeadParamSetsArrowheadOnEdgeInDotSourceOnlyWhenLabelOrColorEnabled(): void {
		$printer = $this->makePrinter( [ 'arrowhead' => 'diamond', 'graphlabel' => false, 'graphcolor' => false ] );
		$dot = $this->buildDot( $printer, [ $this->twoNodeRowWithEdge() ] );
		$this->assertStringNotContainsString( 'arrowhead=', $dot );

		$printer = $this->makePrinter( [ 'arrowhead' => 'diamond', 'graphcolor' => true ] );
		$dot = $this->buildDot( $printer, [ $this->twoNodeRowWithEdge() ] );
		$this->assertStringContainsString( 'arrowhead=diamond,', $dot );
	}

	/**
	 * relation=parent reverses the edge direction GraphFormatter::buildGraph() emits,
	 * compared to the default relation=child.
	 */
	public function testRelationParamControlsEdgeDirectionInDotSource(): void {
		$printer = $this->makePrinter( [ 'relation' => 'child' ] );
		$dot = $this->buildDot( $printer, [ $this->twoNodeRowWithEdge() ] );
		$this->assertStringContainsString( '"Place1" -> "Place2"', $dot );

		$printer = $this->makePrinter( [ 'relation' => 'parent' ] );
		$dot = $this->buildDot( $printer, [ $this->twoNodeRowWithEdge() ] );
		$this->assertStringContainsString( '"Place2" -> "Place1"', $dot );
	}

	/**
	 * graphlegend only renders legend markup once graphcolor is also enabled
	 * (GraphFormatter::getGraphLegend()'s `isGraphLegend() && isGraphColor()` guard), and
	 * only for predicates that were actually used as edges.
	 */
	public function testGraphLegendParamRendersLegendOnlyWithGraphColorEnabled(): void {
		$printer = $this->makePrinter( [ 'graphlegend' => true, 'graphcolor' => false ] );
		$formatter = $this->buildFormatter( $printer, [ $this->twoNodeRowWithEdge() ] );
		$this->assertStringNotContainsString( 'graphlegenditem', $formatter->getGraphLegend() );

		$printer = $this->makePrinter( [ 'graphlegend' => true, 'graphcolor' => true ] );
		$formatter = $this->buildFormatter( $printer, [ $this->twoNodeRowWithEdge() ] );
		$legend = $formatter->getGraphLegend();
		$this->assertStringContainsString( 'graphlegenditem', $legend );
		$this->assertStringContainsString( 'Located in', $legend );
	}
}
