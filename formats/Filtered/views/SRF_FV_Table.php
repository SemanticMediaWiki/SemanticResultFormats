<?php

/**
 * File holding the SRF_FV_Table class
 *
 * @author Hans-Juergen Hartl (gesinn.it)
 * @author Alexander Gesinn (gesinn.it)
 * @file
 * @ingroup SemanticResultFormats
 */

/**
 * The SRF_FV_Table class defines the Table view.
 *
 * Available parameters for this view:
 *   headers: (show)|hide|plain 
 *   list view named args: use named args for templates
 *
 * @ingroup SemanticResultFormats
 */
class SRF_FV_Table extends SRF_Filtered_View {

	const VIEW_CONTAINER_HTML_TAG = 'div';

	private
		$mFormat,
		$mNamedArgs,
		$mShowHeaders;

	public static function getViewContainerHtmlTag(){
		return self::VIEW_CONTAINER_HTML_TAG;
	}

	public function __construct($id, &$results, &$params, SRFFiltered &$queryPrinter){
		parent::__construct($id, $results, $params, $queryPrinter);
		$this->params = $params;
	}
	
	/**
	 * Transfers the parameters applicable to this view into internal variables.
	 */
	protected function handleParameters() {

		$params = $this->getActualParameters();

		$this->mFormat = 'table';

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
		$outputmode = SMW_OUTPUT_WIKI;
		$columnClasses = array();

		// Table Header		
		if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) { // building headers
			$headers = array();
			$aPrintRequests = array();
			
			list($id, $queryResultValue) = each( $this->getQueryResults());
			
			$row = $queryResultValue->getValue();
			foreach ( $row as $field ) {
				$printRequest = $field->getPrintRequest();
				$aPrintRequests[] = $printRequest;
			}
					
			foreach ( $aPrintRequests as $pr ) {
				$attribs = array();
				// build columnClass from header text
				$columnClass = str_replace( array( ' ', '_' ), '-', strip_tags( $pr->getText( SMW_OUTPUT_WIKI ) ) );
				$attribs['class'] = $columnClass;
				// Also add this to the array of classes, for
				// use in displaying each row.
				$columnClasses[] = $columnClass;
				
				$text = $pr->getText( $outputmode, ( $this->mShowHeaders == SMW_HEADERS_PLAIN ? null :$this->getQueryPrinter()->getLinker(false,true) ) );
				
				$headers[] = Html::rawElement(
						'th',
						$attribs,
						$text === '' ? '&nbsp;' : $text
				);
			}
				
			$headers = '<tr>' . implode( "\n", $headers ) . '</tr>';
			$headers = "\n$headers\n";
			$result .= $headers;
		}

		// Table Body		
		$tableRows = $this->getTableRows( $this->getQueryResults(), $outputmode, $columnClasses);		
		$tableRows = implode( "\n", $tableRows );
		$result .= $tableRows;
		
		// Put the <table> tag around the whole thing
		$class = '';
		if(array_key_exists('class', $this->params)){
			$class = $this->params['class'];
		}
		$tableAttrs = array( 'class' => $class );
		
		$result = Xml::tags( 'table', $tableAttrs, $result );
		
		$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML ); // yes, our code can be viewed as HTML if requested, no more parsing needed
		
		
		return $result;
	}

	/**
	 * Get all table rows
	 * 
	 * @return string
	 */
	protected function getTableRows( $queryResults, $outputmode, $columnClasses) {
		
		$tableRows = array();
		
		foreach ( $queryResults as $id => $value ) {
			
			$cells = array();
			$row = $value->getValue();
			
			foreach($row as $i => $field){
				
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
				'default' => 'sortable wikitable smwtable',
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
		Message::newFromKey( 'srf-filtered-selectorlabel-table' )->inContentLanguage()->text();
	}

}
