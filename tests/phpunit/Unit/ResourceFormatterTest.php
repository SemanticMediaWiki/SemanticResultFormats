<?php

namespace SRF\Tests;

use SMW\Tests\PHPUnitCompat;
use SRF\ResourceFormatter;

/**
 * @covers \SRF\ResourceFormatter
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author mwjames
 */
class ResourceFormatterTest extends \PHPUnit_Framework_TestCase {

	use PHPUnitCompat;

	public function testSession() {

		$this->assertContains(
			'smw-',
			ResourceFormatter::session()
		);
	}

	public function testPlaceholder() {

		$this->assertInternalType(
			'string',
			ResourceFormatter::placeholder()
		);
	}

}
