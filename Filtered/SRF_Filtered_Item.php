<?php

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
class SRF_Filtered_Item {

	private $mResultArray;
	private $mItemData = array();
	private $mQueryPrinter;

	public function __construct( $resultArray, SRFFiltered &$queryPrinter ) {
		$this->mResultArray = $resultArray;
		$this->mQueryPrinter = $queryPrinter;
	}

	public function setData ( $viewOrFilterId, $data ) {
		$this->mItemData[$viewOrFilterId] = $data;
	}

	public function unsetData ( $viewOrFilterId ) {
		unset( $this->mItemData[$viewOrFilterId] );
	}

	public function getData ( $viewOrFilterId ) {
		return $this->mItemData[$viewOrFilterId];
	}

	public function getValue() {
		return $this->mResultArray;
	}

	public function getArrayRepresentation() {

		$printouts = array();

			foreach ( $this->mResultArray as $i => $field ) {

				$printRequest = $field->getPrintRequest();

				$label = $printRequest->getLabel();
				$type = $printRequest->getTypeID();
				$params = $printRequest->getParameters();

				$values = array();

				$field->reset();
				while ( ( $value = $field->getNextText( SMW_OUTPUT_WIKI, null ) ) !== false ) {
					$values[] = $value;
				}

				$printouts[ md5( $printRequest->getHash() ) ] = array(
					'label' => $label,
					'type' => $type,
					'params' => $params,
					'values' => $values,
				);
			}


		return array(
			'printouts' => $printouts,
			'data' => $this->mItemData,
		);
	}
}
