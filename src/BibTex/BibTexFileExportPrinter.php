<?php

namespace SRF\BibTex;

use SMWTimeValue as TimeValue;
use SMWQueryResult as QueryResult;
use SMW\Query\ResultPrinters\FileExportPrinter;

/**
 * Printer class for creating BibTeX exports
 *
 * For details on availble keys see the README
 *
 * Example of a book :
 *
 * @Book{abramowitz1964homf,
 *   author =     "Milton Abramowitz and Irene A. Stegun",
 *   title =     "Handbook of Mathematical Functions",
 *   publisher =     "Dover",
 *   year =     1964,
 *   address =     "New York",
 *   edition =     "ninth Dover printing, tenth GPO printing"
 * }
 *
 * @license GNU GPL v2+
 * @since 1.5
 *
 * @author Markus Krötzsch
 * @author Denny Vrandecic
 * @author Frank Dengler
 * @author Steren Giannini
 */
class BibTexFileExportPrinter extends FileExportPrinter {

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return wfMessage( 'srf_printername_bibtex' )->text();
	}

	/**
	 * @see FileExportPrinter::getMimeType
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getMimeType( QueryResult $queryResult ) {
		return 'text/bibtex';
	}

	/**
	 * @see FileExportPrinter::getFileName
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getFileName( QueryResult $queryResult ) {

		if ( $this->params['filename'] !== '' ) {

			if ( strpos( $this->params['filename'], '.bib' ) === false ) {
				$this->params['filename'] .= '.bib';
			}

			return str_replace( ' ', '_', $this->params['filename'] );
		} elseif ( $this->getSearchLabel( SMW_OUTPUT_WIKI ) != '' ) {
			return str_replace( ' ', '_', $this->getSearchLabel( SMW_OUTPUT_WIKI ) ) . '.bib';
		}

		return 'BibTeX.bib';
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['filename'] = [
			'message' => 'smw-paramdesc-filename',
			'default' => 'bibtex.bib',
		];

		return $params;
	}

	/**
	 * @since 3.1
	 *
	 * @param array $list
	 *
	 * @return string
	 */
	public function getFormattedList( $key, array $values ) {
		return $GLOBALS['wgLang']->listToText( $values );
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( QueryResult $res, $outputMode ) {

		if ( $outputMode !== SMW_OUTPUT_FILE ) {
			return $this->getBibTexLink( $res, $outputMode );
		}

		$items = [];

		while ( $row = $res->getNext() ) {
			$items[] = $this->newItem( $row )->text();
		}

		return implode( "\r\n\r\n", $items );
	}

	private function getBibTexLink( QueryResult $res, $outputMode ) {

		// Can be viewed as HTML if requested, no more parsing needed
		$this->isHTML = $outputMode == SMW_OUTPUT_HTML;

		$link = $this->getLink(
			$res,
			$outputMode
		);

		return $link->getText( $outputMode, $this->mLinker );
	}

	/**
	 * @since 3.1
	 *
	 * @param $row array of SMWResultArray
	 *
	 * @return bibTexItem
	 */
	private function newItem( array /* of SMWResultArray */ $row ) {

		$item = new Item();
		$item->setFormatterCallback( [ $this, 'getFormattedList' ] );

		foreach ( $row as /* SMWResultArray */ $field ) {
			$printRequest = $field->getPrintRequest();
			$values = [];

			$label = strtolower( $printRequest->getLabel() );
			$dataValue = $field->getNextDataValue();

			if ( $dataValue === false ) {
				continue;
			}

			if ( $label === 'date' && $dataValue instanceof TimeValue ) {
				$item->set( 'year', $dataValue->getYear() );
				$item->set( 'month', $dataValue->getMonth() );
			} elseif ( $label === 'author' || $label === 'authors' ) {
				$values[] = $dataValue->getShortWikiText();

				while ( ( /* SMWDataValue */ $dataValue = $field->getNextDataValue() ) !== false ) {
					$values[] = $dataValue->getShortWikiText();
				}

				$item->set( 'author', $values );
			} elseif ( $label === 'editor' || $label === 'editors' ) {
				$values[] = $dataValue->getShortWikiText();

				while ( ( /* SMWDataValue */ $dataValue = $field->getNextDataValue() ) !== false ) {
					$values[] = $dataValue->getShortWikiText();
				}

				$item->set( 'editor', $values );
			} else {
				$item->set( $label, $dataValue->getShortWikiText() );
			}
		}

		return $item;
	}

}
