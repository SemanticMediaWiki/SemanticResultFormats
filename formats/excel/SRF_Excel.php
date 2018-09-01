<?php

namespace SRF;

use ImagePage;
use PHPExcel;
use PHPExcel_Cell_DataType;
use PHPExcel_IOFactory;
use Sanitizer;
use SMW\FileExportPrinter;
use SMWQueryResult;
use Title;

/**
 * @author Kim Eik
 * @since 1.9
 */
class SRFExcel extends FileExportPrinter {

	const HEADER_ROW_OFFSET = 1;

	/**
	 * @var int
	 */
	protected $rowNum;

	/**
	 * @var int
	 */
	protected $colNum;

	/**
	 * @var \PHPExcel_Worksheet
	 */
	protected $sheet;

	protected $styled = false;

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
	public function getMimeType( SMWQueryResult $queryResult ) {
		return "application/vnd.ms-excel";
	}

	public function getFileName( SMWQueryResult $queryResult ) {

		return $this->params['filename'] ? $this->params['filename'] : round( microtime( true ) * 1000 ) . '.xls';
	}

	public function outputAsFile( SMWQueryResult $queryResult, array $params ) {
		if ( $this->isPHPExcelInstalled() ) {
			parent::outputAsFile( $queryResult, $params );
		} else {
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			echo $this->getResult( $queryResult, $params, SMW_OUTPUT_FILE );
		}
	}

	/**
	 * @param $definitions \ParamProcessor\ParamDefinition[]
	 *
	 * @return array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$definitions['searchlabel']->setDefault( wfMessage( 'srf-excel-link' )->inContentLanguage()->text() );

		$params['templatefile'] = [
			'type' => 'string',
			'name' => 'templatefile',
			'default' => '',
			'message' => 'srf-paramdesc-excel-templatefile',
		];

		$params['filename'] = [
			'type' => 'string',
			'name' => 'filename',
			'default' => '',
			'message' => 'srf-paramdesc-excel-filename',
		];

		return $params;
	}

	/**
	 * Return serialised results in specified format.
	 */
	protected function getResultText( SMWQueryResult $res, $outputMode ) {

		if ( $outputMode === SMW_OUTPUT_FILE ) {
			return $this->getResultFileContents( $res );
		}

		$this->isHTML = ( $outputMode === SMW_OUTPUT_HTML );
		return $this->getLink( $res, $outputMode )->getText( $outputMode, $this->mLinker );
	}

	/*
	 * Turns the PHPExcel document object into a string
	 */
	/**
	 * @param $document
	 *
	 * @return string
	 */
	protected function writeDocumentToString( $document ) {
		$objWriter = PHPExcel_IOFactory::createWriter( $document, 'Excel5' );

		ob_start();
		$objWriter->save( 'php://output' );
		return ob_get_clean();
	}

	/**
	 * Populates the PHPExcel document with the query data
	 *
	 * @param $res SMWQueryResult the query result
	 */
	protected function populateDocumentWithQueryData( $res ) {
		while ( $row = $res->getNext() ) {
			$this->rowNum++;
			$this->colNum = 0;
			$this->readRowData( $row );
		}
	}

	/**
	 * @param SMWQueryResult $res
	 *
	 * @return string
	 */
	protected function getResultFileContents( SMWQueryResult $res ) {
		if ( !$this->isPHPExcelInstalled() ) {
			throw new \RuntimeException( wfMessage( 'srf-excel-missing-phpexcel' )->parse() );
		}

		$document = $this->createExcelDocument();
		$this->sheet = $document->getSheet( 0 );

		$this->rowNum = 0;
		//Get headers
		if ( $this->mShowHeaders ) {
			$this->populateDocumentWithHeaders( $res );
			$this->rowNum++;
		}

		//Get data rows
		$this->populateDocumentWithQueryData( $res );

		$document->getActiveSheet()->getDefaultRowDimension()->setRowHeight();

		$result = $this->writeDocumentToString( $document );
		return $result;
	}

	/**
	 * Sets or appends a string value at the given col,row location
	 *
	 * If there already exists a value at a given col,row location, then
	 * convert the cell to a string and append the data value. Creating
	 * a list of comma separated entries.
	 *
	 * @param $object \SMWDataValue the raw data value object
	 */
	protected function setOrAppendStringDataValue( $object ) {
		$type = PHPExcel_Cell_DataType::TYPE_STRING;
		$value = $object->getWikiValue();
		$value = Sanitizer::decodeCharReferences( $value );
		$value = PHPExcel_Cell_DataType::checkString( $value );

		$cell = $this->sheet->getCellByColumnAndRow( $this->colNum, $this->rowNum );
		$existingValue = $cell->getValue();
		if ( $existingValue ) {
			$value = $existingValue . ', ' . $value;
		}
		$cell->setValueExplicit( $value, $type );
	}

	/**
	 * Sets a numeric value at the given col,row location
	 *
	 * @param $object \SMWDataValue the raw data value object
	 */
	protected function setNumberDataValue( $object ) {
		$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		$value = $object->getDataItem()->getNumber();

		$this->sheet->getCellByColumnAndRow( $this->colNum, $this->rowNum )
			->setValueExplicit( $value, $type );
	}

	/**
	 * Sets a quantity value at the given col,row location
	 *
	 * @param $object \SMWDataValue  the raw data value object
	 */
	protected function setQuantityDataValue( $object ) {
		$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		$unit = $object->getUnit();
		$value = $object->getNumber();

		$this->sheet->getCellByColumnAndRow( $this->colNum, $this->rowNum )
			->setValueExplicit( $value, $type );

		if ( !$this->styled ) {
			$this->sheet->getStyleByColumnAndRow( $this->colNum, $this->rowNum )
				->getNumberFormat()
				->setFormatCode( '0 "' . $unit . '"' );
		}
	}

	/**
	 * Sets a date/time value at the given col,row location
	 *
	 * @param \SMWTimeValue $object the raw data value object
	 */
	protected function setTimeDataValue( \SMWTimeValue $object ) {
		$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
		$value = \PHPExcel_Shared_Date::stringToExcel( str_replace( 'T', ' ', $object->getISO8601Date() ) );

		$this->sheet
			->getCellByColumnAndRow( $this->colNum, $this->rowNum )
			->setValueExplicit( $value, $type );

		if ( !$this->styled ) {
			$this->sheet
				->getStyleByColumnAndRow( $this->colNum, $this->rowNum )
				->getNumberFormat()
				->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY );
		}
	}

	/**
	 * Populates the PHPExcel sheet with the headers from the result query
	 *
	 * @param SMWQueryResult $res The query result
	 */
	protected function populateDocumentWithHeaders( SMWQueryResult $res ) {
		$this->colNum = 0;
		foreach ( $res->getPrintRequests() as $pr ) {
			$header = $pr->getLabel();
			if ( $this->showLabel( $header ) ) {
				$this->sheet->setCellValueByColumnAndRow( $this->colNum, self::HEADER_ROW_OFFSET, $header )
					->getStyleByColumnAndRow( $this->colNum, self::HEADER_ROW_OFFSET )
					->getFont()
					->setBold( true );
				$this->colNum++;
			}
		}
	}

	/**
	 * Creates a new PHPExcel document and returns it
	 *
	 * @return PHPExcel
	 */
	protected function createExcelDocument() {

		$fileTitle = Title::newFromText( $this->params['templatefile'], NS_FILE );

		if ( $fileTitle !== null && $fileTitle->exists() ) {

			$filePage = new ImagePage( $fileTitle, $this );

			$virtualFile = $filePage->getDisplayedFile();
			$virtualFilePath = $virtualFile->getPath();

			$localFile = $virtualFile->getRepo()->getLocalReference( $virtualFilePath );
			$localFilePath = $localFile->getPath();

			$objPHPExcel = PHPExcel_IOFactory::load( $localFilePath );

			$this->styled = true;

		} else {

			$objPHPExcel = new PHPExcel();

		}

		// Set document properties
		$objPHPExcel->getProperties()->setCreator( "SemanticMediaWiki PHPExcel Export" );

		return $objPHPExcel;
	}

	/**
	 * Check for the existence of the extra mainlabel.
	 *
	 * @param $label
	 *
	 * @return bool
	 */
	private function showLabel( $label ) {
		return !( array_key_exists( "mainlabel", $this->params ) && $label === $this->params["mainlabel"] . '#' );
	}

	protected function readFieldValue( $field ) {
		$valueCount = 0;
		while ( ( $object = $field->getNextDataValue() ) !== false ) {
			if ( $valueCount === 0 ) {
				$this->setValueAccordingToType( $object );
			} else {
				$this->setOrAppendStringDataValue( $object );
			}
			$valueCount++;
		}
	}

	/**
	 * Checks the type of the value, and set's it in the sheet accordingly
	 *
	 * @param $object
	 */
	protected function setValueAccordingToType( $object ) {
		//NOTE: must check against subclasses before superclasses
		if ( $object instanceof \SMWQuantityValue ) {
			$this->setQuantityDataValue( $object );
		} else {
			if ( $object instanceof \SMWNumberValue ) {
				$this->setNumberDataValue( $object );
			} else {
				if ( $object instanceof \SMWTimeValue ) {
					$this->setTimeDataValue( $object );
				} else {
					$this->setOrAppendStringDataValue( $object );
				}
			}
		}
	}

	/**
	 * @param $row
	 */
	protected function readRowData( $row ) {
		foreach ( $row as $field ) {
			if ( $this->showLabel( $field->getPrintRequest()->getLabel() ) ) {
				$this->readFieldValue( $field );
				$this->colNum++;
			}
		}
	}

	private function isPHPExcelInstalled() {
		return class_exists( "PHPExcel" );
	}

}

