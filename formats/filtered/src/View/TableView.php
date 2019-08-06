<?php

namespace SRF\Filtered\View;

/**
 * File holding the TableView class
 *
 * @author Hans-Juergen Hartl (gesinn.it)
 * @author Alexander Gesinn (gesinn.it)
 * @file
 * @ingroup SemanticResultFormats
 */

use Html;
use Message;
use SMW\Query\PrintRequest;
use SMWPrintRequest;
use SMWResultArray;
use SRF\Filtered\ResultItem;
use Xml;

/**
 * The TableView class defines the Table view.
 *
 * Available parameters for this view:
 *   headers: (show)|hide|plain
 *
 * @ingroup SemanticResultFormats
 */
class TableView extends View {

	private $mShowHeaders;

	/**
	 * @var string[]
	 */
	private $columnClasses;

	/**
	 * Transfers the parameters applicable to this view into internal variables.
	 */
	protected function handleParameters() {

		$params = $this->getActualParameters();

		if ( $params['headers'] === 'hide' ) {
			$this->mShowHeaders = SMW_HEADERS_HIDE;
		} elseif ( $params['headers'] === 'plain' ) {
			$this->mShowHeaders = SMW_HEADERS_PLAIN;
		} else {
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

		// Initialise more values
		$resultText = '';
		$this->columnClasses = [];

		// Table Header		
		if ( $this->mShowHeaders !== SMW_HEADERS_HIDE ) { // no headers when headers=hide
			$resultText .= $this->getTableHeaders();
		}

		// Table Body		
		$resultText .= $this->getTableRowsHTML();

		// Put the <table> tag around the whole thing and optionally add CSS class
		$tableAttrs = null;
		if ( array_key_exists( 'table view class', $this->getActualParameters() ) ) {
			$tableAttrs = [ 'class' => $this->getActualParameters()['table view class'] ];
		}

		$resultText = Xml::tags( 'table', $tableAttrs, $resultText );

		return $resultText;
	}

	private function getTableHeaders() {
		$headers = [];

		$queryResults = $this->getQueryResults();
		$queryResultValue = reset( $queryResults );

		if ( !is_a( $queryResultValue, ResultItem::class ) ) {
			return '';
		}

		foreach ( $queryResultValue->getValue() as $field ) {
			$printRequest = $field->getPrintRequest();
			if ( filter_var( $printRequest->getParameter( 'hide' ), FILTER_VALIDATE_BOOLEAN ) === false ) {
				$headers[] = $this->getTableHeader( $printRequest );
			}
		}

		return "\n<tr>\n" . implode( "\n", $headers ) . "\n</tr>\n";
	}

	private function getTableHeader( PrintRequest $pr ) {
		// build class attributes from header text assigned to each column's cell
		$columnClass = $this->getColumnClass( $pr );

		// Also add this to the array of classes, for
		// use in displaying each row.
		$this->columnClasses[] = $columnClass;

		// get header text (and link to property)
		$text = $pr->getText(
			SMW_OUTPUT_WIKI,
			$this->mShowHeaders === SMW_HEADERS_PLAIN ? null : $this->getQueryPrinter()->getLinker( false, true )
		);

		return Html::rawElement(
			'th',
			[ 'class' => $columnClass ],
			$text === '' ? '&nbsp;' : $text
		);
	}

	private function getColumnClass( PrintRequest $pr ) {
		return str_replace(
			[ ' ', '_' ],
			'-',
			strip_tags( $pr->getText( SMW_OUTPUT_WIKI ) )
		);
	}

	private function getTableRowsHTML() {
		return implode(
			"\n",
			$this->getTableRows(
				$this->getQueryResults(),
				SMW_OUTPUT_WIKI,
				$this->columnClasses
			)
		);
	}

	/**
	 * @param ResultItem[] $queryResults
	 * @param int $outputmode
	 * @param string[] $columnClasses
	 *
	 * @return array
	 */
	private function getTableRows( $queryResults, $outputmode, $columnClasses ) {
		$tableRows = [];

		foreach ( $queryResults as $id => $value ) {
			$cells = [];
			$row = $value->getValue();

			foreach ( $row as $fieldId => $field ) {

				if ( filter_var(
						$field->getPrintRequest()->getParameter( 'hide' ),
						FILTER_VALIDATE_BOOLEAN
					) === false ) {

					if ( array_key_exists( $fieldId, $columnClasses ) ) {
						$columnClass = $columnClasses[$fieldId];
					} else {
						$columnClass = null;
					}

					$cells[] = $this->getCellForPropVals( $field, $outputmode, $columnClass );
				}
			}

			$rowClass = 'filtered-table-item';
			$tableRows[] = "<tr class=\"$rowClass $id\">\n\t" . implode( "\n\t", $cells ) . "\n</tr>";
		}

		return $tableRows;

	}

	/**
	 * Gets a table cell for all values of a property of a subject.
	 *
	 * @since 1.6.1
	 *
	 * @param SMWResultArray $resultArray
	 * @param $outputmode
	 * @param string | null $columnClass
	 *
	 * @return string
	 */
	protected function getCellForPropVals( SMWResultArray $resultArray, $outputmode, $columnClass ) {

		$resultArray->reset();

		$dataValues = [];

		while ( ( $dataValue = $resultArray->getNextDataValue() ) !== false ) {
			$dataValues[] = $dataValue;
		}

		$attribs = [];
		$content = null;

		if ( count( $dataValues ) > 0 ) {
			$sortkey = $dataValues[0]->getDataItem()->getSortKey();

			if ( is_numeric( $sortkey ) ) {
				$attribs['data-sort-value'] = $sortkey;
			}

			$alignment = trim( $resultArray->getPrintRequest()->getParameter( 'align' ) );

			if ( in_array( $alignment, [ 'right', 'left', 'center' ] ) ) {
				$attribs['style'] = "text-align: $alignment;";
			}

			if ( $columnClass !== null ) {
				$attribs['class'] = $columnClass;
			}

			$content = $this->getCellContent(
				$dataValues,
				$outputmode,
				$resultArray->getPrintRequest()->getMode() === SMWPrintRequest::PRINT_THIS
			);
		}

		return Html::rawElement(
			'td',
			$attribs,
			$content
		);
	}

	/**
	 * Gets the contents for a table cell for all values of a property of a subject.
	 *
	 * @since 1.6.1
	 *
	 * @param \SMWDataValue[] $dataValues
	 * @param $outputmode
	 * @param boolean $isSubject
	 *
	 * @return string
	 */
	protected function getCellContent( array $dataValues, $outputmode, $isSubject ) {
		$values = [];

		foreach ( $dataValues as $dataValue ) {
			$values[] = $dataValue->getShortText( $outputmode, $this->getQueryPrinter()->getLinker( $isSubject ) );
		}

		return implode( '<br />', $values );
	}

	/**
	 * A function to describe the allowed parameters of a query for this view.
	 *
	 * @return array of Parameter
	 */
	public static function getParameters() {
		$params = parent::getParameters();

		$params[] = [
			'name' => 'table view class',
			'message' => 'smw-paramdesc-table-class',
			'default' => 'wikitable sortable',
		];

		return $params;
	}

	/**
	 * Returns the label of the selector for this view.
	 *
	 * @return String the selector label
	 */
	public function getSelectorLabel() {
		return Message::newFromKey( 'srf-filtered-selectorlabel-table' )->inContentLanguage()->text();
	}

}
