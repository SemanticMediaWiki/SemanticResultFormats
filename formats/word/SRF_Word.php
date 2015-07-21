<?php

namespace SRF;

use ImagePage;
use SMW\FileExportPrinter;
use ParamProcessor\Definition\StringParam;
use SMWQueryResult;
use PHPWord;
use Sanitizer;
use Title;

/**
 * @author Wolfgang Fahl 
 * @since 2.1.3 
 */
class SRFWord extends FileExportPrinter {

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
	 * @since 2.1.3
	 *
	 * @param SMWQueryResult $queryResult
	 *
	 * @return string
	 */
	public function getMimeType( SMWQueryResult $queryResult ) {
		return "application/msword";
	}
 
  /**
   * get a file name for the Word file
   *
   * if the filename parameter is not specified a filename is generated
   * from the current time
   *
	 * @since 2.1.3
	 *
	 * @param SMWQueryResult $queryResult
	 *
	 * @return string
   * 
   */
	public function getFileName( SMWQueryResult $queryResult ) {
    $l_filename=$this->params[ 'filename' ] ? $this->params[ 'filename' ] : round( microtime( true ) * 1000 ) . '.doc';
		return $l_filename;
	}

  /**
   * output the given query result with the given params as a file
	 * @since 2.1.3
	 *
	 * @param SMWQueryResult $queryResult
   *
   * @param array $params
	 *
   */ 
	public function outputAsFile( SMWQueryResult $queryResult, array $params ) {
		if ( $this->isPHPWordInstalled() ) {
			parent::outputAsFile( $queryResult, $params );
		} else {
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			echo $this->getResult( $queryResult, $params, SMW_OUTPUT_FILE );
		}
	}

	/**
   * return the parameter definitions
   *  searchlabel, templatefile and filename are possible
   *
	 * @param $definitions \ParamProcessor\ParamDefinition[]
	 *
	 * @return array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$definitions[ 'searchlabel' ]->setDefault( wfMessage( 'srf-word-link' )->inContentLanguage()->text() );

		$params[ 'templatefile' ] = new StringParam( 'string', 'templatefile', '' );
		$params[ 'filename' ] = new StringParam( 'string', 'filename', '' );

		return $params;
	}

	/**
	 * Return serialised results in specified format.
	 * Implemented by subclasses.
	 */
	protected function getResultText( SMWQueryResult $res, $outputMode ) {
		if ( $outputMode == SMW_OUTPUT_FILE ) {
			if ( $this->isPHPWordInstalled() ) {
				$document = $this->createWordDocument();
				//Get data rows
				$this->populateDocumentWithQueryData( $res );
				$result = $this->writeDocumentToString( $document );
			} else {
				$result = wfMessage( 'srf-word-missing-phpexcel' )->parse();
			}
		} else {
			$result = $this->getLink( $res, $outputMode )->getText( $outputMode, $this->mLinker );
			$this->isHTML = ( $outputMode == SMW_OUTPUT_HTML );
		}

		return $result;
	}

	/*
	 * Turns the PHPWord document object into a string
	 */
	protected function writeDocumentToString( $document ) {
		$objWriter = PHPWord_IOFactory::createWriter( $document, 'Word2007' );

		ob_start();
		$objWriter->save('php://output');
		return ob_get_clean();
	}

	/**
	 * Populates the PHPWord document with the query data
	 *
	 * @param $res SMWQueryResult the query result
	 */
	protected function populateDocumentWithQueryData( $res ) {
		while ( $row = $res->getNext() ) {
			$this->rowNum++;
			$this->colNum = 0;
			$this->readRowData($row);
		}
	}

	/**
	 * Creates a new PHPWord document and returns it
	 *
	 * @return PHPWord
	 */
	protected function createWordDocument() {

		$fileTitle = Title::newFromText( $this->params[ 'templatefile' ], NS_FILE );

		if ( $fileTitle !== null && $fileTitle->exists() ) {

			$filePage = new ImagePage( $fileTitle, $this );

			$virtualFile = $filePage->getDisplayedFile();
			$virtualFilePath =  $virtualFile->getPath();

			$localFile= $virtualFile->getRepo()->getLocalReference( $virtualFilePath );
			$localFilePath = $localFile->getPath();
      $objPHPWord = new PHPWord();
      $objPHPWord->loadTemplate($localFilePath);

		} else {

			$objPHPWord = new PHPWord();

		}

		// Set document properties
		$objPHPWord->getProperties()->setCreator( "SemanticMediaWiki PHPWord Export" );

		return $objPHPWord;
	}

	/**
	 * Check for the existence of the extra mainlabel.
	 * @param $label
	 * @return bool
	 */
	private function showLabel( $label ) {
		return !(array_key_exists("mainlabel", $this->params) && $label === $this->params[ "mainlabel" ] . '#');
	}

	protected function readFieldValue( $field ) {
		$valueCount = 0;
		while ( ( $object = $field->getNextDataValue() ) !== false ) {
			if( $valueCount === 0 ) {
				$this->setValueAccordingToType($object);
			} else {
				$this->setOrAppendStringDataValue($object);
			}
			$valueCount++;
		}
	}

	/**
	 * Checks the type of the value, and set's it in the sheet accordingly
	 * @param $object
	 */
	protected function setValueAccordingToType( $object ) {
		//NOTE: must check against subclasses before superclasses
		if( $object instanceof \SMWQuantityValue ) {
			$this->setQuantityDataValue($object);
		} else if( $object instanceof \SMWNumberValue ) {
			$this->setNumberDataValue($object);
		} else if ( $object instanceof \SMWTimeValue ) {
			$this->setTimeDataValue( $object );
		} else {
			$this->setOrAppendStringDataValue($object);
		}
	}

	/**
	 * @param $row
	 */
	protected function readRowData( $row ) {
		foreach ( $row as $field ) {
			if( $this->showLabel($field->getPrintRequest()->getLabel()) ) {
				$this->readFieldValue($field);
				$this->colNum++;
			}
		}
	}

	private function isPHPWordInstalled() {
		return class_exists( "PHPWord" );
	}

}

