<?php

/**
 * File holding the SRF_FV_Table class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

/**
 * The SRF_FV_Table class defines the Table view.
 *
 * Available parameters for this view:
 *   table view class: use named args for templates
 *
 * @ingroup SemanticResultFormats
 */
class SRF_FV_Table extends SRF_Filtered_View {

	private $mClass, $mShowHeaders;

	/**
	 * Transfers the parameters applicable to this view into internal variables.
	 */
	protected function handleParameters() {

		$params = $this->getActualParameters();
		$this->mClass = $params['table view class'];

		switch ( $params[ 'headers' ] ) {
			case 'hide':
				$this->mShowHeaders = SMW_HEADERS_HIDE;
				break;
			case 'plain' :
				$this->mShowHeaders = SMW_HEADERS_PLAIN;
				break;
			default:
				$this->mShowHeaders = SMW_HEADERS_SHOW;
		}
	}

	/**
	 * Returns the wiki text that is to be included for this view.
	 *
	 * @return string
	 */
	public function getResultText() {

		$this->handleParameters();
		$sep = ', ';

		$result = "<table class='$this->mClass'>";

		$queryResults = $this->getQueryResults();

		//TODO: insert table heading
		if ( $this->mShowHeaders !== SMW_HEADERS_HIDE && count( $queryResults ) > 0 ) {

			$headerTexts = array();

			$value = reset( $queryResults );
			$row = $value->getValue();
			foreach ( $row as $field ) {
				$headerTexts[] = '<th>' . $field->getPrintRequest()->getText( SMW_OUTPUT_WIKI, ( $this->mShowHeaders === SMW_HEADERS_PLAIN ? null:$this->getQueryPrinter()->getLinker( true, true ) ) ) . '</th>';
			}

			$result .= "<tr>\n" . implode("", $headerTexts ) . "</tr>";

		}

		// Now print each row
		foreach ( $queryResults as $id => $value ) {

			$row = $value->getValue();

			$result .= $this->printRow( $row, $id, $sep );
		}

		$result .= '</table>';

		return $result;
	}

	/**
	 * Prints one row of a table view.
	 *
	 * @param SMWResultArray[] $row
	 * @param string           $id
	 * @param string           $sep
	 *
	 * @return string
	 */
	protected function printRow( $row, $id, $sep ) {

		$result = "\t<tr class='filtered-table-item " . $id . "' id='$id' >";

		foreach ( $row as $field ) {

			$printrequest = $field->getPrintRequest();

			// only print value if not hidden
			if ( $printrequest->getParameter( 'hide' ) === false ) {

				$field->reset();
				$fieldTexts = array();
				$first_col = true;
				while ( ( $text = $field->getNextText( SMW_OUTPUT_WIKI, $this->getQueryPrinter()->getLinker( $first_col ) ) ) !== false ) {
					$fieldTexts[] = $text; // actual output value
				}

				$result .= '<td><div>' . implode( $sep, $fieldTexts ) . '</div></td>';
			}
		}

		$result .= "</tr>\n";

		return $result;
	}

	/**
	 * A function to describe the allowed parameters of a query for this view.
	 *
	 * @return array of Parameter
	 */
	public static function getParameters() {
		$params = parent::getParameters();

		$params[] = array(
			// 'type' => 'string',
			'name' => 'table view class',
			'message' => 'srf-paramdesc-filtered-table-class',
			'default' => 'wikitable',
			// 'istable' => false,
		);

		return $params;
	}

	/**
	 * Returns the name of the resource module to load for this view.
	 *
	 * @return string|array
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.table-view';
	}

	/**
	 * Returns the label of the selector for this view.
	 * @return String the selector label
	 */
	public function getSelectorLabel() {
		return Message::newFromKey( 'srf-filtered-selectorlabel-table' )->inContentLanguage()->text();
	}

}
