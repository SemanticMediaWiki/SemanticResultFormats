<?php

namespace SRF\dataframe;

use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\FileExportPrinter;

/**
 * @author Marco Falda
 * @since 3.2
 */
class DataframePrinter extends FileExportPrinter {

	public const HEADER_ROW_OFFSET = 1;

	protected $fileFormats = [
		'R' => [
			'writer' => 'R',
			'mimetype' => 'text/R',
			'extension' => '.R',
		],
	];

	protected $styled = false;
	protected $fileFormat;

	/**
	 * Output a human readable label for this printer.
	 *
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->msg( 'srf-printername-dataframe' );
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
		$this->fileFormat = $this->fileFormats[ 'R' ];
		parent::outputAsFile( $queryResult, $params );
	}

	/**
	 * Defines the list of available parameters to an individual result
	 * printer.
	 *
	 * @return array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$definitions[ 'searchlabel' ]->setDefault( wfMessage( 'srf-dataframe-link' )->inContentLanguage()->text() );

		$params[ 'filename' ] = [
			'type'    => 'string',
			'name'    => 'filename',
			'default' => '',
			'message' => 'srf-paramdesc-dataframe-filename',
		];

		$params[ 'fileformat' ] = [
			'type'    => 'string',
			'name'    => 'fileformat',
			'default' => 'R',
			'tolower' => true,
			'message' => 'srf-paramdesc-dataframe-fileformat',
		];

		return $params;
	}

	/**
	 * Return serialised results in specified format.
	 *
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
		$res = 'data.frame(';
		if ( array_key_exists( 'rownames', $this->params ) ) {
			$res .= 'row.names=T, ';
		}
		$headers = [];
		$printRequests = $queryResult->getPrintRequests();

		foreach ( $printRequests as $printRequest ) {
			$header = $printRequest->getLabel();
			if ( $header === '' ) {
				$header = 'ID';
			}
			$headers[] = $header;
		}

		$cols = [];
		while ( $resultRow = $queryResult->getNext() ) {

			foreach ( $resultRow as $resultField ) {
				$propertyLabel = $resultField->getPrintRequest()->getLabel();
				// $subjectLabel = $resultField->getResultSubject()->getTitle()->getFullText();
				$dataItems = $resultField->getContent();

				if ( count( $dataItems ) > 1 ) {
					$values = [];

					while ( $value = $resultField->getNextText( SMW_OUTPUT_FILE ) ) {
						$values[] = $value;
					}
					$rowData = "'" . implode( ', ', $values ) . "'";
				} else {
					$nextDataValue = $resultField->getNextDataValue();
					if ( $nextDataValue !== false ) {
						if ( $nextDataValue == '' ) {
							$rowData = 'NA';
						} elseif ( $nextDataValue instanceof \SMWNumberValue ) {
							$rowData = $nextDataValue;
						} elseif ( $nextDataValue instanceof \SMWTimeValue ) {
							$rowData = "'" . $nextDataValue->getISO8601Date() . "'";
						} else {
							$nextDataValue = str_replace( "'", "\'", $nextDataValue );
							$rowData = "'$nextDataValue'";
						}
					} else {
						$rowData = 'NA';
					}
				}
				// $cols[$propertyLabel][$subjectLabel][] = $rowData;
				$cols[$propertyLabel][][] = $rowData;
			}
		}

		/*
			INPUT -> cols: [ "prop1" => [ [ [subj111], ['subj112'], [subj113] ], ...  ]
				e.g.: data.frame("prop1" = c(c(c(subj111), c('subj112'), c(subj113))),
			c(subj121, 'subj122', subj123)),
							"prop2" = c(c(subj211, 'subj212', subj213),
								c(subj221, 'subj222', subj223)))
		*/
		$i = 0;
		foreach ( $cols as $props ) {
			$data1 = [];
			foreach ( $props as $subjs ) {
				$data1[] = implode( ",\n", $subjs );
			}
			$data[] = "'" . $headers[$i] . "' = c(" . implode( ', ', $data1 ) . ')';
			$i++;
		}

		$res .= implode( ",\n", $data ) . ')';
		return $res;
	}

}
