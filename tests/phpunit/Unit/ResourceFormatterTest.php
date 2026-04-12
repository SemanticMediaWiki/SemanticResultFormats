<?php

namespace SRF\Tests;

use SRF\ResourceFormatter;

/**
 * @covers \SRF\ResourceFormatter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class ResourceFormatterTest extends \PHPUnit\Framework\TestCase {

	public function testSession() {
		$this->assertStringContainsString(
			'smw-',
			ResourceFormatter::session()
		);
	}

	public function testPlaceholder() {
		$this->assertIsString(
			ResourceFormatter::placeholder()
		);
	}

}
