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
 * @licence GNU GPL v2+
 * 
 * @author Wolfgang Fahl < wf@bitplan.com >
 * @since 2.1.3 
 */
class SRFWord extends FileExportPrinter {
  /**
   * set to true for debug output
   */
  protected $debug=true;

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
    $l_filename=$this->params[ 'filename' ] ? $this->params[ 'filename' ] : round( microtime( true ) * 1000 ) . '.docx';
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
	 * @since 2.1.3
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
   * @param document - the document
	 */
	protected function writeDocumentToString( $document ) {
    global $wgTmpDirectory;
    $l_tmpname=$wgTmpDirectory."/tmp.docx";
    if ($this->debug) 
      wfDebug("saving template result to tmp file ".$l_tmpname."\n");
    $document->saveAs($l_tmpname);
    $l_document=IOFactory::load($l_tmpname);
    // create a writer
		$objWriter = IOFactory::createWriter( $l_document, 'Word2007' );
    // write to output pipe to allow downloading the resulting document
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
    if ($this->debug)
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
    if ($this->debug)
      wfDebug( "templatefile=".$l_templatefile."\n");
		$fileTitle = Title::newFromText( $l_templatefile, NS_FILE );
		if ( $fileTitle !== null && $fileTitle->exists() ) {
      if ($this->debug)
        wfDebug( "got file title ".$fileTitle->getFullURL()."\n");
			$filePage = new ImagePage( $fileTitle, $this );

			$virtualFile = $filePage->getDisplayedFile();
			$virtualFilePath =  $virtualFile->getPath();

			$localFile= $virtualFile->getRepo()->getLocalReference( $virtualFilePath );
			$localFilePath = $localFile->getPath();
      if ($this->debug)
        wfDebug( "template for Word is at ".$localFilePath."\n");
      // see https://github.com/PHPOffice/PHPWord
			$this->objPHPWord = new \PhpOffice\PhpWord\PhpWord();
      // the document to be saved is based on the template
      $this->document =  $this->objPHPWord->loadTemplate($localFilePath);

		} else {
      if ($this->debug)
        wfDebug( "creating word object with no template\n");
      // see https://github.com/PHPOffice/PHPWord
			$this->objPHPWord = new \PhpOffice\PhpWord\PhpWord();
			$this->document = $this->objPHPWord; 

		}
    if ($this->debug)
      wfDebug( "setting creator\n");

		// Set document properties
    $l_properties = $this->objPHPWord -> getDocInfo ();
		$l_properties -> setCreator( "SemanticMediaWiki PHPWord Export" );
    if ($this->debug)
      wfDebug( "creator set\n");

		return $this->document;
	}

	/**
	 * filter labels  
	 * @param $label
	 * @return bool
	 */
	private function showLabel( $label ) {
    $l_show=true; 
    // filter labels
    // $l_show=!(array_key_exists("mainlabel", $this->params) && $label === $this->params[ "mainlabel" ] . '#'); 
    return $l_show;
	}

  /**
   * get the Value for the given dataItem 
   * @param dataItem - the dataItem to read the value from
   * @param plabel  - the label
   */
	protected function readValueFromItem(/* SMWDataItem */ $dataItem,$plabel ) {
    $l_value="?";
    switch ($dataItem->getDIType()) {
      case SMWDataItem::TYPE_BLOB:
        $l_value=$dataItem->getString();
      break;
    }
    $l_name=strtolower($plabel);
    if ($this->debug) {
      wfDebug("readValue from field: ".$l_name."=".$l_value."\n");
    }
		$this->document->setValue($l_name,$l_value);
	}

  /**
   * get the Value for the given dataValue 
   * @param dataValue - the dataValue to read the value from
   * @param plabel  - the label
   */
	protected function readValue(/* SMWDataValue */ $dataValue,$plabel ) {
    $l_value="?";
    // http://semantic-mediawiki.org/doc/classSMWDataValue.html
    $l_type=$dataValue->getTypeID();
    $l_dataItem=$dataValue->getDataItem();
    if ($l_dataItem!=null) {
      switch ($l_dataItem->getDIType()) {
        case SMWDataItem::TYPE_BLOB:
          $l_value=$l_dataItem->getString();
        break;
      }
    }
    $l_name=strtolower($plabel);
    if ($this->debug) {
      wfDebug("readValue from field: ".$l_name."(".$l_type.")=".$l_value."\n");
    }
		$this->document->setValue($l_name,$l_value);
	}

	/**
   * read data from the given row
	 * @param $row - SMWResultArray
	 */
	protected function readRowData( $row ) {
    // loop over fields of this row
		foreach ( $row as /* SMWResultArray */ $field ) {
      // http://kontext.fraunhofer.de/haenelt/kurs/Skripten/Wiki-Anleitungen/SMW-Ausgabeschnittstellex.pdf
      if ($this->debug) {
        // can not do this "Fatal error: Nesting level too deep - recursive dependency?
        //$l_fielddump = var_export($field, true);
        //wfDebug("fielddump=".$l_fielddump."\n");
      }
      $l_fieldinfo=$field->getPrintRequest();
      $l_label=$l_fieldinfo->getLabel();
      
      if ($this->debug) {
        wfDebug("field label=".$l_label."\n");
      }
			if( $this->showLabel($l_label)) {
        while ( ( /* SMWDataValue */ $dataValue = $field->getNextDataValue() ) !== false ) {
				  $this->readValue($dataValue,$l_label);
        }
        /*
        $l_contents=$field->getContent();
        if ($this->debug) {
          wfDebug("getting ".count($l_contents)." dataitems for field label=".$l_label."\n");
        }
        // loop over SMWDataItems
        foreach ( $l_contents as $dataItem ) {
				  $this->readValueFromItem($dataItem,$l_label);
				  $this->colNum++;
        }
        */
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

