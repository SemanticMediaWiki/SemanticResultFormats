<?php

namespace SRF\Prolog;

use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\FileExportPrinter;

/**
 * @author Marco Falda
 * @since 3.2
 */
class PrologPrinter extends FileExportPrinter {

	public const HEADER_ROW_OFFSET = 1;

	protected $fileFormats = [
		'pl' => [
			'writer' => 'pl',
			'mimetype' => 'text/prolog',
			'extension' => '.pl',
		],
		'pro' => [
			'writer' => 'pl',
			'mimetype' => 'text/prolog',
			'extension' => '.pro',
		],
	];

	protected $fileFormat;

	/**
	 * Output a human readable label for this printer.
	 *
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->msg( 'srf-printername-prolog' );
	}

	/**
	 * @see ExportPrinter::getMimeType()
	 *
	 * @since 1.8
	 *
	 * @param QueryResult $queryResult
	 *
	 * @return string
	 */
	public function getMimeType( QueryResult $queryResult ) {
		return $this->fileFormat[ 'mimetype' ];
	}

	/**
	 * @see ExportPrinter::getFileName
	 *
	 * @param QueryResult $queryResult
	 *
	 * @return string
	 */
	public function getFileName( QueryResult $queryResult ) {
		return ( $this->params[ 'filename' ] ?: base_convert( uniqid(), 16, 36 ) ) . $this->fileFormat[ 'extension' ];
	}

	/**
	 * @see ExportPrinter::outputAsFile
	 *
	 * @param QueryResult $queryResult
	 * @param array $params
	 */
	public function outputAsFile( QueryResult $queryResult, array $params ) {
		if ( array_key_exists( 'fileformat', $params ) && array_key_exists( $params[ 'fileformat' ]->getValue(), $this->fileFormats ) ) {
			$this->fileFormat = $this->fileFormats[ $params[ 'fileformat' ]->getValue() ];
		} else {
			$this->fileFormat = $this->fileFormats[ 'pl' ];
		}

		parent::outputAsFile( $queryResult, $params );
	}

	/**
	 * Defines the list of available parameters to an individual result
	 * printer.
	 *
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$definitions[ 'searchlabel' ]->setDefault( wfMessage( 'srf-prolog-link' )->inContentLanguage()->text() );

		$params[ 'filename' ] = [
			'type'    => 'string',
			'name'    => 'filename',
			'default' => '',
			'message' => 'srf-paramdesc-prolog-filename',
		];

		$params[ 'fileformat' ] = [
			'type'    => 'string',
			'name'    => 'fileformat',
			'default' => 'pl',
			'tolower' => true,
			'message' => 'srf-paramdesc-prolog-fileformat',
		];

		$params[ 'pname' ] = [
			'type'    => 'string',
			'name'    => 'pname',
			'default' => 'predicate',
			'tolower' => true,
			'message' => 'srf-paramdesc-prolog-pname',
		];

		$params[ 'navalue' ] = [
			'type'    => 'string',
			'name'    => 'navalue',
			'default' => "'NA'",
			'message' => 'srf-paramdesc-prolog-navalue',
		];

		return $params;
	}

	/**
	 * This method gets the query result object and is supposed to return
	 * whatever output the format creates. For example, in the list format, it
	 * goes through all results and constructs an HTML list, which is then
	 * returned. Looping through the result object is somewhat complex, and
	 * requires some understanding of the `QueryResult` class.
	 *
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( QueryResult $queryResult, $outputMode ) {
		if ( $outputMode === SMW_OUTPUT_FILE ) {
			return $this->getResultFileContents( $queryResult );
		}

		$this->isHTML = ( $outputMode === SMW_OUTPUT_HTML );
		return $this->getLink( $queryResult, $outputMode )->getText( $outputMode, $this->mLinker );
	}

	/**
	 * @param QueryResult $queryResult
	 *
	 * @return string
	 */
	protected function getResultFileContents( QueryResult $queryResult ) {
		$res = '';
		/*if ($this->params['rownames'])
			$res .= 'row.names=T, ';*/

		$preds = [];
		while ( $resultRow = $queryResult->getNext() ) {

			$subject = '';
			$i = 0;
			foreach ( $resultRow as $resultField ) {
				if ( $i === 0 ) {
					$subject = $dataItems = $resultField->getContent()[0];
				} else {
					$propertyLabel = $resultField->getPrintRequest()->getLabel();

					// $subjectLabel = $resultField->getResultSubject()->getTitle()->getFullText();
					$dataItems = $resultField->getContent();

					if ( count( $dataItems ) > 1 ) {
						$values = [];

						while ( $value = $resultField->getNextText( SMW_OUTPUT_FILE ) ) {
							$values[] = $value;
						}
						$rowData = "['" . implode( "', '", $values ) . "']";
					} else {
						$nextDataValue = $resultField->getNextDataValue();
						if ( $nextDataValue !== false ) {
							if ( $nextDataValue instanceof \SMWNumberValue ) {
								$rowData = $nextDataValue;
							} elseif ( $nextDataValue instanceof \SMWTimeValue ) {
								$rowData = "'" . $nextDataValue->getISO8601Date() . "'";
							} else {
								$nextDataValue = str_replace( "'", "\'", $nextDataValue );
								$rowData = "'$nextDataValue'";
							}
						} else {
							$rowData = $this->params['navalue'];
						}
					}
					$preds[] = $this->params['pname'] . "('$subject', '$propertyLabel', $rowData).";
				}

				$i++;
			}
		}

		$res = implode( "\n", $preds );
		return $res;
	}

}
