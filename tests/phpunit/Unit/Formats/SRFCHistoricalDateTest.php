<?php

declare( strict_types=1 );

namespace SRF\Tests\Unit\Formats;

use MediaWikiUnitTestCase;
use SRFCHistoricalDate;

/**
 * @covers \SRFCHistoricalDate
 *
 * @group SRF
 */
class SRFCHistoricalDateTest extends MediaWikiUnitTestCase {

	/**
	 * @dataProvider provideLeapGregorianData
	 */
	public function testLeapGregorianReturnsCorrectResult( int $year, bool $expected ): void {
		$date = new class extends SRFCHistoricalDate {
			public static function exposedLeapGregorian( int $year ): bool {
				return self::leapGregorian( $year );
			}
		};
		$this->assertSame( $expected, $date::exposedLeapGregorian( $year ) );
	}

	public static function provideLeapGregorianData(): array {
		return [
			'divisible by 400 is leap' => [ 2000, true ],
			'divisible by 100 not 400 not leap' => [ 1900, false ],
			'divisible by 4 not 100 is leap' => [ 2024, true ],
			'not divisible by 4 not leap' => [ 2023, false ],
			'year 1 not leap' => [ 1, false ],
			'year 4 is leap' => [ 4, true ],
			'year 100 not leap' => [ 100, false ],
			'year 400 is leap' => [ 400, true ],
			'year 1600 is leap' => [ 1600, true ],
			'year 1700 not leap' => [ 1700, false ],
		];
	}

	/**
	 * @dataProvider provideLeapJulianData
	 */
	public function testLeapJulianReturnsCorrectResult( int $year, bool $expected ): void {
		$date = new class extends SRFCHistoricalDate {
			public static function exposedLeapJulian( int $year ): bool {
				return self::leapJulian( $year );
			}
		};
		$this->assertSame( $expected, $date::exposedLeapJulian( $year ) );
	}

	public static function provideLeapJulianData(): array {
		return [
			'year 4 is leap' => [ 4, true ],
			'year 8 is leap' => [ 8, true ],
			'year 100 is leap (Julian has no 100yr exception)' => [ 100, true ],
			'year 1 not leap' => [ 1, false ],
			'year 3 not leap' => [ 3, false ],
			// astronomical year 0 = 1 BC; 1 BC is a Julian leap year
			'year 0 (1 BC) is leap' => [ 0, true ],
			// astronomical year -4 = 5 BC, which is a leap year
			'year -4 (5 BC) is leap' => [ -4, true ],
			// astronomical year -1 = 2 BC, not a leap year
			'year -1 (2 BC) not leap' => [ -1, false ],
			// astronomical year -3 = 4 BC, not a leap year
			'year -3 (4 BC) not leap' => [ -3, false ],
		];
	}

	/**
	 * @dataProvider provideLeapJulGregData
	 */
	public function testLeapJulGregDispatchesCorrectly( int $year, bool $expected ): void {
		$date = new class extends SRFCHistoricalDate {
			public static function exposedLeapJulGreg( int $year ): bool {
				return self::leapJulGreg( $year );
			}
		};
		$this->assertSame( $expected, $date::exposedLeapJulGreg( $year ) );
	}

	public static function provideLeapJulGregData(): array {
		return [
			'year 1200 (Julian) is leap' => [ 1200, true ],
			'year 1300 (Julian) is leap' => [ 1300, true ],
			'year 1581 not leap' => [ 1581, false ],
			'year 1580 (Julian) is leap' => [ 1580, true ],
			'year 1582 (Gregorian) not leap' => [ 1582, false ],
			'year 1600 (Gregorian) is leap' => [ 1600, true ],
			'year 1700 (Gregorian) not leap' => [ 1700, false ],
			'year 2000 (Gregorian) is leap' => [ 2000, true ],
			'year 2024 (Gregorian) is leap' => [ 2024, true ],
		];
	}

	/**
	 * @dataProvider provideDaysInMonthData
	 */
	public function testDaysInMonthReturnsCorrectCount( int $year, int $month, int $expected ): void {
		$this->assertSame( $expected, SRFCHistoricalDate::daysInMonth( $year, $month ) );
	}

	public static function provideDaysInMonthData(): array {
		return [
			'January has 31 days' => [ 2024, 1, 31 ],
			'February in leap year has 29 days' => [ 2024, 2, 29 ],
			'February in non-leap year has 28 days' => [ 2023, 2, 28 ],
			'February in Gregorian century not leap' => [ 1900, 2, 28 ],
			'February in Gregorian 400yr leap' => [ 2000, 2, 29 ],
			'February in Julian year 1300 is leap' => [ 1300, 2, 29 ],
			'March has 31 days' => [ 2024, 3, 31 ],
			'April has 30 days' => [ 2024, 4, 30 ],
			'May has 31 days' => [ 2024, 5, 31 ],
			'June has 30 days' => [ 2024, 6, 30 ],
			'July has 31 days' => [ 2024, 7, 31 ],
			'August has 31 days' => [ 2024, 8, 31 ],
			'September has 30 days' => [ 2024, 9, 30 ],
			'October has 31 days' => [ 2024, 10, 31 ],
			'November has 30 days' => [ 2024, 11, 30 ],
			'December has 31 days' => [ 2024, 12, 31 ],
		];
	}

	/**
	 * @dataProvider provideDayOfWeekData
	 */
	public function testGetDayOfWeekReturnsCorrectWeekday( int $year, int $month, int $day, int $expected ): void {
		$date = new SRFCHistoricalDate();
		$date->create( $year, $month, $day );
		$this->assertSame( $expected, $date->getDayOfWeek() );
	}

	public static function provideDayOfWeekData(): array {
		// getDayOfWeek() returns: 0=Sunday, 1=Monday, ..., 6=Saturday
		// (floor(JD + 1.5) % 7; JD 2451545.0 = J2000.0 = 1 Jan 2000 = Saturday = 6)
		return [
			'2000-01-01 Saturday' => [ 2000, 1, 1, 6 ],
			'2000-01-02 Sunday' => [ 2000, 1, 2, 0 ],
			'2000-01-03 Monday' => [ 2000, 1, 3, 1 ],
			'1970-01-01 Thursday' => [ 1970, 1, 1, 4 ],
			'2024-06-28 Friday' => [ 2024, 6, 28, 5 ],
		];
	}

	public function testCreateUsesJulianBeforeGregorianAdoption(): void {
		// Oct 4, 1582 was the last Julian day; Oct 15, 1582 was the first Gregorian day.
		$julian = new SRFCHistoricalDate();
		$julian->create( 1582, 10, 4 );

		$gregorian = new SRFCHistoricalDate();
		$gregorian->create( 1582, 10, 15 );

		// Oct 15 Gregorian directly follows Oct 4 Julian — they are consecutive days
		$this->assertSame( 1, $gregorian->getDayOfWeek() - $julian->getDayOfWeek() );
	}

	public function testCreateGregorianKnownDate(): void {
		// J2000.0 epoch: Jan 1, 2000 = JD 2451545.0 — well-known astronomical reference
		// getDayOfWeek: floor(2451545.0 + 1.5) % 7 = 6 (Saturday)
		$date = new SRFCHistoricalDate();
		$date->create( 2000, 1, 1 );
		$this->assertSame( 6, $date->getDayOfWeek() );
	}

}
