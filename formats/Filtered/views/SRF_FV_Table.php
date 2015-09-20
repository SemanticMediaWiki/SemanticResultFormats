<?php

/**
 * File holding the SRF_FV_Table class
 *
 * @author Hans-Juergen Hartl (gesinn.it)
 * @author Alexander Gesinn (gesinn.it)
 * @file
 * @ingroup SemanticResultFormats
 */
use SMW\Query\PrintRequest;

/**
 * The SRF_FV_Table class defines the Table view.
 *
 * Available parameters for this view:
 *   headers: (show)|hide|plain 
 *
 * @ingroup SemanticResultFormats
 */
class SRF_FV_Table extends SRF_Filtered_View {

	private	$mShowHeaders;

	/**
	 * @var string[]
	 */
	private $columnClasses;

	public function __construct($id, &$results, &$params, SRFFiltered &$queryPrinter){
		parent::__construct($id, $results, $params, $queryPrinter);
		$this->params = $params;
	}
	
	/**
	 * Transfers the parameters applicable to this view into internal variables.
	 */
	protected function handleParameters() {

		$params = $this->getActualParameters();

		if ( $params['headers'] == 'hide' ) {
			$this->mShowHeaders = SMW_HEADERS_HIDE;
		} elseif ( $params['headers'] == 'plain' ) {
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
		$result = '';
		$this->columnClasses = array();

		// Table Header		
		if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) { // no headers when headers=hide
			$result .= $this->getTableHeaders();
		}

		// Table Body		
		$result .= $this->getTableRowsHTML();
		
		// Put the <table> tag around the whole thing and optionally add CSS class
		$class = '';
		if ( array_key_exists( 'class', $this->params ) ){
			$class = $this->params['class'];
		}
		$tableAttrs = array( 'class' => $class );
		
		$result = Xml::tags( 'table', $tableAttrs, $result );
		
		return $result;
	}

	private function getTableHeaders() {
		$headers = array();

		/**
		 * Get first QueryResult and assign array members to variables
		 * @var SRF_Filtered_Item $queryResultValue
		 */
		list( , $queryResultValue ) = each( $this->getQueryResults() );

		foreach ( $queryResultValue->getValue() as $field ) {
			$headers[] = $this->getTableHeader( $field->getPrintRequest() );
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
			$this->mShowHeaders == SMW_HEADERS_PLAIN ? null : $this->getQueryPrinter()->getLinker( false, true )
		);

		return Html::rawElement(
			'th',
			array( 'class' => $columnClass ),
			$text === '' ? '&nbsp;' : $text
		);
	}

	private function getColumnClass( PrintRequest $pr ) {
		return str_replace(
			array( ' ', '_' ),
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

	private function getTableRows( $queryResults, $outputmode, $columnClasses) {
		$tableRows = array();
		
		foreach ( $queryResults as $id => $value ) {
			$cells = array();
			$row = $value->getValue();
			
			foreach( $row as $i => $field ) {
				if ( array_key_exists( $i, $columnClasses ) ) {
					$columnClass = $columnClasses[$i];
				} else {
					$columnClass = null;
				}
				
				$cells[] = $this->getCellForPropVals( $field, $outputmode, $columnClass );
			}
			
			$rowClass = 'filtered-table-item';
			$tableRows[] = "<tr id=\"$id\" class=\"$rowClass\">\n\t" . implode( "\n\t", $cells ) . "\n</tr>";
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
	 * 
	 * @return string
	 */
	protected function getCellForPropVals( SMWResultArray $resultArray, $outputmode, $columnClass ) {
		
		$resultArray->reset();
		
		$dataValues = array();
		
		while ( ( $dv = $resultArray->getNextDataValue() ) !== false ) {
			$dataValues[] = $dv;
		}
		
		$attribs = array();
		$content = null;
		
		if ( count( $dataValues ) > 0 ) {
			$sortkey = $dataValues[0]->getDataItem()->getSortKey();
			
			if ( is_numeric( $sortkey ) ) {
				$attribs['data-sort-value'] = $sortkey;
			}
			
			$alignment = trim( $resultArray->getPrintRequest()->getParameter( 'align' ) );
		
			if ( in_array( $alignment, array( 'right', 'left', 'center' ) ) ) {
				$attribs['style'] = "text-align:' . $alignment . ';";
			}
			$attribs['class'] = $columnClass;

			$content = $this->getCellContent(
				$dataValues,
				$outputmode,
				$resultArray->getPrintRequest()->getMode() == SMWPrintRequest::PRINT_THIS
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
	 * @param array $dataValues
	 * @param $outputmode
	 * @param boolean $isSubject
	 * 
	 * @return string
	 */
	protected function getCellContent( array /* of SMWDataValue */ $dataValues, $outputmode, $isSubject ) {
		$values = array();
		
		foreach ( $dataValues as $dv ) {
			$value = $dv->getShortText( $outputmode, $this->getQueryPrinter()->getLinker($isSubject ) );
			$values[] = $value;
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
		
		$params[] = array(
				'name' => 'class',
				'message' => 'smw-paramdesc-table-class',
				'default' => 'wikitable sortable',
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
