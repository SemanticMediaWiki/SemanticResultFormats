<?php

namespace SRF\Tests\vCard;

use SRF\vCard\Email;

/**
 * @covers \SRF\vCard\Email
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author mwjames
 */
class EmailTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			Email::class,
			new Email( '', '' )
		);
	}

	public function testText() {

		$instance = new Email( '', 'johnDoe@example.org' );

		$this->assertSame(
			"EMAIL;TYPE=INTERNET:johnDoe@example.org\r\n",
			$instance->text()
		);
	}

}
