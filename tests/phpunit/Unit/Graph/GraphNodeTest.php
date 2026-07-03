<?php

namespace SRF\Tests\Unit\Formats;

use SRF\Graph\GraphNode;

/**
 * @covers \SRF\Graph\GraphNode
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author Sebastian Schmid < sebastian.schmid@gesinn.it >
 */
class GraphNodeTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			GraphNode::class,
			new GraphNode( "graphnode:id" )
		);
	}

	public function testGraphNode() {
		$node = new GraphNode( 'Team:Beta' );
		$this->assertEquals( 'Team:Beta', $node->getID() );

		$node->setLabel( "Fossil Power Generation" );
		$this->assertEquals( "Fossil Power Generation", $node->getLabel() );
	}

	public function testAddParentNode() {
		$mockParentNode1[] = [
			"predicate" => 'Part Of Team',
			"object"    => 'Alpha Team'
		];

		$node = new GraphNode( 'Team:Beta' );

		$node->addParentNode( 'Part Of Team', 'Alpha Team' );
		$this->assertEquals( $mockParentNode1, $node->getParentNode() );
	}

	public function testGetFieldsReturnsEmptyArrayInitially() {
		$node = new GraphNode( 'Team:Alpha' );
		$this->assertSame( [], $node->getFields() );
	}

	public function testAddFieldStoresField() {
		$node = new GraphNode( 'Team:Alpha' );
		$node->addField( 'Rating', '10', '_num', 'Rating', null );

		$fields = $node->getFields();
		$this->assertCount( 1, $fields );
		$this->assertSame( 'Rating', $fields[0]['name'] );
		$this->assertSame( '10', $fields[0]['value'] );
		$this->assertSame( '_num', $fields[0]['type'] );
		$this->assertSame( 'Rating', $fields[0]['page'] );
		$this->assertNull( $fields[0]['valueLink'] );
	}

	public function testAddFieldUsesPageAsNameWhenNameIsEmpty() {
		$node = new GraphNode( 'Team:Alpha' );
		$node->addField( '', 'some value', '_txt', 'MyPage', null );

		$fields = $node->getFields();
		$this->assertSame( 'MyPage', $fields[0]['name'] );
	}

	public function testAddFieldStoresValueLink() {
		$node = new GraphNode( 'Team:Alpha' );
		$node->addField( 'Casted', 'Sebastian Schmid', '_wpg', 'Casted', 'Sebastian Schmid' );

		$fields = $node->getFields();
		$this->assertSame( 'Sebastian Schmid', $fields[0]['valueLink'] );
	}

	public function testAddFieldAccumulatesMultipleFields() {
		$node = new GraphNode( 'Team:Alpha' );
		$node->addField( 'Field A', 'value A', '_txt', 'PageA', null );
		$node->addField( 'Field B', 'value B', '_num', 'PageB', null );

		$this->assertCount( 2, $node->getFields() );
	}

}
