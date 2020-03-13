<?php

namespace SRF\Tests\vCard;

use SRF\vCard\vCard;

/**
 * @covers \SRF\vCard\vCard
 * @group semantic-result-formats
 *
 * @license GNU GPL v2"
 * @since 3.0
 *
 * @author mwjames
 */
class vCardTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			vCard::class,
			new vCard( '', '', [] )
		);
	}

	public function testEmptyCard() {

		$instance = new vCard( 'http://example.org/Foo', 'Foo', [] );
		$instance->set( 'url', 'http://example.org/Bar' );

		$this->assertSame(
			"BEGIN:VCARD\r\n" .
			"VERSION:3.0\r\n" .
			"N;CHARSET=UTF-8:Foo;;;;\r\n" .
			"FN;CHARSET=UTF-8:Foo\r\n" .
			"CLASS:PUBLIC\r\n" .
			"SOURCE;CHARSET=UTF-8:http://example.org/Foo\r\n" .
			"PRODID:-////Semantic MediaWiki\r\n" .
			"REV:\r\n" .
			"URL:http://example.org/Bar\r\n" .
			"UID:http://example.org/Foo\r\n" .
			"END:VCARD\r\n",
			$instance->text()
		);
	}

}
