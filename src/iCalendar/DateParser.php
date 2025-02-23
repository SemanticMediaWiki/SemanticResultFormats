<?php

namespace SRF\iCalendar;

use SMW\DataValueFactory;
use SMWTimeValue as TimeValue;

/**
 * @license GPL-2.0-or-later
 * @since 3.2
 */
class DateParser {

	/**
	 * Extract a date string formatted for iCalendar from a SMWTimeValue object.
	 *
	 * @since 3.2
	 *
	 * @param TimeValue $dataValue
	 * @param bool $isEnd
	 *
	 * @return string
	 */
	public function parseDate( TimeValue $dataValue, $isEnd = false ) {
		$year = $dataValue->getYear();

		// ISO range is limited to four digits
		if ( ( $year > 9999 ) || ( $year < -9998 ) ) {
			return '';
		}

		$year = number_format( $year, 0, '.', '' );
		$time = str_replace( ':', '', $dataValue->getTimeString( false ) ?? '' );

		// increment by one day, compute date to cover leap years etc.
		if ( ( $time == false ) && ( $isEnd ) ) {
			$dataValue = DataValueFactory::getInstance()->newDataValueByType(
				'_dat',
				$dataValue->getWikiValue() . 'T00:00:00-24:00'
			);
		}

		$month = $dataValue->getMonth();

		if ( strlen( $month ) == 1 ) {
			$month = '0' . $month;
		}

		$day = $dataValue->getDay();

		if ( strlen( $day ) == 1 ) {
			$day = '0' . $day;
		}

		$result = $year . $month . $day;

		if ( $time != false ) {
			$result .= "T$time";
		}

		return $result;
	}

}
