<?php

namespace SRF\Tests\Unit\Formats;

use SRF\Graph\GraphOptions;

/**
 * @covers \SRF\Graph\GraphOptions
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 *
 * @author gesinn-it-gea
 */
class GraphOptionsTest extends \PHPUnit\Framework\TestCase {

	private const BASE = [
		'graphname'      => 'My Graph',
		'graphsize'      => '10',
		'graphfontsize'  => 12,
		'nodeshape'      => 'box',
		'nodelabel'      => 'displaytitle',
		'arrowdirection' => 'LR',
		'arrowhead'      => 'normal',
		'wordwraplimit'  => 25,
		'relation'       => 'child',
		'graphlink'      => true,
		'graphlabel'     => true,
		'graphcolor'     => true,
		'graphlegend'    => true,
		'graphfields'    => false,
		'graphfieldspages' => false,
	];

	private function opts( array $overrides = [] ): GraphOptions {
		return new GraphOptions( array_merge( self::BASE, $overrides ) );
	}

	public function testCanConstruct(): void {
		$this->assertInstanceOf( GraphOptions::class, $this->opts() );
	}

	public function testGetGraphName(): void {
		$this->assertSame( 'My Graph', $this->opts()->getGraphName() );
	}

	public function testGetGraphNameStripsSpecialChars(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'graphname' => 'Test!@#Graph' ] ) );
		$this->assertSame( 'TestGraph', $opts->getGraphName() );
	}

	public function testGetGraphNamePreservesAlphanumericAndSpaces(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'graphname' => 'Unit Test 42' ] ) );
		$this->assertSame( 'Unit Test 42', $opts->getGraphName() );
	}

	public function testGetGraphNameTrimmed(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'graphname' => '  spaced  ' ] ) );
		$this->assertSame( 'spaced', $opts->getGraphName() );
	}

	public function testGetGraphSize(): void {
		$this->assertSame( '10', $this->opts()->getGraphSize() );
	}

	public function testGetGraphFontSize(): void {
		$this->assertSame( 12, $this->opts()->getGraphFontSize() );
	}

	public function testGetNodeShape(): void {
		$this->assertSame( 'box', $this->opts()->getNodeShape() );
	}

	public function testGetNodeLabel(): void {
		$this->assertSame( 'displaytitle', $this->opts()->getNodeLabel() );
	}

	public function testGetRankDir(): void {
		$this->assertSame( 'LR', $this->opts()->getRankDir() );
	}

	public function testGetRankDirIsUpperCase(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'arrowdirection' => 'lr' ] ) );
		$this->assertSame( 'LR', $opts->getRankDir() );
	}

	public function testGetArrowHead(): void {
		$this->assertSame( 'normal', $this->opts()->getArrowHead() );
	}

	public function testGetWordWrapLimit(): void {
		$this->assertSame( 25, $this->opts()->getWordWrapLimit() );
	}

	public function testGetParentRelationReturnsFalseForChild(): void {
		$this->assertFalse( $this->opts()->getParentRelation() );
	}

	public function testGetParentRelationReturnsTrueForParent(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'relation' => 'parent' ] ) );
		$this->assertTrue( $opts->getParentRelation() );
	}

	public function testGetParentRelationIsCaseInsensitive(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'relation' => 'PARENT' ] ) );
		$this->assertTrue( $opts->getParentRelation() );
	}

	public function testIsGraphLink(): void {
		$this->assertTrue( $this->opts()->isGraphLink() );
	}

	public function testIsGraphLinkFalse(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'graphlink' => false ] ) );
		$this->assertFalse( $opts->isGraphLink() );
	}

	public function testIsGraphLabel(): void {
		$this->assertTrue( $this->opts()->isGraphLabel() );
	}

	public function testIsGraphColor(): void {
		$this->assertTrue( $this->opts()->isGraphColor() );
	}

	public function testIsGraphLegend(): void {
		$this->assertTrue( $this->opts()->isGraphLegend() );
	}

	public function testShowGraphFields(): void {
		$this->assertFalse( $this->opts()->showGraphFields() );
	}

	public function testShowGraphFieldsTrue(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'graphfields' => true ] ) );
		$this->assertTrue( $opts->showGraphFields() );
	}

	public function testShowGraphFieldsPages(): void {
		$this->assertFalse( $this->opts()->showGraphFieldsPages() );
	}

	public function testShowGraphFieldsPagesTrue(): void {
		$opts = new GraphOptions( array_merge( self::BASE, [ 'graphfieldspages' => true ] ) );
		$this->assertTrue( $opts->showGraphFieldsPages() );
	}
}
