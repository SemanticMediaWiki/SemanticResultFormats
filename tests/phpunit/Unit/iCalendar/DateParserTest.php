<?php

namespace SRF\Tests\iCalendar;

use SRF\iCalendar\DateParser;

/**
 * @covers \SRF\iCalendar\DateParser
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.2
 *
 * @author mwjames
 */
class DateParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			DateParser::class,
			new DateParser()
		);
	}

	public function testParseDate_Year() {

		$timeValue = $this->getMockBuilder( '\SMWTimeValue' )
			->disableOriginalConstructor()
			->getMock();

		$timeValue->expects( $this->any() )
			->method( 'getYear' )
			->will( $this->returnValue( 2000 ) );

		$instance = new DateParser();

		$this->assertSame(
			'20000101',
			$instance->parseDate( $timeValue, true )
		);
	}

	public function testParseDate_Year_Month_Day_Time() {

		$timeValue = $this->getMockBuilder( '\SMWTimeValue' )
			->disableOriginalConstructor()
			->getMock();

		$timeValue->expects( $this->any() )
			->method( 'getYear' )
			->will( $this->returnValue( 2000 ) );

		$timeValue->expects( $this->any() )
			->method( 'getMonth' )
			->will( $this->returnValue( 12 ) );

		$timeValue->expects( $this->any() )
			->method( 'getDay' )
			->will( $this->returnValue( 12 ) );

		$timeValue->expects( $this->any() )
			->method( 'getTimeString' )
			->will( $this->returnValue( '12:01:01' ) );

		$instance = new DateParser();

		$this->assertSame(
			'20001212T120101',
			$instance->parseDate( $timeValue, true )
		);
	}

}
