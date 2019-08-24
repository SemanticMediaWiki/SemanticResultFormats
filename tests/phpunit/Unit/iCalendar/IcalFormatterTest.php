<?php

namespace SRF\Tests\iCalendar;

use SMW\Tests\TestEnvironment;
use SRF\iCalendar\IcalFormatter;

/**
 * @covers \SRF\iCalendar\IcalFormatter
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.2
 *
 * @author mwjames
 */
class IcalFormatterTest extends \PHPUnit_Framework_TestCase {

	private $stringValidator;
	private $icalTimezoneFormatter;

	protected function setUp() {
		parent::setUp();

		$this->icalTimezoneFormatter = $this->getMockBuilder( '\SRF\iCalendar\IcalTimezoneFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$this->stringValidator = TestEnvironment::newValidatorFactory()->newStringValidator();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			IcalFormatter::class,
			new IcalFormatter( $this->icalTimezoneFormatter )
		);
	}

	/**
	 * @dataProvider paramsProvider
	 */
	public function testGetIcal( $params, $events, $expected ) {

		$instance = new IcalFormatter(
			$this->icalTimezoneFormatter
		);

		$instance->setCalendarName( $params['calendarname'] );
		$instance->setDescription( $params['description'] );

		foreach ( $events as $event ) {
			$instance->addEvent( $event );
		}

		$this->stringValidator->assertThatStringContains(
			$expected,
			$instance->getIcal()
		);
	}

	public function paramsProvider() {

		yield [
			[
				'calendarname' => 'FooCalendar',
				'description'  => 'Calendar description'
			],
			[
				[
					'summary'     => 'summary',
					'url'         => 'http://example.org/Test_1',
					'start'       => '1 Jan 2000',
					'end'         => '2 Jan 2000',
					'location'    => 'FooPlanet',
					'description' => 'Event description',
					'timestamp'   => '1566476717',
					'sequence'    => '123'
				]
			],
			"BEGIN:VCALENDAR\r\n" .
			"PRODID:-//SMW Project//Semantic Result Formats\r\n" .
			"VERSION:2.0\r\n" .
			"METHOD:PUBLISH\r\n" .
			"X-WR-CALNAME:FooCalendar\r\n" .
			"X-WR-CALDESC:Calendar description\r\n" .
			"BEGIN:VEVENT\r\n" .
			"SUMMARY:summary\r\n" .
			"URL:http://example.org/Test_1\r\n" .
			"UID:http://example.org/Test_1\r\n" .
			"DTSTART:1 Jan 2000\r\n" .
			"DTEND:2 Jan 2000\r\nLOCATION:FooPlanet\r\n" .
			"DESCRIPTION:Event description\r\n" .
			"DTSTAMP:19700101T000000\r\n" .
			"SEQUENCE:123\r\n" .
			"END:VEVENT\r\n" .
			"END:VCALENDAR"
		];
	}

}
