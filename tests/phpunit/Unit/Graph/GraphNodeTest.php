<?php

namespace SRF\Tests\Unit\Formats;

use SRF\Graph\GraphNode;


/**
 * @covers \SRF\Graph\GraphNode
 * @group semantic-result-formats
 *
 * @licence GNU GPL v2+
 * @since 3.1
 *
 * @author Sebastian Schmid < sebastian.schmid@gesinn.it >
 */

class GraphNodeTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			GraphNode::class,
			new GraphNode( "graphnode:id" )
		);
	}

	public function testGraphNode(){
		$node = new GraphNode( 'Team:Beta' );
		$this->assertEquals( 'Team:Beta', $node->getID() );

		$node->setLabel( "Fossil Power Generation" );
		$this->assertEquals( "Fossil Power Generation", $node->getLabel() );
	}

	public function testAddParentNode(){
		$mockParentNode1[] = [
			"predicate" => 'Part Of Team',
			"object"    => 'Alpha Team'
		];

		$node = new GraphNode( 'Team:Beta' );

		$node->addParentNode( 'Part Of Team', 'Alpha Team' );
		$this->assertEquals( $mockParentNode1, $node->getParentNode() );
	}

}
