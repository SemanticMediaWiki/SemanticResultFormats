<?php

namespace SRF\Tests\iCalendar;

use SRF\iCalendar\DateParser;

/**
 * @covers \SRF\iCalendar\DateParser
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.2
 *
 * @author mwjames
 */
class DateParserTest extends \PHPUnit\Framework\TestCase {

	public function testParseDate_Year() {
		$timeValue = $this->createMock( \SMWTimeValue::class );

		$timeValue->expects( $this->any() )
			->method( 'getYear' )
			->willReturn( 2000 );

		$instance = new DateParser();

		$this->assertSame(
			'20000101',
			$instance->parseDate( $timeValue, true )
		);
	}

	public function testParseDate_Year_Month_Day_Time() {
		$timeValue = $this->createMock( \SMWTimeValue::class );

		$timeValue->expects( $this->any() )
			->method( 'getYear' )
			->willReturn( 2000 );

		$timeValue->expects( $this->any() )
			->method( 'getMonth' )
			->willReturn( 12 );

		$timeValue->expects( $this->any() )
			->method( 'getDay' )
			->willReturn( 12 );

		$timeValue->expects( $this->any() )
			->method( 'getTimeString' )
			->willReturn( '12:01:01' );

		$instance = new DateParser();

		$this->assertSame(
			'20001212T120101',
			$instance->parseDate( $timeValue, true )
		);
	}

}
