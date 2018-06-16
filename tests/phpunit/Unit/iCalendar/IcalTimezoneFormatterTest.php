<?php

namespace SRF\Tests\iCalendar;

use SRF\iCalendar\IcalTimezoneFormatter;
use SMW\Tests\TestEnvironment;

/**
 * @covers \SRF\iCalendar\IcalTimezoneFormatter
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author mwjames
 */
class IcalTimezoneFormatterTest extends \PHPUnit_Framework_TestCase {

	private $stringValidator;

	protected function setUp() {
		parent::setUp();

		$this->stringValidator = TestEnvironment::newValidatorFactory()->newStringValidator();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			IcalTimezoneFormatter::class,
			new IcalTimezoneFormatter()
		);
	}

	/**
	 * @dataProvider transitionsProvider
	 */
	public function testGetTransitions( $tz, $from, $to, $expected ) {

		$instance = new IcalTimezoneFormatter();
		$instance->setLocalTimezones( $tz );
		$instance->calcTransitions(  $from, $to );

		$this->stringValidator->assertThatStringContains(
			$expected,
			$instance->getTransitions()
		);
	}

	public function transitionsProvider() {

		// DTSTART can be different pending the OS hence use .*

		return [[
			'UTC',
			1,
			2,
			"BEGIN:VTIMEZONE\r\nTZID:UTC\r\nBEGIN:STANDARD\r\nDTSTART:.*\r\nTZOFFSETFROM:+0000\r\nTZOFFSETTO:+0000\r\nTZNAME:UTC\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
		], [
			'America/New_York',
			1,
			2,
			"BEGIN:VTIMEZONE\r\nTZID:UTC\r\nBEGIN:STANDARD\r\nDTSTART:.*\r\nTZOFFSETFROM:-0400\r\nTZOFFSETTO:-0400\r\nTZNAME:UTC\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
		]];
	}
}
