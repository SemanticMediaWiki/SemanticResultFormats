<?php
namespace SRF;
/**
 * @author Kim Eik
 * @since 1.9
 */
class SRFExcel extends SMWExportPrinter {

	const HEADER_ROW_OFFSET = 1;

	/**
	 * Some printers do not mainly produce embeddable HTML or Wikitext, but
	 * produce stand-alone files. An example is RSS or iCalendar. This function
	 * returns the mimetype string that this file would have, or FALSE if no
	 * standalone files are produced.
	 *
	 * If this function returns something other than FALSE, then the printer will
	 * not be regarded as a printer that displays in-line results. This is used to
	 * determine if a file output should be generated in Special:Ask.
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $queryResult
	 *
	 * @return string
	 */
	public function getMimeType ( SMWQueryResult $queryResult ) {
		return "application/vnd.ms-excel";
	}

	public function getFileName ( SMWQueryResult $queryResult ) {
		return round( microtime( true ) * 1000 ) . '.xls';
	}

	public function outputAsFile ( SMWQueryResult $queryResult, array $params ) {
		if ( $this->isPHPExcelInstalled() ) {
			parent::outputAsFile( $queryResult, $params );
		} else {
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			echo $this->getResult( $queryResult, $params, SMW_OUTPUT_FILE );
		}
	}

	/**
	 * Return serialised results in specified format.
	 * Implemented by subclasses.
	 */
	protected function getResultText ( SMWQueryResult $res, $outputmode ) {
		if ( $outputmode == SMW_OUTPUT_FILE ) {
			if ( $this->isPHPExcelInstalled() ) {
				$document = $this->createExcelDocument();
				$sheet = $document->getSheet( 0 );

				$rowNum = 0;
				//Get headers
				if ( $this->mShowHeaders ) {
					$this->populateDocumentWithHeaders( $res, $sheet );
					$rowNum++;
				}

				//Get data rows
				$this->populateDocumentWithQueryData( $res, $sheet, $rowNum );

				$result = $this->writeDocumentToString( $document );
			} else {
				$result = wfMessage( 'srf-excel-missing-phpexcel' )->parse();
			}
		} else {
			$result = $this->getLink( $res, $outputmode )->getText( $outputmode, $this->mLinker );
			$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML );
		}

		return $result;
	}

	/*
	 * Turns the PHPExcel document object into a string
	 */
	protected function writeDocumentToString ( $document ) {
		$objWriter = PHPExcel_IOFactory::createWriter( $document, 'Excel5' );

		ob_start();
		$objWriter->save('php://output');
		return ob_get_clean();
	}

	/**
	 * Populates the PHPExcel document with the query data
	 *
	 * @param $res       the query result
	 * @param $sheet     the current phpexcel sheet
	 * @param $rowOffset the offset at which rows should be inserted
	 */
	protected function populateDocumentWithQueryData ( $res, $sheet, $rowOffset ) {
		while ( $row = $res->getNext() ) {
			$rowOffset++;
			$colOffset = 0;
			foreach ( $row as $field ) {

				while ( ( $object = $field->getNextDataValue() ) !== false ) {
					//NOTE: must check against subclasses before superclasses
					if ( $object instanceof SMWQuantityValue ) {
						$this->setQuantityDataValue( $object, $sheet, $colOffset, $rowOffset );
					} else if ( $object instanceof SMWNumberValue ) {
						$this->setNumberDataValue( $object, $sheet, $colOffset, $rowOffset );
					} else {
						$this->setStringDataValue( $object, $sheet, $colOffset, $rowOffset );
					}
				}
				$colOffset++;
			}
		}
	}

	/**
	 * Sets a string value at the given col,row location
	 *
	 * @param $object    the raw data value object
	 * @param $sheet     the current phpexcel sheet
	 * @param $colOffset the col offset to store the data
	 * @param $rowOffset the row offset to store the data
	 */
	protected function setStringDataValue ( $object, $sheet, $colOffset, $rowOffset ) {
		$type = PHPExcel_Cell_DataType::TYPE_STRING;
		$value = $object->getWikiValue();
		$value = Sanitizer::decodeCharReferences( $value );
		$value = PHPExcel_Cell_DataType::checkString( $value );

		$sheet->getCellByColumnAndRow( $colOffset, $rowOffset )
			->setValueExplicit( $value, $type );
	}

	/**
	 * Sets a numeric value at the given col,row location
	 *
	 * @param $object    the raw data value object
	 * @param $sheet     the current phpexcel sheet
	 * @param $colOffset the col offset to store the data
	 * @param $rowOffset the row offset to store the data
	 */
	protected function setNumberDataValue ( $object, $sheet, $colOffset, $rowOffset ) {
		$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		$value = $object->getDataItem()->getNumber();

		$sheet->getCellByColumnAndRow( $colOffset, $rowOffset )
			->setValueExplicit( $value, $type );
	}

	/**
	 * Sets a quantity value at the given col,row location
	 *
	 * @param $object    the raw data value object
	 * @param $sheet     the current phpexcel sheet
	 * @param $colOffset the col offset to store the data
	 * @param $rowOffset the row offset to store the data
	 */
	protected function setQuantityDataValue ( $object, $sheet, $colOffset, $rowOffset ) {
		$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		$unit = $object->getUnit();
		$value = $object->getNumber();

		$sheet->getCellByColumnAndRow( $colOffset, $rowOffset )
			->setValueExplicit( $value, $type );
		$sheet->getStyleByColumnAndRow( $colOffset, $rowOffset )
			->getNumberFormat()
			->setFormatCode( '0 "' . $unit . '"' );
	}

	/**
	 * Populates the PHPExcel sheet with the headers from the result query
	 *
	 * @param $res   the query result
	 * @param $sheet the current phpexcel sheet
	 */
	protected function populateDocumentWithHeaders ( $res, $sheet ) {
		$colOffset = 0;
		foreach ( $res->getPrintRequests() as $pr ) {
			$header = $pr->getLabel();
			$sheet->setCellValueByColumnAndRow( $colOffset, self::HEADER_ROW_OFFSET, $header )
				->getStyleByColumnAndRow( $colOffset, self::HEADER_ROW_OFFSET )
				->getFont()
				->setBold( true );
			$colOffset++;
		}
	}

	/**
	 * Creates a new PHPExcel document and returns it
	 *
	 * @return PHPExcel
	 */
	protected function createExcelDocument () { // Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator( "SemanticMediaWiki PHPExcel Export" );

		return $objPHPExcel;
	}

	public function getParamDefinitions ( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$definitions[ 'searchlabel' ]->setDefault( wfMessage( 'srf-excel-link' )->inContentLanguage()->text() );

		return $params;
	}

	private function isPHPExcelInstalled () {
		return class_exists( "PHPExcel" );
	}
}
