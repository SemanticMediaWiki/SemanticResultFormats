<?php

namespace SRF\Tests\Outline;

use SRF\Outline\OutlineItem;

/**
 * @covers \SRF\Outline\OutlineItem
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class OutlineItemTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			OutlineItem::class,
			new OutlineItem( [] )
		);
	}

	public function testPropertyAccess() {

		$instance = new OutlineItem( [ 'Foo' ] );

		$this->assertEquals(
			[ 'Foo' ],
			$instance->row
		);
	}

}
