<?php

namespace SRF\Filtered\View;

/**
 * File holding the CalendarView class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

use Message;
use SRF\Filtered\ResultItem;

/**
 * The CalendarView class defines the List view.
 *
 * Available parameters for this view:
 *   list view type: list|ul|ol; default: list
 *   list view template: a template rendering a list item
 *   list view introtemplate: a template prepended to the list
 *   list view outrotemplate: a template appended to the list
 *   list view named args: use named args for templates
 *
 * @ingroup SemanticResultFormats
 */
class CalendarView extends View {

	private $start;
	private $end;
	private $title;
	private $titleTemplate;

	/**
	 * @param ResultItem $row
	 *
	 * @return array
	 */
	public function getJsDataForRow( ResultItem $row ) {

		$value = $row->getValue();
		$data = [];
		$wikitext = '';

		foreach ( $value as $valueId => $field ) {

			$printRequest = $field->getPrintRequest();

			$field->reset();
			$datavalue = $field->getNextDataValue();

			if ( $datavalue instanceof \SMWTimeValue &&
				( $printRequest->getLabel() === $this->start || $this->start === null && !array_key_exists(
						'start',
						$data
					) )
			) {
				// found specified column for start date
				// OR no column for start date specified, take first available date value
				$data['start'] = $datavalue->getISO8601Date();
			}

			if ( $datavalue instanceof \SMWTimeValue && $printRequest->getLabel() === $this->end ) {
				// found specified column for end date
				$data['end'] = $datavalue->getISO8601Date();
			}

			if ( $this->titleTemplate === null &&
				( $printRequest->getLabel() === $this->title || $this->title === null && !array_key_exists(
						'title',
						$data
					) )
			) {
				// found specified column for title
				if ( $datavalue !== false ) {
					if ( $datavalue instanceof \SMWWikiPageValue ) {
						$data['url'] = $datavalue->getDataItem()->getTitle()->getLocalURL();
					}
					$data['title'] = $datavalue->getShortHTMLText();
				}
			}

			// only add to title template if requested and if not hidden
			if ( $this->titleTemplate !== null && filter_var(
					$printRequest->getParameter( 'hide' ),
					FILTER_VALIDATE_BOOLEAN
				) === false ) {

				$params = [];
				while ( ( $text = $field->getNextText(
						SMW_OUTPUT_WIKI,
						$this->getQueryPrinter()->getLinker( $valueId === 0 )
					) ) !== false ) {
					$params[] = $text;
				}
				$wikitext .= '|' . ( $valueId + 1 ) . '=' . join( ',', $params );
			}

		}

		// only add to title template if requested and if not hidden
		if ( $this->titleTemplate !== null ) {
//			$wikitext .= "|#=$rownum";
			$data['title'] = trim(
				$this->getQueryPrinter()->getParser()->recursiveTagParse(
					'{{' . $this->titleTemplate . $wikitext . '}}'
				)
			);
			$this->getQueryPrinter()->getParser()->replaceLinkHolders( $data['title'] );
		}

		return $data;
	}

	/**
	 * Transfers the parameters applicable to this view into internal variables.
	 */
	protected function handleParameters() {

		$params = $this->getActualParameters();
		$parser = $this->getQueryPrinter()->getParser();

		// find the hash for the printout containing the start date
		if ( $params['calendar view start'] !== '' ) {
			$this->start = trim( $parser->recursiveTagParse( $params['calendar view start'] ) );
		}

		// find the hash for the printout containing the start date
		if ( $params['calendar view end'] !== '' ) {
			$this->end = trim( $parser->recursiveTagParse( $params['calendar view end'] ) );
		}

		// find the hash for the printout containing the title of the element
		if ( $params['calendar view title'] !== '' ) {
			$this->title = trim( $parser->recursiveTagParse( $params['calendar view title'] ) );
		}

		// find the hash for the printout containing the title of the element
		if ( $params['calendar view title template'] !== '' ) {
			$this->titleTemplate = trim( $parser->recursiveTagParse( $params['calendar view title template'] ) );
		}

//		$this->mTemplate = $params['list view template'];
//		$this->mIntroTemplate = $params['list view introtemplate'];
//		$this->mOutroTemplate = $params['list view outrotemplate'];
//		$this->mNamedArgs = $params['list view named args'];
//
//		if ( $params['headers'] == 'hide' ) {
//			$this->mShowHeaders = SMW_HEADERS_HIDE;
//		} elseif ( $params['headers'] == 'plain' ) {
//			$this->mShowHeaders = SMW_HEADERS_PLAIN;
//		} else {
//			$this->mShowHeaders = SMW_HEADERS_SHOW;
//		}
	}

	/**
	 * A function to describe the allowed parameters of a query for this view.
	 *
	 * @return array of Parameter
	 */
	public static function getParameters() {
		$params = parent::getParameters();

		$params[] = [
			// 'type' => 'string',
			'name' => 'calendar view start',
			'message' => 'srf-paramdesc-filtered-calendar-start',
			'default' => '',
			// 'islist' => false,
		];

		$params[] = [
			// 'type' => 'string',
			'name' => 'calendar view end',
			'message' => 'srf-paramdesc-filtered-calendar-end',
			'default' => '',
			// 'islist' => false,
		];

		$params[] = [
			// 'type' => 'string',
			'name' => 'calendar view title',
			'message' => 'srf-paramdesc-filtered-calendar-title',
			'default' => '',
			// 'islist' => false,
		];

		$params[] = [
			// 'type' => 'string',
			'name' => 'calendar view title template',
			'message' => 'srf-paramdesc-filtered-calendar-title-template',
			'default' => '',
			// 'islist' => false,
		];

		return $params;
	}

	/**
	 * Returns the name of the resource module to load for this view.
	 *
	 * @return string|array
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.calendar-view';
	}

	/**
	 * Returns an array of config data for this filter to be stored in the JS
	 *
	 * @return string[]
	 */
	public function getJsConfig() {
		global $wgAmericanDates;

		return
			$this->getParamHashes( $this->getQueryResults(), $this->getActualParameters() ) +
			[
				'firstDay' => ( $wgAmericanDates ? '0' : Message::newFromKey(
					'srf-filtered-firstdayofweek'
				)->inContentLanguage()->text() ),
				'isRTL' => wfGetLangObj( true )->isRTL(),
			];
	}

	/**
	 * @param ResultItem[] $results
	 * @param string[] $params
	 *
	 * @return string[]
	 */
	private function getParamHashes( $results, $params ) {

		if ( $results === null || count( $results ) < 1 ) {
			return [];
		}

		if ( $params['calendar view title'] !== '' ) {

			$titleLabel = trim(
				$this->getQueryPrinter()->getParser()->recursiveTagParse( $params['calendar view title'] )
			);

			// find the hash for the printout containing the title of the element
			foreach ( reset( $results )->getValue() as $printout ) {

				if ( $printout->getPrintRequest()->getLabel() === $titleLabel ) {
					return [ 'title' => $this->getQueryPrinter()->uniqid( $printout->getPrintRequest()->getHash() ) ];
				}
			}

		} elseif ( $params['mainlabel'] !== '-' ) { // first column not suppressed
			$value = reset( $results )->getValue();
			return [ 'title' => $this->getQueryPrinter()->uniqid( reset( $value )->getPrintRequest()->getHash() ) ];
		}

		return [];
	}

	/**
	 * Returns the label of the selector for this view.
	 *
	 * @return String the selector label
	 */
	public function getSelectorLabel() {
		return Message::newFromKey( 'srf-filtered-selectorlabel-calendar' )->inContentLanguage()->text();
	}

}
