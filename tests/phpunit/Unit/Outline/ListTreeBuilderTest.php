<?php

namespace SRF\Tests\Outline;

use SMW\Tests\PHPUnitCompat;
use SRF\Outline\ListTreeBuilder;
use SRF\Outline\OutlineTree;

/**
 * @covers \SRF\Outline\ListTreeBuilder
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class ListTreeBuilderTest extends \PHPUnit_Framework_TestCase {

	use PHPUnitCompat;

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ListTreeBuilder::class,
			new ListTreeBuilder( [] )
		);
	}

	public function testBuildForEmptyTree() {
		$params = [
			'outlineproperties' => [ 'Foo' ]
		];

		$instance = new ListTreeBuilder( $params );

		$this->assertInternalType(
			'string',
			$instance->build( new OutlineTree() )
		);
	}

}
