<?php

namespace SRF\Tests\vCard;

use SRF\vCard\Tel;

/**
 * @covers \SRF\vCard\Tel
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class TelTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			Tel::class,
			new Tel( '', '' )
		);
	}

	public function testText() {
		$instance = new Tel( '', '+1 781 555 1212' );

		$this->assertSame(
			"TEL;TYPE=WORK:+1 781 555 1212\r\n",
			$instance->text()
		);
	}

}
