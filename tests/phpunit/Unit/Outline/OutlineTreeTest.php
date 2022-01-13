<?php

namespace SRF\Tests\Outline;

use SRF\Outline\OutlineTree;

/**
 * @covers \SRF\Outline\OutlineTree
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class OutlineTreeTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			OutlineTree::class,
			new OutlineTree( [] )
		);
	}

	public function testPropertyAccess() {
		$instance = new OutlineTree();

		$this->assertEmpty(
			$instance->tree
		);

		$this->assertEmpty(
			$instance->items
		);

		$this->assertEquals(
			0,
			$instance->itemCount
		);

		$this->assertEquals(
			0,
			$instance->leafCount
		);
	}

}
