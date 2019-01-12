<?php

namespace SRF\Tests\Outline;

use SRF\Outline\TemplateBuilder;

/**
 * @covers \SRF\Outline\TemplateBuilder
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class TemplateBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			TemplateBuilder::class,
			new TemplateBuilder( [] )
		);
	}

	public function tesBuild() {

		$params = [
			'outlineproperties' => [ 'Foo' ],
			'template' => 'Bar',
			'userparam' => ''
		];

		$instance = new TemplateBuilder( $params );
	}

}
