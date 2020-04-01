<?php

namespace SRF\Tests\Outline;

use SRF\Outline\TemplateBuilder;
use SRF\Outline\OutlineTree;

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

	public function testBuildForEmptyTree() {

		$params = [
			'outlineproperties' => [ 'Foo' ],
			'template' => 'Bar',
			'userparam' => '',
			'introtemplate' => 'Intro',
			'outrotemplate' => 'Outro'
		];

		$instance = new TemplateBuilder( $params );

		$this->assertInternalType(
			'string',
			$instance->build( new OutlineTree() )
		);
	}

}
