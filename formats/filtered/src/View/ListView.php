<?php

namespace SRF\Filtered\View;

/**
 * File holding the ListView class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

use Message;

/**
 * The ListView class defines the List view.
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
class ListView extends View {

	private $mFormat, $mTemplate, $mIntroTemplate, $mOutroTemplate, $mNamedArgs, $mShowHeaders;

	/**
	 * Transfers the parameters applicable to this view into internal variables.
	 */
	protected function handleParameters() {

		$params = $this->getActualParameters();

		$this->mFormat = $params['list view type'];
		$this->mTemplate = $params['list view template'];
		$this->mIntroTemplate = $params['list view introtemplate'];
		$this->mOutroTemplate = $params['list view outrotemplate'];
		$this->mNamedArgs = $params['list view named args'];

		$this->mShowHeaders = $params['headers'];
	}

	public function getJsConfig() {
		$this->handleParameters();
		return [
			'format' => $this->mFormat,
			'named args' => $this->mNamedArgs,
			'show headers' => $this->mShowHeaders
		];
	}

	/**
	 * Returns the wiki text that is to be included for this view.
	 *
	 * @return string
	 */
	public function getResultText() {

		$this->handleParameters();

		// Determine mark-up strings used around list items:
		if ( ( $this->mFormat == 'ul' ) || ( $this->mFormat == 'ol' ) ) {
			$header = "<" . $this->mFormat . ">\n";
			$footer = "</" . $this->mFormat . ">\n";
			$rowstart = "\t<li class='filtered-list-item ";
			$rowend = "</li>\n";
			$listsep = ', ';
		} else { // "list" format
			$header = '';
			$footer = '';
			$rowstart = "\t<div class='filtered-list-item ";
			$rowend = "</div>\n";
			$listsep = ', ';
		}

		// Initialise more values
		$result = $header;

		if ( $this->mIntroTemplate !== '' ) {
			$result .= "{{" . $this->mIntroTemplate . "}}";
		}

		// Now print each row
		$rownum = -1;

		foreach ( $this->getQueryResults() as $id => $value ) {
			$row = $value->getValue();

			$this->printRow( $row, $rownum, $rowstart . $id . '\'>', $rowend, $result, $listsep );
		}

		if ( $this->mOutroTemplate !== '' ) {
			$result .= "{{" . $this->mOutroTemplate . "}}";
		}

		// Print footer
		if ( $footer !== '' ) {
			$result .= $footer;
		}

		return $result;
	}

	/**
	 * Prints one row of a list view.
	 *
	 * @param \SMWResultArray[] $row
	 * @param $rownum
	 * @param $rowstart
	 * @param $rowend
	 * @param $result
	 * @param $listsep
	 */
	protected function printRow( $row, &$rownum, $rowstart, $rowend, &$result, $listsep ) {

		$rownum++;

		$result .= $rowstart;

		if ( $this->mTemplate !== '' ) { // build template code
			$this->getQueryPrinter()->hasTemplates( true );

			// $wikitext = ( $this->mUserParam ) ? "|userparam=$this->mUserParam" : '';
			$wikitext = '';

			foreach ( $row as $fieldNumber => $field ) {

				$printrequest = $field->getPrintRequest();

				// only print value if not hidden
				if ( filter_var( $printrequest->getParameter( 'hide' ), FILTER_VALIDATE_BOOLEAN ) === false ) {

					$wikitext .= '|' . ( $this->mNamedArgs ? '?' . $printrequest->getLabel() : $fieldNumber + 1 ) . '=';
					$isFirstValue = true;

					$field->reset();
					while ( ( $text = $field->getNextText(
							SMW_OUTPUT_WIKI,
							$this->getQueryPrinter()->getLinker( $fieldNumber == 0 )
						) ) !== false ) {
						if ( $isFirstValue ) {
							$isFirstValue = false;
						} else {
							$wikitext .= ', ';
						}
						$wikitext .= $text;
					}
				}
			}

			$wikitext .= "|#=$rownum";
			$result .= '{{' . $this->mTemplate . $wikitext . '}}';

		} else {  // build simple list
			$firstCol = true;
			$foundValues = false; // has anything but the first column been printed?

			foreach ( $row as $field ) {
				$isFirstValue = true;

				$printrequest = $field->getPrintRequest();

				$field->reset();
				while ( ( $text = $field->getNextText(
						SMW_OUTPUT_WIKI,
						$this->getQueryPrinter()->getLinker( $firstCol )
					) ) !== false ) {

					// only print value if not hidden
					if ( filter_var( $printrequest->getParameter( 'hide' ), FILTER_VALIDATE_BOOLEAN ) === false ) {

						if ( !$firstCol && !$foundValues ) { // first values after first column
							$result .= ' (';
							$foundValues = true;
						} elseif ( $foundValues || !$isFirstValue ) {
							// any value after '(' or non-first values on first column
							$result .= "$listsep ";
						}

						if ( $isFirstValue ) { // first value in any column, print header
							$isFirstValue = false;

							if ( ( $this->mShowHeaders != SMW_HEADERS_HIDE ) && ( $field->getPrintRequest()->getLabel(
									) !== '' ) ) {
								$result .= $field->getPrintRequest()->getText(
										SMW_OUTPUT_WIKI,
										( $this->mShowHeaders == SMW_HEADERS_PLAIN ? null : $this->getQueryPrinter(
										)->getLinker( true, true ) )
									) . ' ';
							}
						}

						$result .= $text; // actual output value
					}
				}

				$firstCol = false;
			}

			if ( $foundValues ) {
				$result .= ')';
			}
		}

		$result .= $rowend;
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
			'name' => 'list view type',
			'message' => 'srf-paramdesc-filtered-list-type',
			'default' => 'list',
			// 'islist' => false,
		];

		$params[] = [
			// 'type' => 'string',
			'name' => 'list view template',
			'message' => 'srf-paramdesc-filtered-list-template',
			'default' => '',
			// 'islist' => false,
		];

		$params[] = [
			'type' => 'boolean',
			'name' => 'list view named args',
			'message' => 'srf-paramdesc-filtered-list-named-args',
			'default' => false,
			// 'islist' => false,
		];

		$params[] = [
			//'type' => 'string',
			'name' => 'list view introtemplate',
			'message' => 'srf-paramdesc-filtered-list-introtemplate',
			'default' => '',
			// 'islist' => false,
		];

		$params[] = [
			//'type' => 'string',
			'name' => 'list view outrotemplate',
			'message' => 'srf-paramdesc-filtered-list-outrotemplate',
			'default' => '',
			// 'islist' => false,
		];

		return $params;
	}

	/**
	 * Returns the label of the selector for this view.
	 *
	 * @return String the selector label
	 */
	public function getSelectorLabel() {
		return Message::newFromKey( 'srf-filtered-selectorlabel-list' )->inContentLanguage()->text();
	}

}
