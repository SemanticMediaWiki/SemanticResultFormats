<?php

namespace SRF\Tests\Outline;

use SMW\Tests\PHPUnitCompat;
use SRF\Outline\OutlineTree;
use SRF\Outline\TemplateBuilder;

/**
 * @covers \SRF\Outline\TemplateBuilder
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class TemplateBuilderTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

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

		$this->assertIsString(
			
			$instance->build( new OutlineTree() )
		);
	}

}
