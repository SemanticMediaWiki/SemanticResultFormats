<?php

namespace SRF\Tests\iCalendar;

use SMW\Tests\TestEnvironment;
use SRF\iCalendar\IcalTimezoneFormatter;

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
		$instance->calcTransitions( $from, $to );

		$this->stringValidator->assertThatStringContains(
			$expected,
			$instance->getTransitions()
		);
	}

	public function transitionsProvider() {

		// DTSTART can be different pending the OS hence use .*

		yield [
			'UTC',
			1,
			2,
			"BEGIN:VTIMEZONE\r\nTZID:UTC\r\nBEGIN:STANDARD\r\nDTSTART:.*\r\n" .
			"TZOFFSETFROM:+0000\r\nTZOFFSETTO:+0000\r\nTZNAME:UTC\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
		];

		yield [
			'Asia/Bangkok',
			1,
			2,
			"BEGIN:VTIMEZONE\r\nTZID:Asia/Bangkok\r\nBEGIN:STANDARD\r\nDTSTART:.*\r\n" .
		//  Travis-CI PHP 7 issue, outputs `TZNAME:+07`
		//	"TZOFFSETFROM:+0700\r\nTZOFFSETTO:+0700\r\nTZNAME:ICT\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
			"TZOFFSETFROM:+0700\r\nTZOFFSETTO:+0700\r\nTZNAME:.*\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
		];

		yield [
			'Asia/Tokyo',
			1,
			2,
			"BEGIN:VTIMEZONE\r\nTZID:Asia/Tokyo\r\nBEGIN:STANDARD\r\nDTSTART:.*\r\n" .
			"TZOFFSETFROM:+0900\r\nTZOFFSETTO:+0900\r\nTZNAME:JST\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
		];

		yield [
			'America/New_York',
			1,
			2,
			"BEGIN:VTIMEZONE\r\nTZID:America/New_York\r\nBEGIN:STANDARD\r\nDTSTART:.*\r\n" .
			"TZOFFSETFROM:-0500\r\nTZOFFSETTO:-0500\r\nTZNAME:EST\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
		];
	}

}
