<?php

namespace SRF\iCalendar;

use SMW\Query\Result\ResultArray;
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
	 * @var DateParser
	 */
	private $dateParser;

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

		if ( $this->title == '' ) {
			$this->title = $GLOBALS['wgSitename'];
		}

		$this->dateParser = new DateParser();

		$this->icalTimezoneFormatter = new IcalTimezoneFormatter();

		$this->icalTimezoneFormatter->setLocalTimezones(
			$this->params['timezone'] ?? []
		);

		$icalFormatter = new IcalFormatter(
			$this->icalTimezoneFormatter
		);

		$icalFormatter->setCalendarName(
			$this->title
		);

		$icalFormatter->setDescription(
			$this->description
		);

		while ( $row = $res->getNext() ) {
			$icalFormatter->addEvent( $this->getEventParams( $row ) );
		}

		return $icalFormatter->getIcal();
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
	 *
	 * @param ResultArray[] $row
	 *
	 * @return []
	 */
	private function getEventParams( array $row ) {

		$result = '';

		$subject = $row[0]->getResultSubject(); // get the object
		$dataValue = DataValueFactory::getInstance()->newDataValueByItem( $subject, null );

		$params = [
			'summary' => $dataValue->getShortWikiText()
		];

		$params['from'] = null;
		$params['to'] = null;

		foreach ( $row as /* SMWResultArray */ $field ) {
			$this->filterField( $field, $params );
		}

		$this->icalTimezoneFormatter->calcTransitions(
			$params['from'],
			$params['to']
		);

		$title = $subject->getTitle();

		$params['url'] = $title->getFullURL();
		$params['timestamp'] = WikiPage::factory( $title )->getTimestamp();
		$params['sequence'] = $title->getLatestRevID();

		return $params;
	}

	private function filterField( $field, &$params ) {

		// later we may add more things like a generic
		// mechanism to add whatever you want :)
		// could include funny things like geo, description etc. though
		$printRequest = $field->getPrintRequest();
		$label = strtolower( $printRequest->getLabel() );

		switch ( $label ) {
			case 'start':
			case 'end':
				if ( $printRequest->getTypeID() == '_dat' ) {
					$dataValue = $field->getNextDataValue();

					if ( $dataValue === false ) {
						unset( $params[$label] );
					} else {
						$params[$label] = $this->dateParser->parseDate(
							$dataValue,
							$label == 'end'
						);

						$timestamp = strtotime( $params[$label] );

						if ( $params['from'] === null || $timestamp < $params['from'] ) {
							$params['from'] = $timestamp;
						}

						if ( $params['to'] === null || $timestamp > $params['to'] ) {
							$params['to'] = $timestamp;
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

}
