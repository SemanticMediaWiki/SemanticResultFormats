<?php

namespace SRF\Tests\vCard;

use SRF\vCard\Address;

/**
 * @covers \SRF\vCard\Address
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author mwjames
 */
class AddressTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			Address::class,
			new Address( '', [] )
		);
	}

	public function testHasAddress() {

		$instance = new Address( '', [] );

		$this->assertFalse(
			$instance->hasAddress()
		);
	}

	public function testText() {

		$adr = [
			'pobox' => '',
			'ext' => '',
			'street' => '2 Example Avenue',
			'locality' => 'Anytown',
			'region' => 'Foo',
			'code' => '01111',
			'country' => 'Bar'
		];

		$instance = new Address( '', $adr );
		$instance->set( 'region', 'Bar0042' );

		$this->assertSame(
			"ADR;TYPE=WORK;CHARSET=UTF-8:;;2 Example Avenue;Anytown;Bar0042;01111;Bar\r\n",
			$instance->text()
		);
	}

}
