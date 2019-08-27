<?php

namespace SRF\Tests\Unit\Formats;

use SRF\Graph\GraphFormatter;
use SRF\Graph\GraphNode;


/**
 * @covers \SRF\Graph\GraphFormatter
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 *
 * @author Sebastian Schmid (gesinn.it)
 */
class GraphFormatterTest extends \PHPUnit_Framework_TestCase {

	/*
	* @see https://www.semantic-mediawiki.org/wiki/Help:Graph_format
	*/
	private $options;

	private $graphFormatter;

	private $nodes = [];

	protected function setUp() {
		parent::setUp();

		$this->options->graphName =  'Unit Test';
		$this->options->graphSize = '100';
		$this->options->nodeShape = 'rect';
		$this->options->nodeLabel = 'displaytitle';
		$this->options->rankDir = 'TB';
		$this->options->wordWrapLimit = '20';
		$this->options->parentRelation = 'parent';
		$this->options->enableGraphLink = 'yes';
		$this->options->showGraphLabel = 'yes';
		$this->options->showGraphColor = 'yes';
		$this->options->showGraphLegend = 'yes';

		$this->graphFormatter = new GraphFormatter( $this->options );

		$this->nodes = [];

		$node1 = new GraphNode( 'Team:Alpha' );
		$node1->setLabel( "Alpha" );
		$node1->addParentNode( "Casted", "Person:Alexander Gesinn" );
		$this->nodes[] = $node1;

		$node2 = new GraphNode( 'Team:Beta' );
		$node2->setLabel( "Beta" );
		$node2->addParentNode( "Casted", "Person:Sebastian Schmid" );
		$node2->addParentNode( "Casted", "Person:Alexander Gesinn" );
		$node2->addParentNode( "Part of Team ", "Team:Alpha" );
		$this->nodes[] = $node2;

		$this->graphFormatter->buildGraph( $this->nodes );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf( GraphFormatter::class, new GraphFormatter( $this->options ) );
	}

	public function testGetWordWrappedText() {
		$text = 'Lorem ipsum dolor sit amet';
		$expected = 'Lorem \nipsum \ndolor sit \namet';

		$this->assertEquals( GraphFormatter::getWordWrappedText( $text, 10 ), $expected );
	}

	public function testGetGraphLegend() {
		$expected = "<div class=\"graphlegend\">".
					"<div class=\"graphlegenditem\" style=\"color: black\">black: Casted</div>".
					"<div class=\"graphlegenditem\" style=\"color: red\">red: Part of Team </div>".
					"</div>";

		$this->assertEquals( $this->graphFormatter->getGraphLegend(), $expected );
	}

	public function testBuildGraph(){
		$expected = "digraph Unit Test {graph [fontsize=10, fontname=\"Verdana\"]\n".
					"node [fontsize=10, fontname=\"Verdana\"];\n".
					"edge [fontsize=10, fontname=\"Verdana\"];\n".
					"size=\"100\";node [shape=rect];rankdir=TB;".
					"\"Team:Alpha\" [URL = \"[[Team:Alpha]]\", label = \"Alpha\"]; ".
					"\"Team:Beta\" [URL = \"[[Team:Beta]]\", label = \"Beta\"];  ".
					"\"Person:Alexander Gesinn\" -> \"Team:Alpha\" [label=\"Casted\",fontcolor=black,color=black]; ".
					"\"Person:Sebastian Schmid\" -> \"Team:Beta\" [label=\"Casted\",fontcolor=black,color=black]; ".
					"\"Person:Alexander Gesinn\" -> \"Team:Beta\" [label=\"Casted\",fontcolor=black,color=black]; ".
					"\"Team:Alpha\" -> \"Team:Beta\" [label=\"Part of Team \",fontcolor=red,color=red];}";

		$this->assertEquals( $this->graphFormatter->getGraph(), $expected);
	}
}