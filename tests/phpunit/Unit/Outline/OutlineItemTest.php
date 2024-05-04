<?php

namespace SRF\Tests\Outline;

use SRF\Outline\OutlineItem;

/**
 * @covers \SRF\Outline\OutlineItem
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class OutlineItemTest extends \PHPUnit\Framework\TestCase {

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
