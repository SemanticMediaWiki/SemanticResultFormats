<?php

/**
 * Create the iCalendar's vTimezone component
 *
 * @author HgO
 *
 * @ingroup SemanticResultFormats
 */
class SRFTimezones {
	private $localTimezones;
	
	private $transitions;
	
	private $dateFrom;
	private $dateTo;
	
	private $offsets;

	public function __construct() {
		global $wgSRFTimezones;
		
		if ( empty( $wgSRFTimezones ) )
			$this->localTimezones = [];
		elseif ( is_array( $wgSRFTimezones ) )
			$this->localTimezones = $wgSRFTimezones;
		else
			$this->localTimezones = [ date_default_timezone_get() ];
	}
	
	/**
	 * @param array $localTimezones A list of local timezones.
	 */
	public function setLocalTimezones( $localTimezones ) {
		$this->localTimezones = $localTimezones;
	}
	
	/**
	 * Set a new range for the generated transitions.
	 * 
	 * @param $from Timestamp from which transitions are generated.
	 * @param $to Timestamp until which transitions are generated.
	 */
	public function setRange( $from, $to ) {
		$this->dateFrom = $from;
		$this->dateTo = $to;
	}
	
	/**
	 * Increase the range of the generated transitions. 
	 *
	 * @param int $from Timestamp from which transitions are generated.
	 * @param int $to Timestamp until which transitions are generated.
	 */
	public function updateRange( $from, $to ) {
		if ( $this->dateFrom === null || $from < $this->dateFrom )
			$this->dateFrom = $from;
		
		if ( $this->dateTo === null || $to > $this->dateTo )
			$this->dateTo = $to;
	}
	
	/**
	 * Calculate transitions for each timezone.
	 */
	public function calcTransitions() {
		$this->transitions = [];
	
		if ( $this->dateFrom === null || $this->dateTo === null )
			return false;
		
		foreach ( $this->localTimezones as $timezone ) {
			try {
				$dateTimezone = new DateTimeZone( $timezone );
			} catch( Exception $e ) {
				continue;
			}
			
			$transitions = $dateTimezone->getTransitions();
			
			$min = 0;
			$max = 1;
			foreach ( $transitions as $i => $transition ) {
				if ( $transition['ts'] < $this->dateFrom ) {
					$min = $i;
					continue;
				}

				if ( $transition['ts'] > $this->dateTo ) {
					$max = $i;
					break;
				}
			}
			
			$this->offsets[$timezone] = $transitions[max( $min-1, 0 )]['offset'];
			$this->transitions[$timezone] = array_slice( $transitions, $min, $max - $min );
		}
	}
	
	/**
	 * Generate the transitions for a given range, for each timezones, in the iCalendar format.
	 */
	public function getIcalForTimezone() {
		if ( $this->transitions === null )
			return false;
		
		$result = '';
		
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
				
				if ( !empty( $transition['abbr'] ) )
					$result .= "TZNAME:{$transition['abbr']}\r\n";
				
				$result .= "END:$dst\r\n";
				
				$tzfrom = $offset;
			}
			
			$result .= "END:VTIMEZONE\r\n";
		}
		
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
