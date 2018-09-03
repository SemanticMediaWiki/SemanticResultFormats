<?php

namespace SRF\iCalendar;

use SMWDataValueFactory as DataValueFactory;
use SMWExportPrinter as FileExportPrinter;
use SMWQuery as Query;
use SMWQueryProcessor as QueryProcessor;
use SMWQueryResult as QueryResult;
use SMWTimeValue as TimeValue;
use WikiPage;

/**
 * Printer class for iCalendar exports
 *
 * @see https://en.wikipedia.org/wiki/ICalendar
 * @see https://tools.ietf.org/html/rfc5545
 *
 * @license GNU GPL v2+
 * @since 1.5
 *
 * @author Markus Krötzsch
 * @author Denny Vrandecic
 * @author Jeroen De Dauw
 */
class iCalendarFileExportPrinter extends FileExportPrinter {

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var IcalTimezoneFormatter
	 */
	private $icalTimezoneFormatter;

	/**
	 * @see ResultPrinter::getName
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return wfMessage( 'srf_printername_icalendar' )->text();
	}

	/**
	 * @see FileExportPrinter::getMimeType
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getMimeType( QueryResult $queryResult ) {
		return 'text/calendar';
	}

	/**
	 * @see FileExportPrinter::getFileName
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getFileName( QueryResult $queryResult ) {

		if ( $this->title != '' ) {
			return str_replace( ' ', '_', $this->title ) . '.ics';
		}

		return 'iCalendar.ics';
	}

	/**
	 * @see FileExportPrinter::getQueryMode
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getQueryMode( $context ) {
		return ( $context == QueryProcessor::SPECIAL_PAGE ) ? Query::MODE_INSTANCES : Query::MODE_NONE;
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['title'] = [
			'default' => '',
			'message' => 'srf_paramdesc_icalendartitle',
		];

		$params['description'] = [
			'default' => '',
			'message' => 'srf_paramdesc_icalendardescription',
		];

		$params['timezone'] = [
			'default' => '',
			'message' => 'srf-paramdesc-icalendar-timezone',
		];

		return $params;
	}

	/**
	 * @see ResultPrinter::handleParameters
	 *
	 * {@inheritDoc}
	 */
	protected function handleParameters( array $params, $outputMode ) {
		parent::handleParameters( $params, $outputMode );

		$this->title = trim( $params['title'] );
		$this->description = trim( $params['description'] );
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( QueryResult $res, $outputMode ) {

		if ( $outputMode == SMW_OUTPUT_FILE ) {
			return $this->getIcal( $res );
		}

		return $this->getIcalLink( $res, $outputMode );
	}

	/**
	 * Returns the query result in iCal.
	 */
	private function getIcal( QueryResult $res ) {

		$this->icalTimezoneFormatter = new IcalTimezoneFormatter();

		$this->icalTimezoneFormatter->setLocalTimezones(
			isset( $this->params['timezone'] ) ? $this->params['timezone'] : []
		);

		$result = '';

		if ( $this->title == '' ) {
			$this->title = $GLOBALS['wgSitename'];
		}

		$result .= "BEGIN:VCALENDAR\r\n";
		$result .= "PRODID:-//SMW Project//Semantic Result Formats\r\n";
		$result .= "VERSION:2.0\r\n";
		$result .= "METHOD:PUBLISH\r\n";
		$result .= "X-WR-CALNAME:" . $this->title . "\r\n";

		if ( $this->description !== '' ) {
			$result .= "X-WR-CALDESC:" . $this->description . "\r\n";
		}

		$events = '';

		while ( $row = $res->getNext() ) {
			$events .= $this->getIcalForItem( $row );
		}

		$result .= $this->icalTimezoneFormatter->getTransitions();
		$result .= $events;
		$result .= "END:VCALENDAR\r\n";

		return $result;
	}

	/**
	 * Returns html for a link to a query that returns the iCal.
	 */
	private function getIcalLink( QueryResult $res, $outputMode ) {

		if ( $this->getSearchLabel( $outputMode ) ) {
			$label = $this->getSearchLabel( $outputMode );
		} else {
			$label = wfMessage( 'srf_icalendar_link' )->inContentLanguage()->text();
		}

		$link = $res->getQueryLink( $label );
		$link->setParameter( 'icalendar', 'format' );

		if ( $this->title !== '' ) {
			$link->setParameter( $this->title, 'title' );
		}

		if ( $this->description !== '' ) {
			$link->setParameter( $this->description, 'description' );
		}

		if ( array_key_exists( 'limit', $this->params ) ) {
			$link->setParameter( $this->params['limit'], 'limit' );
		} else { // use a reasonable default limit
			$link->setParameter( 20, 'limit' );
		}

		// yes, our code can be viewed as HTML if requested, no more parsing needed
		$this->isHTML = ( $outputMode == SMW_OUTPUT_HTML );

		return $link->getText( $outputMode, $this->mLinker );
	}

	/**
	 * Returns the iCal for a single item.
	 */
	private function getIcalForItem( array $row ) {
		$result = '';

		$subject = $row[0]->getResultSubject(); // get the object
		$subject = DataValueFactory::getInstance()->newDataValueByItem( $subject, null );

		$params = [
			'summary' => $subject->getShortWikiText()
		];

		$from = null;
		$to = null;
		foreach ( $row as /* SMWResultArray */
				  $field ) {
			// later we may add more things like a generic
			// mechanism to add whatever you want :)
			// could include funny things like geo, description etc. though
			$req = $field->getPrintRequest();
			$label = strtolower( $req->getLabel() );

			switch ( $label ) {
				case 'start':
				case 'end':
					if ( $req->getTypeID() == '_dat' ) {
						$dataValue = $field->getNextDataValue();

						if ( $dataValue === false ) {
							unset( $params[$label] );
						} else {
							$params[$label] = $this->parsedate( $dataValue, $label == 'end' );

							$timestamp = strtotime( $params[$label] );
							if ( $from === null || $timestamp < $from ) {
								$from = $timestamp;
							}
							if ( $to === null || $timestamp > $to ) {
								$to = $timestamp;
							}
						}
					}
					break;
				case 'location':
				case 'description':
				case 'summary':
					$value = $field->getNextDataValue();
					if ( $value !== false ) {
						$params[$label] = $value->getShortWikiText();
					}
					break;
			}
		}

		$this->icalTimezoneFormatter->calcTransitions( $from, $to );

		$title = $subject->getTitle();
		$timestamp = WikiPage::factory( $title )->getTimestamp();
		$url = $title->getFullURL();

		$result .= "BEGIN:VEVENT\r\n";
		$result .= "SUMMARY:" . $this->escape( $params['summary'] ) . "\r\n";
		$result .= "URL:$url\r\n";
		$result .= "UID:$url\r\n";

		if ( array_key_exists( 'start', $params ) ) {
			$result .= "DTSTART:" . $params['start'] . "\r\n";
		}

		if ( array_key_exists( 'end', $params ) ) {
			$result .= "DTEND:" . $params['end'] . "\r\n";
		}

		if ( array_key_exists( 'location', $params ) ) {
			$result .= "LOCATION:" . $this->escape( $params['location'] ) . "\r\n";
		}

		if ( array_key_exists( 'description', $params ) ) {
			$result .= "DESCRIPTION:" . $this->escape( $params['description'] ) . "\r\n";
		}

		$t = strtotime( str_replace( 'T', ' ', $timestamp ) );
		$result .= "DTSTAMP:" . date( "Ymd", $t ) . "T" . date( "His", $t ) . "\r\n";
		$result .= "SEQUENCE:" . $title->getLatestRevID() . "\r\n";
		$result .= "END:VEVENT\r\n";

		return $result;
	}

	/**
	 * Extract a date string formatted for iCalendar from a SMWTimeValue object.
	 */
	private function parsedate( TimeValue $dv, $isend = false ) {
		$year = $dv->getYear();

		// ISO range is limited to four digits
		if ( ( $year > 9999 ) || ( $year < -9998 ) ) {
			return '';
		}

		$year = number_format( $year, 0, '.', '' );
		$time = str_replace( ':', '', $dv->getTimeString( false ) );

		// increment by one day, compute date to cover leap years etc.
		if ( ( $time == false ) && ( $isend ) ) {
			$dv = DataValueFactoryg::getInstance()->newDataValueByType(
				'_dat',
				$dv->getWikiValue() . 'T00:00:00-24:00'
			);
		}

		$month = $dv->getMonth();

		if ( strlen( $month ) == 1 ) {
			$month = '0' . $month;
		}

		$day = $dv->getDay();

		if ( strlen( $day ) == 1 ) {
			$day = '0' . $day;
		}

		$result = $year . $month . $day;

		if ( $time != false ) {
			$result .= "T$time";
		}

		return $result;
	}

	/**
	 * Implements esaping of special characters for iCalendar properties of type
	 * TEXT. This is defined in RFC2445 Section 4.3.11.
	 */
	private function escape( $text ) {
		// Note that \\ is a PHP escaped single \ here
		return str_replace( [ "\\", "\n", ";", "," ], [ "\\\\", "\\n", "\\;", "\\," ], $text );
	}

}
