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
		return $this->mItemData[$viewOrFilterId] ?? null;
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

			// contains plain text
			$values = [];
			// may contain links
			$formatted = [];
			// uses DEFAULTSORT when available
			$sorted = [];

			$field->reset();

			while ( ( $dataValue = $field->getNextDataValue() ) instanceof SMWDataValue ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

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

			$printouts[] = $this->serializePrintout( $values, $formatted, $sorted );

			$isFirstColumn = false;
		}

		$representation = [ 'p' => $printouts ];

		if ( $this->mItemData !== [] ) {
			$representation['d'] = $this->mItemData;
		}

		return $representation;
	}

	/**
	 * Serializes one printout into the compact per-item schema. A printout with no
	 * values becomes null. Otherwise it becomes [ 'v' => values ], with the formatted
	 * values ('f') and sort values ('s') only included when they differ from the plain
	 * values. The client falls back to the plain values when 'f' or 's' is absent.
	 *
	 * The returned printouts are a positional array aligned with the config-level
	 * printrequests order, replacing the former map keyed by uniqid( printRequest hash ).
	 * That alignment is load-bearing: QueryResult::getPrintRequests() order, the per-row
	 * field order from ResultArray, and the client's printrequests iteration must all
	 * agree, since filters and views now reference printouts by index.
	 */
	private function serializePrintout( array $values, array $formatted, array $sorted ): ?array {
		if ( $values === [] ) {
			return null;
		}

		$printout = [ 'v' => $values ];

		if ( $formatted !== $values ) {
			$printout['f'] = $formatted;
		}

		if ( $sorted !== $values ) {
			$printout['s'] = $sorted;
		}

		return $printout;
	}
}
