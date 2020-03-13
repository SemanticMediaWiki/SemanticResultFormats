<?php

namespace SRF\iCalendar;

use Exception;

/**
 * @license GNU GPL v2+
 * @since 3.2
 *
 * @author mwjames
 */
class IcalFormatter {

	/**
	 * @var IcalTimezoneFormatter
	 */
	private $icalTimezoneFormatter;

	/**
	 * @var string
	 */
	private $calendarName;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var []
	 */
	private $events = [];

	/**
	 * @since 3.0
	 */
	public function __construct( IcalTimezoneFormatter $icalTimezoneFormatter ) {
		$this->icalTimezoneFormatter = $icalTimezoneFormatter;
	}

	/**
	 * @since 3.2
	 *
	 * @param string $calendarName
	 */
	public function setCalendarName( $calendarName ) {
		$this->calendarName = $calendarName;
	}

	/**
	 * @since 3.2
	 *
	 * @param string $description
	 */
	public function setDescription( $description ) {
		$this->description = $description;
	}

	/**
	 * @since 3.2
	 *
	 * @param array $params
	 */
	public function addEvent( array $params ) {

		$event = '';
		$event .= "BEGIN:VEVENT\r\n";

		if ( isset( $params['summary'] ) ) {
			$event .= "SUMMARY:" . $this->escape( $params['summary'] ) . "\r\n";
		}

		if ( isset( $params['url'] ) ) {
			$event .= "URL:" . $params['url'] . "\r\n";
			$event .= "UID:" . $params['url'] . "\r\n";
		}

		if ( array_key_exists( 'start', $params ) ) {
			$event .= "DTSTART:" . $params['start'] . "\r\n";
		}

		if ( array_key_exists( 'end', $params ) ) {
			$event .= "DTEND:" . $params['end'] . "\r\n";
		}

		if ( array_key_exists( 'location', $params ) ) {
			$event .= "LOCATION:" . $this->escape( $params['location'] ) . "\r\n";
		}

		if ( array_key_exists( 'description', $params ) ) {
			$event .= "DESCRIPTION:" . $this->escape( $params['description'] ) . "\r\n";
		}

		if ( isset( $params['timestamp'] ) ) {
			$t = strtotime( str_replace( 'T', ' ', $params['timestamp'] ) );
			$event .= "DTSTAMP:" . date( "Ymd", $t ) . "T" . date( "His", $t ) . "\r\n";
		}

		if ( isset( $params['sequence'] ) ) {
			$event .= "SEQUENCE:" . $params['sequence'] . "\r\n";
		}

		$event .= "END:VEVENT\r\n";

		$this->events[] = $event;
	}

	/**
	 * @since 3.2
	 *
	 * @return string
	 */
	public function getIcal() {

		$result = '';

		$result .= "BEGIN:VCALENDAR\r\n";
		$result .= "PRODID:-//SMW Project//Semantic Result Formats\r\n";
		$result .= "VERSION:2.0\r\n";
		$result .= "METHOD:PUBLISH\r\n";
		$result .= "X-WR-CALNAME:" . $this->calendarName . "\r\n";

		if ( $this->description !== null ) {
			$result .= "X-WR-CALDESC:" . $this->description . "\r\n";
		}

		$result .= $this->icalTimezoneFormatter->getTransitions();

		foreach ( $this->events as $event ) {
			$result .= $event;
		}

		$result .= "END:VCALENDAR\r\n";
		$this->events = [];

		return $result;
	}

	/**
	 * Implements esaping of special characters for iCalendar properties of type
	 * TEXT. This is defined in RFC2445 Section 4.3.21.
	 */
	private function escape( $text ) {
		// Note that \\ is a PHP escaped single \ here
		return str_replace( [ "\\", "\n", ";", "," ], [ "\\\\", "\\n", "\\;", "\\," ], $text );
	}

}
