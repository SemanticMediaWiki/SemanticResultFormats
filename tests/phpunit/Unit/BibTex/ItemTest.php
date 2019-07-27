<?php

namespace SRF\Tests\BibTex;

use SRF\BibTex\Item;

/**
 * @covers \SRF\BibTex\Item
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class ItemTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			Item::class,
			new Item()
		);
	}

	/**
	 * @dataProvider fieldsProvider
	 */
	public function testText( $fields, $expected ) {

		$instance = new Item();

		foreach ( $fields as $key => $value ) {
			$instance->set( $key, $value );
		}

		$this->assertEquals(
			$expected,
			$instance->text()
		);
	}

	/**
	 * @dataProvider formatterCallbackFieldsProvider
	 */
	public function testFormatterCallback( $fields, $expected ) {

		$instance = new Item();
		$instance->setFormatterCallback( function( $key, $values ) {
			return implode( '#', $values );
		} );

		foreach ( $fields as $key => $value ) {
			$instance->set( $key, $value );
		}

		$this->assertEquals(
			$expected,
			$instance->text()
		);
	}

	/**
	 * @dataProvider replaceTextProvider
	 */
	public function testReplace( $key, $text, $expected ) {

		$instance = new Item();

		$this->assertEquals(
			$expected,
			$instance->replace( 'uri', $text )
		);
	}

	public function fieldsProvider() {

		yield [
			[ 'foo' => 'test', 'author' => [ 'abc', 'def', '123' ] ],
			"@Book{abc,\r\n  author = \"abc, def, 123\", \r\n}"
		];

		yield [
			[ 'foo' => 'test', 'title' => 'foo bar', 'editor' => [ 'abc', 'def', '123' ] ],
			"@Book{fb,\r\n  editor = \"abc, def, 123\", \r\n  title = \"foo bar\", \r\n}"
		];
	}

	public function formatterCallbackFieldsProvider() {

		yield [
			[ 'foo' => 'test', 'author' => [ 'abc', 'def', '123' ] ],
			"@Book{abc,\r\n  author = \"abc#def#123\", \r\n}"
		];

		yield [
			[ 'foo' => 'test', 'title' => 'foo bar', 'editor' => [ 'abc', 'def', '123' ] ],
			"@Book{fb,\r\n  editor = \"abc#def#123\", \r\n  title = \"foo bar\", \r\n}"
		];
	}

	public function replaceTextProvider() {

		yield [
			'uri',
			'abc-_+ÄäÖ',
			'abcAeaeOe'
		];
	}

}
