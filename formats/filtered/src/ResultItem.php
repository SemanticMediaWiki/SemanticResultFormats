<?php

namespace SRF\Filtered;

use SMW\DIWikiPage;
use SMW\Query\Result\ResultArray;
use SMWDataValue;
use SMWDIGeoCoord;
use SMWErrorValue;

/**
 * File holding the SRF_Filtered_Item class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

/**
 * The SRF_Filtered_Item class.
 *
 * @ingroup SemanticResultFormats
 */
class ResultItem {

	private $mResultArray;
	private $mItemData = [];
	private $mQueryPrinter;

	/**
	 * @param ResultArray[] $resultArray
	 * @param Filtered &$queryPrinter
	 */
	public function __construct( array $resultArray, Filtered &$queryPrinter ) {
		$this->mResultArray = $resultArray;
		$this->mQueryPrinter = $queryPrinter;
	}

	public function setData( $viewOrFilterId, $data ) {
		if ( $data === null ) {
			$this->unsetData( $viewOrFilterId );
		} else {
			$this->mItemData[$viewOrFilterId] = $data;
		}
	}

	public function unsetData( $viewOrFilterId ) {
		unset( $this->mItemData[$viewOrFilterId] );
	}

	public function getData( $viewOrFilterId ) {
		return $this->mItemData[$viewOrFilterId];
	}

	/**
	 * @return ResultArray[]
	 */
	public function getValue() {
		return $this->mResultArray;
	}

	public function getArrayRepresentation() {
		$printouts = [];
		$isFirstColumn = true;

		foreach ( $this->mResultArray as $field ) {

			$printRequest = $field->getPrintRequest();
			// contains plain text
			$values = [];
			// may contain links
			$formatted = [];
			// uses DEFAULTSORT when available
			$sorted = [];

			$field->reset();

			while ( ( $dataValue = $field->getNextDataValue() ) instanceof SMWDataValue ) {

				$dataItem = $dataValue->getDataItem();

				if ( $dataItem instanceof SMWDIGeoCoord ) {
					$values[] = [ 'lat' => $dataItem->getLatitude(), 'lng' => $dataItem->getLongitude() ];
					$sorted[] = $dataItem->getSortKey();
				} elseif ( $dataItem instanceof DIWikiPage ) {
					$values[] = $dataValue->getShortWikiText();
					$sorted[] = bin2hex( $dataItem->getSortKey() );
				} else {
					$values[] = $dataValue->getShortWikiText();
					$sorted[] = $dataValue->getShortWikiText();
				}

				if ( $dataValue instanceof SMWErrorValue ) {
					$formatted[] = $dataItem->getSerialization();
				} else {
					$formatted[] = $dataValue->getShortHTMLText( $this->mQueryPrinter->getLinker( $isFirstColumn ) );
				}
			}

			$printouts[$this->mQueryPrinter->uniqid( $printRequest->getHash() )] = [
				'values' => $values,
				'formatted values' => $formatted,
				'sort values' => $sorted,
			];

			$isFirstColumn = false;
		}

		return [
			'printouts' => $printouts,
			'data' => $this->mItemData,
		];
	}
}
