<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Test\QueryPrinterRegistryTestCase;
use SRF\Graph\GraphNode;


/**
 * Tests for the SRF\Graph class.
 *
 * @file
 * @since 1.8
 *
 * @ingroup SemanticResultFormats
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GraphTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'graph' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRF\Graph\GraphPrinter';
	}

	/**
	 * Testing class GraphNode
	 *
	 * @since 3.0
	 *
	 */
	public function testGraphNode(){

		//can create GraphNode
		$this->assertInstanceOf(
			GraphNode::class,
			$node = new GraphNode( 'Team:Beta' )
		);

		$this->assertEquals( 'Team:Beta', $node->getID() );

		$node->setLabel( "Fossil Power Generation" );
		$this->assertEquals( "Fossil Power Generation", $node->getLabel() );

		$mockParentNode1[] = [
			"predicate" => 'Part Of Team',
			"object"    => 'Alpha Team'
		];

		$node->addParentNode( 'Part Of Team', 'Alpha Team' );
		$this->assertEquals( $mockParentNode1, $node->getParentNode() );

	}

}
