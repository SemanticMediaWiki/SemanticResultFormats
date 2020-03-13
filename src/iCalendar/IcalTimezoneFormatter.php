<?php

namespace SRF\iCalendar;

use DateTimeZone;
use Exception;

/**
 * Create the iCalendar's vTimezone component
 *
 * @license GNU GPL v2+
 * @since 3.0
 *
 * @author HgO
 */
class IcalTimezoneFormatter {

	/**
	 * @var array
	 */
	private $localTimezones = [];

	/**
	 * @var array
	 */
	private $transitions = [];

	/**
	 * @var array
	 */
	private $offsets = [];

	/**
	 * @since 3.0
	 */
	public function __construct() {
		$this->localTimezones = [ $GLOBALS['wgLocaltimezone'] ];
	}

	/**
	 * Set a list of local timezones.
	 *
	 * @since 3.0
	 *
	 * @param array|string $localTimezones
	 */
	public function setLocalTimezones( $localTimezones ) {

		if ( is_array( $localTimezones ) ) {
			$localTimezones = $localTimezones;
		} elseif ( strpos( $localTimezones, ',' ) !== false ) {
			$localTimezones = explode( ',', $localTimezones );
		} elseif ( $localTimezones !== '' ) {
			$localTimezones = [ $localTimezones ];
		} else {
			$localTimezones = [];
		}

		$this->localTimezones = $localTimezones;
	}

	/**
	 * Calculate transitions for each timezone.
	 *
	 * @since 3.0
	 *
	 * @param integer $from Timestamp from which transitions are generated.
	 * @param integer $to Timestamp until which transitions are generated.
	 *
	 * @return boolean
	 */
	public function calcTransitions( $from = null, $to = null ) {

		if ( $this->localTimezones === [] ) {
			return false;
		}

		if ( $from === null || $to === null ) {
			return false;
		}

		foreach ( $this->localTimezones as $timezone ) {
			try {
				$dateTimezone = new DateTimeZone( $timezone );
			}
			catch ( Exception $e ) {
				continue;
			}

			$transitions = $dateTimezone->getTransitions( $from, $to );

			if ( $transitions === false ) {
				continue;
			}

			$min = 0;
			$max = 1;

			foreach ( $transitions as $i => $transition ) {
				if ( $transition['ts'] < $from ) {
					$min = $i;
					continue;
				}

				if ( $transition['ts'] > $to ) {
					$max = $i;
					break;
				}
			}

			$this->offsets[$timezone] = $transitions[max( $min - 1, 0 )]['offset'];
			$this->transitions[$timezone] = array_slice( $transitions, $min, $max - $min );
		}

		return true;
	}

	/**
	 * Generate the transitions for a given range, for each timezones, in the
	 * iCalendar format.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function getTransitions() {

		$result = '';

		if ( $this->transitions === null || $this->transitions === [] ) {
			return $result;
		}

		foreach ( $this->transitions as $timezone => $transitions ) {
			// cf. http://www.kanzaki.com/docs/ical/vtimezone.html
			$result .= "BEGIN:VTIMEZONE\r\n";
			$result .= "TZID:$timezone\r\n";

			$tzfrom = $this->offsets[$timezone] / 3600;
			foreach ( $transitions as $transition ) {
				$dst = ( $transition['isdst'] ) ? "DAYLIGHT" : "STANDARD";
				$result .= "BEGIN:$dst\r\n";

				$start_date = date( 'Ymd\THis', $transition['ts'] );
				$result .= "DTSTART:$start_date\r\n";

				$offset = $transition['offset'] / 3600;

				$offset_from = $this->formatTimezoneOffset( $tzfrom );
				$result .= "TZOFFSETFROM:$offset_from\r\n";

				$offset_to = $this->formatTimezoneOffset( $offset );
				$result .= "TZOFFSETTO:$offset_to\r\n";

				if ( !empty( $transition['abbr'] ) ) {
					$result .= "TZNAME:{$transition['abbr']}\r\n";
				}

				$result .= "END:$dst\r\n";

				$tzfrom = $offset;
			}

			$result .= "END:VTIMEZONE\r\n";
		}

		// Clear the calculation
		$this->transitions = [];

		return $result;
	}

	/**
	 * Format an integer offset to '+hhii', where hh are the hours, and ii the
	 * minutes
	 *
	 * @param int $offset
	 */
	private function formatTimezoneOffset( $offset ) {
		return sprintf( '%s%02d%02d', $offset >= 0 ? '+' : '-', abs( floor( $offset ) ), ( $offset - floor( $offset ) ) * 60 );
	}

}
