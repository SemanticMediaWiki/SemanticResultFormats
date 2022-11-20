<?php

namespace SRF\Tests\Unit\Formats;

use SRF\Graph\GraphFormatter;
use SRF\Graph\GraphNode;
use SRF\Graph\GraphOptions;

/**
 * @covers \SRF\Graph\GraphFormatter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 *
 * @author Sebastian Schmid (gesinn.it)
 */
class GraphFormatterTest extends \PHPUnit\Framework\TestCase {

	/** @var array $cases An array of test cases. */
	private $cases = [
		'Simple' => [
			'params' => [ 'graphfields' => false ], // @see https://www.semantic-mediawiki.org/wiki/Help:Graph_format
			'nodes' => [
				[ 'name' => 'Team:Alpha', 'label' => 'Alpha', 'parents' => [
					[ 'predicate' => 'Casted', 'object' => 'Person:Alexander Gesinn' ]
				] ],
				[ 'name' => 'Team:Beta', 'label' => 'Beta', 'parents' => [
					[ 'predicate' => 'Casted', 'object' => 'Person:Sebastian Schmid' ],
					[ 'predicate' => 'Casted', 'object' => 'Person:Alexander Gesinn' ],
					[ 'predicate' => 'Part of Team', 'object' => 'Team:Alpha' ],
				] ]
			],
			'legend' => '<div class="graphlegend">' .
				'<div class="graphlegenditem" style="color: black">black: Casted</div>' .
				'<div class="graphlegenditem" style="color: red">red: Part of Team</div>' .
				'</div>',
			'dot' => <<<'SIMPLE'
digraph "Unit Test" {graph [fontsize=10, fontname="Verdana"]
node [fontsize=10, fontname="Verdana"];
edge [fontsize=10, fontname="Verdana"];
size="100";node [shape=rect];rankdir=LR;
"Team:Alpha" [URL = "[[Team:Alpha]]", label = "Alpha"];
"Team:Beta" [URL = "[[Team:Beta]]", label = "Beta"];
"Person:Alexander Gesinn" -> "Team:Alpha" [label="Casted",fontcolor=black,arrowhead=diamond,color=black];
"Person:Sebastian Schmid" -> "Team:Beta" [label="Casted",fontcolor=black,arrowhead=diamond,color=black];
"Person:Alexander Gesinn" -> "Team:Beta" [label="Casted",fontcolor=black,arrowhead=diamond,color=black];
"Team:Alpha" -> "Team:Beta" [label="Part of Team",fontcolor=red,arrowhead=diamond,color=red];
}
SIMPLE
		],
		'With fields' => [
			'params' => [ 'graphfields' => true ], // @see https://www.semantic-mediawiki.org/wiki/Help:Graph_format
			'nodes' => [
				[ 'name' => 'Team:Alpha', 'label' => 'Alpha', 'parents' => [
					[ 'predicate' => 'Casted', 'object' => 'Person:Alexander Gesinn' ]
				], 'fields' => [
					[ 'name' => 'Rated as', 'value' => 10, 'type' => '_num', 'page' => 'Rating' ]
				] ],
				[ 'name' => 'Team:Beta', 'label' => 'Beta', 'parents' => [
					[ 'predicate' => 'Casted', 'object' => 'Person:Sebastian Schmid' ],
					[ 'predicate' => 'Casted', 'object' => 'Person:Alexander Gesinn' ],
					[ 'predicate' => 'Part of Team', 'object' => 'Team:Alpha' ],
				], 'fields' => [
					[ 'name' => 'Rated as', 'value' => 20, 'type' => '_num', 'page' => 'Rating' ]
				] ]
			],
			'legend' => '<div class="graphlegend">' .
				'<div class="graphlegenditem" style="color: black">black: Casted</div>' .
				'<div class="graphlegenditem" style="color: red">red: Part of Team</div>' .
				'</div>',
			'dot' => <<<'FIELDS'
digraph "Unit Test" {graph [fontsize=10, fontname="Verdana"]
node [fontsize=10, fontname="Verdana"];
edge [fontsize=10, fontname="Verdana"];
size="100";node [shape=rect];rankdir=LR;
"Team:Alpha" [label = <
<table border="0" cellborder="0" cellspacing="1" columns="*" rows="*">
<tr><td colspan="2" href="[[Team:Alpha]]">Alpha</td></tr><hr/>
<tr><td align="left" href="[[Property:Rating]]">Rated as</td><td align="right">10</td></tr>
</table>
>, tooltip = "Alpha"];
"Team:Beta" [label = <
<table border="0" cellborder="0" cellspacing="1" columns="*" rows="*">
<tr><td colspan="2" href="[[Team:Beta]]">Beta</td></tr><hr/>
<tr><td align="left" href="[[Property:Rating]]">Rated as</td><td align="right">20</td></tr>
</table>
>, tooltip = "Beta"];
"Person:Alexander Gesinn" -> "Team:Alpha" [label="Casted",fontcolor=black,arrowhead=diamond,color=black];
"Person:Sebastian Schmid" -> "Team:Beta" [label="Casted",fontcolor=black,arrowhead=diamond,color=black];
"Person:Alexander Gesinn" -> "Team:Beta" [label="Casted",fontcolor=black,arrowhead=diamond,color=black];
"Team:Alpha" -> "Team:Beta" [label="Part of Team",fontcolor=red,arrowhead=diamond,color=red];
}
FIELDS
		]
	];

	/** @const array BASE_PARAMS A non-changing subset of parameters. */
	/** @see https://www.semantic-mediawiki.org/wiki/Help:Graph_format */
	private const BASE_PARAMS = [
		'graphname' => 'Unit Test',
		'graphsize' => '100',
		'graphfontsize' => 10,
		'nodeshape' => 'rect',
		'nodelabel' => 'displaytitle',
		'arrowdirection' => 'LR',
		'arrowhead' => 'diamond',
		'wordwraplimit' => 20,
		'relation' => 'parent',
		'graphlink' => true,
		'graphlabel' => true,
		'graphcolor' => true,
		'graphlegend' => true,
	];

	/**
	 * Create a complete graph for the test case.
	 * @var array $case
	 * @return GraphFormatter
	 */
	private static function graph( array $case ): GraphFormatter {
		$graph = new GraphFormatter( new GraphOptions( GraphFormatterTest::BASE_PARAMS + $case['params'] ) );
		$nodes = [];
		foreach ( $case['nodes'] as $node ) {
			$graph_node = new GraphNode( $node['name'] );
			$graph_node->setLabel( $node['label'] );
			if ( isset( $node['parents'] ) ) {
				foreach ( $node['parents'] as $parent ) {
					$graph_node->addParentNode( $parent['predicate'], $parent['object'] );
				}
			}
			if ( isset( $node['fields'] ) ) {
				foreach ( $node['fields'] as $field ) {
					$graph_node->addField( $field['name'], $field['value'], $field['type'], $field['page'] );
				}
			}
			$nodes[] = $graph_node;
		}
		$graph->buildGraph( $nodes );
		return $graph;
	}

	/**
	 * @return array Test cases.
	 */
	public function provideCanConstruct(): array {
		$cases = [];
		foreach ( $this->cases as $name => $case ) {
			$cases[$name] = [ self::BASE_PARAMS + $case['params'] ];
		}
		return $cases;
	}

	/**
	 * @covers GraphFormatter::__construct()
	 * @dataProvider provideCanConstruct
	 * @param array $params
	 * @return void
	 */
	public function testCanConstruct( array $params ) {
		$this->assertInstanceOf( GraphFormatter::class, new GraphFormatter( new GraphOptions( $params ) ) );
	}

	/**
	 * @return array
	 */
	public function provideGetWordWrappedText(): array {
		return [
			'Simple wrap' => [
				'Lorem ipsum dolor sit amet',
				<<<'WRAPPED0'
Lorem
ipsum
dolor sit
amet
WRAPPED0
			],
			'Unwrappable' => [ 'Supercalifragilisticexpialidocious', 'Supercalifragilisticexpialidocious' ],
			'One line' => [ 'One line', 'One line' ],
			'Empty' => [ '', '' ]
		];
	}

	/**
	 * @covers GraphFormatter::getWordWrappedText()
	 * @dataProvider provideGetWordWrappedText
	 * @param string $unwrapped
	 * @param string $wrapped
	 * @return void
	 */
	public function testGetWordWrappedText( $unwrapped, $wrapped ) {
		$formatter = new GraphFormatter(
			new GraphOptions( GraphFormatterTest::BASE_PARAMS + ['graphfields' => false] )
		);
		$this->assertEquals( $wrapped, $formatter->getWordWrappedText( $unwrapped, 10 ) );
	}

	/**
	 * @return array
	 */
	public function provideGetGraphLegend(): array {
		$cases = [];
		foreach ( $this->cases as $name => $case ) {
			$cases[$name] = [ $case, $case['legend'] ];
		}
		return $cases;
	}

	/**
	 * @covers GraphFormatter::getGraphLegend()
	 * @dataProvider provideGetGraphLegend
	 * @param array $params
	 * @param string $expected The expected legend.
	 * @return void
	 */
	public function testGetGraphLegend( array $params, $expected ) {
		$this->assertEquals( $expected, self::graph( $params )->getGraphLegend() );
	}

	/**
	 * @return array
	 */
	public function provideBuildGraph(): array {
		$cases = [];
		foreach ( $this->cases as $name => $case ) {
			$cases[$name] = [ $case, $case['dot'] ];
		}
		return $cases;
	}

	/**
	 * @covers GraphFormatter::buildGraph()
	 * @dataProvider provideBuildGraph
	 * @param array $params
	 * @param string $expected The expected DOT code.
	 * @return void
	 */
	public function testBuildGraph( array $params, $expected ) {
		$this->assertEquals( $expected, self::graph( $params )->getGraph() );
	}
}
