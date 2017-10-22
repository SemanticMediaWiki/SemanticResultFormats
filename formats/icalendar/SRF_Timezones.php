<?php

/**
 * Create the iCalendar's vTimezone component
 *
 * @author HgO
 *
 * @ingroup SemanticResultFormats
 */
class SRFTimezones {
	private $m_tzid;
	
	private $m_from;
	private $m_to;

	public function __construct( $from, $to ) {
		global $wgLocalTimezone;
		
		$this->m_tzid = ($wgLocalTimezone !== null) ? $wgLocalTimezone : date_default_timezone_get();
		
		$this->m_from = $from;
		$this->m_to = $to;
	}
	
	/**
	 * Generate all the timezone's transitions that are needed by the events.
	 *
	 * @param int $from The minimum timestamp in the list of events
	 * @param int $to The maximum timestamp in the list of events
	 */
	public function getIcalForTimezone() {
		if ( $this->m_from === null || $this->m_to === null )
			return false;
			
		try {
			$timezone = new DateTimeZone( $this->m_tzid );
		} catch( Exception $e ) {
			return false;
		}
		
		$transitions = $timezone->getTransitions();
		
		$min = 0;
		$max = 1;
		foreach ( $transitions as $i => $transition ) {
			if ( $transition['ts'] < $this->m_from ) {
				$min = $i;
				continue;
			}

			if ( $transition['ts'] > $this->m_to ) {
				$max = $i;
				break;
			}
		}
		
		// cf. http://www.kanzaki.com/docs/ical/vtimezone.html
		$result = "BEGIN:VTIMEZONE\r\n";
		$result .= "TZID:{$this->m_tzid}\r\n";
		
		$transition = ( $min > 0 ) ? $transitions[$min-1] : $transitions[0];
		$tzfrom = $transition['offset'] / 3600;
		
		foreach ( array_slice( $transitions, $min, $max - $min ) as $transition) {
			$dst = ( $transition['isdst'] ) ? "DAYLIGHT" : "STANDARD";
			$result .= "BEGIN:$dst\r\n";
			
			$start_date = date( 'Ymd\THis', $transition['ts'] );
			$result .= "DTSTART:$start_date\r\n";
			
			$offset = $transition['offset'] / 3600;
			
			$offset_from = $this->formatTimezoneOffset( $tzfrom );
			$result .= "TZOFFSETFROM:$offset_from\r\n";

			$offset_to = $this->formatTimezoneOffset( $offset );
			$result .= "TZOFFSETTO:$offset_to\r\n";
			
			if ( !empty( $transition['abbr'] ) )
				$result .= "TZNAME:{$transition['abbr']}\r\n";
			
			$result .= "END:$dst\r\n";
			
			$tzfrom = $offset;
		}
		
		$result .= "END:VTIMEZONE\r\n";
		
		return $result;
	}
	
	/**
	 * Format an integer offset to '+hhii', where hh are the hours, and ii the minutes
	 *
	 * @param int $offset
	 */
	private function formatTimezoneOffset( $offset ) {
		return sprintf('%s%02d%02d', $offset >= 0 ? '+' : '', floor($offset), ($offset - floor($offset)) * 60);
	}
}
