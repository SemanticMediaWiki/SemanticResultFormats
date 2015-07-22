<?php

namespace SRF;

use ImagePage;
use SMW\FileExportPrinter;
use ParamProcessor\Definition\StringParam;
use SMWQueryResult;
use SMWDataItem;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Sanitizer;
use Title;

/**
 * Semantic Results Format for Microsoft Word 
 * 
 * @author Wolfgang Fahl 
 * @since 2.1.3 
 */
class SRFWord extends FileExportPrinter {

  /**
   * @var int
   */
  protected $rowNum;

  /**
   * @var int
   */
  protected $colNum;

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
    // the filename can be given as a parameter
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
				$result = wfMessage( 'srf-word-missing-phpword' )->parse();
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
    // create a writer
		$objWriter = IOFactory::createWriter( $document, 'Word2007' );

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
    wfDebug("populating Document with Query data\n");
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
    // get the templatefile pageTitle
    $l_templatefile=$this->params[ 'templatefile' ];
    wfDebug( "templatefile=".$l_templatefile."\n");
		$fileTitle = Title::newFromText( $l_templatefile, NS_FILE );
		if ( $fileTitle !== null && $fileTitle->exists() ) {
      wfDebug( "got file title ".$fileTitle->getFullURL()."\n");
			$filePage = new ImagePage( $fileTitle, $this );

			$virtualFile = $filePage->getDisplayedFile();
			$virtualFilePath =  $virtualFile->getPath();

			$localFile= $virtualFile->getRepo()->getLocalReference( $virtualFilePath );
			$localFilePath = $localFile->getPath();
      wfDebug( "template for Word is at ".$localFilePath."\n");
      // see https://github.com/PHPOffice/PHPWord
			$objPHPWord = new \PhpOffice\PhpWord\PhpWord();
      $objPHPWord->loadTemplate($localFilePath);

		} else {
      wfDebug( "creating word object with no template\n");
      // see https://github.com/PHPOffice/PHPWord
			$objPHPWord = new \PhpOffice\PhpWord\PhpWord();

		}
    wfDebug( "setting creator\n");

		// Set document properties
    $l_properties = $objPHPWord -> getDocInfo ();
		$l_properties -> setCreator( "SemanticMediaWiki PHPWord Export" );
    wfDebug( "creator set\n");

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

  /**
   * get the Value for the given dataItem 
   * @param dataItem - the dataItem to read the value from
   * @param plabel  - the label
   */
	protected function readValue(/* SMWDataItem */ $dataItem,$plabel ) {
    switch ($dataItem->getDIType()) {
      case SMWDataItem::TYPE_BLOB:
        wfDebug($plabel."=".$dataItem->getString());
      break;
    }
	}

	/**
   * read data from the given row
	 * @param $row - SMWResultArray
	 */
	protected function readRowData( $row ) {
		foreach ( $row as /* SMWResultArray */ $field ) {
      $l_label=$field->getPrintRequest()->getLabel();
      wfDebug("field label=".$l_label."\n");
			if( $this->showLabel($l_label)) {
        foreach ( $field->getContent() as /* SMWDataItem */ $dataItem ) {
				  $this->readValue($dataItem,$l_label);
				  $this->colNum++;
        }
			}
		}
	}

  /**
   * check whether PHP Word is installed
   */
	private function isPHPWordInstalled() { 
    $l_result=class_exists( "PhpOffice\PhpWord\PhpWord" );
    //wfDebug( "isPhpWordInstalled: ".$l_result."\n");
	 	return $l_result;
	}

}

