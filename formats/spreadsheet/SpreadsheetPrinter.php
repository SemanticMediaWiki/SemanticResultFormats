<?php

namespace SRF;

use ImagePage;
use PhpOffice\PhpSpreadsheet\Calculation\DateTime;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sanitizer;
use SMW\FileExportPrinter;
use SMW\Query\ExportPrinter;
use SMWDataValue;
use SMWQueryResult;
use SMWResultArray;
use Title;

/**
 * @author Kim Eik
 * @since 1.9
 */
class SpreadsheetPrinter extends FileExportPrinter {

	const HEADER_ROW_OFFSET = 1;

	protected $fileFormats = [
		'xlsx' => [
			'writer' => 'Xlsx',
			'mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'extension' => '.xlsx',
		],
		'xls' => [
			'writer' => 'Xls',
			'mimetype' => 'aapplication/vnd.ms-excel',
			'extension' => '.xls',
		],
		'ods' => [
			'writer' => 'Ods',
			'mimetype' => 'application/vnd.oasis.opendocument.spreadsheet',
			'extension' => '.ods',
		],
		'csv' => [
			'writer' => 'Csv',
			'mimetype' => 'text/csv',
			'extension' => '.csv',
		],
	];

	protected $styled = false;
	protected $fileFormat;

	/**
	 * @see ExportPrinter::getMimeType()
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $queryResult
	 *
	 * @return string
	 */
	public function getMimeType( SMWQueryResult $queryResult ) {
		return $this->fileFormat[ 'mimetype' ];
	}

	/**
	 * @see ExportPrinter::getFileName
	 *
	 * @param SMWQueryResult $queryResult
	 *
	 * @return string
	 */
	public function getFileName( SMWQueryResult $queryResult ) {
		return ( $this->params[ 'filename' ] ?: base_convert( uniqid(), 16, 36 ) ) . $this->fileFormat[ 'extension' ];
	}

	/**
	 * @see ExportPrinter::outputAsFile
	 *
	 * @param SMWQueryResult $queryResult
	 * @param array $params
	 */
	public function outputAsFile( SMWQueryResult $queryResult, array $params ) {

		if ( array_key_exists( 'fileformat', $params) && array_key_exists( $params[ 'fileformat' ]->getValue(), $this->fileFormats )) {
			$this->fileFormat = $this->fileFormats[ $params[ 'fileformat' ]->getValue() ];
		} else {
			$this->fileFormat = $this->fileFormats[ 'xlsx' ];
		}

		parent::outputAsFile( $queryResult, $params );
	}

	/**
	 * @param $definitions \ParamProcessor\ParamDefinition[]
	 *
	 * @return array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$definitions[ 'searchlabel' ]->setDefault( wfMessage( 'srf-spreadsheet-link' )->inContentLanguage()->text() );

		$params[ 'templatefile' ] = [
			'type'    => 'string',
			'name'    => 'templatefile',
			'default' => '',
			'message' => 'srf-paramdesc-spreadsheet-templatefile',
		];

		$params[ 'filename' ] = [
			'type'    => 'string',
			'name'    => 'filename',
			'default' => '',
			'message' => 'srf-paramdesc-spreadsheet-filename',
		];

		$params[ 'fileformat' ] = [
			'type'    => 'string',
			'name'    => 'fileformat',
			'default' => 'xlsx',
			'tolower' => true,
			'message' => 'srf-paramdesc-spreadsheet-fileformat',
		];

		return $params;
	}

	/*
	 * Turns the PhpSpreadsheet document object into a string
	 */

	/**
	 * Return serialised results in specified format.
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	protected function getResultText( SMWQueryResult $queryResult, $outputMode ) {

		if ( $outputMode === SMW_OUTPUT_FILE ) {
			return $this->getResultFileContents( $queryResult );
		}

		$this->isHTML = ( $outputMode === SMW_OUTPUT_HTML );
		return $this->getLink( $queryResult, $outputMode )->getText( $outputMode, $this->mLinker );
	}

	/**
	 * @param SMWQueryResult $queryResult
	 *
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	protected function getResultFileContents( SMWQueryResult $queryResult ) {

		$spreadsheet = $this->createSpreadsheet();
		$worksheet = $spreadsheet->getSheet( 0 );

		$this->populateWorksheet( $worksheet, $queryResult );

		//$spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight();

		for ( $i = 0; $i < count( $queryResult->getPrintRequests() ); $i++ ) {
			$worksheet->getColumnDimensionByColumn( $i )->setAutoSize( true );
		}

		$result = $this->getStringFromSpreadsheet( $spreadsheet );
		return $result;
	}

	/**
	 * Creates a new PhpSpreadsheet document and returns it
	 *
	 * @return Spreadsheet
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	protected function createSpreadsheet() {

		$fileTitle = Title::newFromText( $this->params[ 'templatefile' ], NS_FILE );

		if ( $fileTitle !== null && $fileTitle->exists() ) {

			$spreadsheet = $this->createSpreadsheetFromTemplate( $fileTitle );

			$this->styled = true;

		} else {

			$spreadsheet = new Spreadsheet();

		}

		// Set document properties
		$spreadsheet->getProperties()->setCreator( 'SemanticMediaWiki Spreadsheet Export' );

		return $spreadsheet;
	}

	/**
	 * @param $fileTitle
	 *
	 * @return Spreadsheet
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	private function createSpreadsheetFromTemplate( $fileTitle ) {
		$filePage = new ImagePage( $fileTitle, $this );

		$virtualFile = $filePage->getDisplayedFile();
		$virtualFilePath = $virtualFile->getPath();

		$localFile = $virtualFile->getRepo()->getLocalReference( $virtualFilePath );
		$localFilePath = $localFile->getPath();

		return IOFactory::load( $localFilePath );
	}

	/**
	 * @param SMWQueryResult $queryResult
	 * @param $worksheet
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function populateWorksheet( Worksheet $worksheet, SMWQueryResult $queryResult ) {

		$rowIterator = $worksheet->getRowIterator( self::HEADER_ROW_OFFSET );

		//Get headers
		if ( $this->mShowHeaders ) {
			$this->populateHeaderRow( $rowIterator->current(), $queryResult );
			$rowIterator->next();
		}

		while ( $resultRow = $queryResult->getNext() ) {

			//Get data rows
			$this->populateRow( $rowIterator->current(), $resultRow );
			$rowIterator->next();
		}
	}

	/**
	 * Populates the PhpSpreadsheet sheet with the headers from the result query
	 *
	 * @param Row $row
	 * @param SMWQueryResult $queryResult The query result
	 *
	 * @return Row
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function populateHeaderRow( Row $row, SMWQueryResult $queryResult ) {

		$printRequests = $queryResult->getPrintRequests();
		$cellIterator = $row->getCellIterator();

		foreach ( $printRequests as $printRequest ) {

			$cell = $cellIterator->current();

			$cell->setValue( $printRequest->getLabel() );

			$cell->getStyle()
				->getFont()
				->setBold( true );

			$cellIterator->next();

		}

		return $row;
	}

	/**
	 * Populates the PhpSpreadsheet document with the query data
	 *
	 * @param Row $row
	 * @param $resultRow
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function populateRow( Row $row, $resultRow ) {

		$cellIterator = $row->getCellIterator();

		foreach ( $resultRow as $resultField ) {

			$this->populateCell( $cellIterator->current(), $resultField );
			$cellIterator->next();
		}
	}

	/**
	 * @param Cell $cell
	 * @param SMWResultArray $field
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function populateCell( Cell $cell, SMWResultArray $field ) {

		$dataItems = $field->getContent();

		if ( $dataItems === false ) {
			return;
		}

		if ( count( $dataItems ) > 1 ) {

			$values = [];

			while ( $value = $field->getNextText( SMW_OUTPUT_FILE ) ) {
				$values[] = $value;
			}

			$cell->setValueExplicit( join( ', ', $values ), DataType::TYPE_STRING );

		} else {

			$nextDataValue = $field->getNextDataValue();

			if ( $nextDataValue !== false ) {
				$this->populateCellAccordingToType( $cell, $nextDataValue );
			}

		}
	}

	/**
	 * Checks the type of the value, and set's it in the sheet accordingly
	 *
	 * @param Cell $cell
	 * @param SMWDataValue $value
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function populateCellAccordingToType( Cell $cell, SMWDataValue $value ) {

		//NOTE: must check against subclasses before superclasses
		if ( $value instanceof \SMWQuantityValue ) {

			$this->setQuantityDataValue( $cell, $value );

		} elseif ( $value instanceof \SMWNumberValue ) {

			$this->setNumberDataValue( $cell, $value );

		} elseif ( $value instanceof \SMWTimeValue ) {

			$this->setTimeDataValue( $cell, $value );

		} else {

			$this->setStringDataValue( $cell, $value );

		}

	}

	/**
	 * Sets a quantity value at the given col,row location
	 *
	 * @param Cell $cell
	 * @param \SMWQuantityValue $value SMWDataValue  the raw data value object
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function setQuantityDataValue( Cell $cell, \SMWQuantityValue $value ) {

		$type = DataType::TYPE_NUMERIC;
		$unit = $value->getUnit();
		$number = $value->getNumber();

		$cell->setValueExplicit( $number, $type );

		if ( !$this->styled ) {
			$cell->getStyle()
				->getNumberFormat()
				->setFormatCode( '0 "' . $unit . '"' );
		}
	}

	/**
	 * Sets a numeric value at the given col,row location
	 *
	 * @param Cell $cell
	 * @param \SMWNumberValue $value SMWDataValue the raw data value object
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function setNumberDataValue( Cell $cell, \SMWNumberValue $value ) {

		$type = DataType::TYPE_NUMERIC;
		$number = $value->getNumber();

		$cell->setValueExplicit( $number, $type );
	}

	/**
	 * Sets a date/time value at the given col,row location
	 *
	 * @param Cell $cell
	 * @param \SMWTimeValue $value the raw data value object
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function setTimeDataValue( Cell $cell, \SMWTimeValue $value ) {

		$type = DataType::TYPE_NUMERIC;
		$number = DateTime::DATEVALUE( str_replace( 'T', ' ', $value->getISO8601Date() ) );

		$cell->setValueExplicit( $number, $type );

		if ( !$this->styled ) {
			$cell->getStyle()
				->getNumberFormat()
				->setFormatCode( NumberFormat::FORMAT_DATE_DDMMYYYY );
		}
	}

	/**
	 * Sets or appends a string value at the given col,row location
	 *
	 * If there already exists a value at a given col,row location, then
	 * convert the cell to a string and append the data value. Creating
	 * a list of comma separated entries.
	 *
	 * @param Cell $cell
	 * @param $value SMWDataValue the raw data value object
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	protected function setStringDataValue( Cell $cell, SMWDataValue $value ) {

		$type = DataType::TYPE_STRING;
		$text = $value->getWikiValue();
		$text = Sanitizer::decodeCharReferences( $text );
		$text = DataType::checkString( $text );

		$cell->setValueExplicit( $text, $type );
	}

	/**
	 * @param Spreadsheet $spreadsheet
	 *
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	protected function getStringFromSpreadsheet( Spreadsheet $spreadsheet ) {

		$writer = IOFactory::createWriter( $spreadsheet, $this->fileFormat[ 'writer' ] );

		ob_start();
		$writer->save( 'php://output' );
		return ob_get_clean();
	}

}

