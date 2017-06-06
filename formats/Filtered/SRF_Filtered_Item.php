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
	private $mItemData = [];
	private $mQueryPrinter;

	/**
	 * @param SMWResultArray[] $resultArray
	 * @param SRFFiltered $queryPrinter
	 */
	public function __construct( array $resultArray, SRFFiltered &$queryPrinter ) {
		$this->mResultArray = $resultArray;
		$this->mQueryPrinter = $queryPrinter;
	}

	public function setData( $viewOrFilterId, $data ) {
		$this->mItemData[$viewOrFilterId] = $data;
	}

	public function unsetData( $viewOrFilterId ) {
		unset( $this->mItemData[$viewOrFilterId] );
	}

	public function getData( $viewOrFilterId ) {
		return $this->mItemData[$viewOrFilterId];
	}

	/**
	 * @return SMWResultArray[]
	 */
	public function getValue() {
		return $this->mResultArray;
	}

	public function getArrayRepresentation() {

		$printouts = [];
		$isFirstColumn = true;
		$dbr = wfGetDB( DB_SLAVE ); // might need this soon

		foreach ( $this->mResultArray as $field ) {

			$printRequest = $field->getPrintRequest();

			$label = $printRequest->getLabel();
			$type = $printRequest->getTypeID();
			$params = $printRequest->getParameters();

			$values = []; // contains plain text
			$formatted = []; // may contain links
			$sortValues = []; // useful if value is a page name
			$useDefaultSort = array_key_exists('value filter pagedefaultsort', $params);

			$field->reset();
			while ( ( $value = $field->getNextDataValue() ) !== false ) {
				$values[] = $value->getShortHTMLText() ;
				$formatted[] = $value->getShortHTMLText( $this->mQueryPrinter->getLinker( $isFirstColumn ) );
				if($useDefaultSort) {
					$title = Title::newFromText($value->getShortHTMLText());
					if($title && $title->getArticleID() !== 0) {
						$sortValue = $dbr->selectField('page_props',
							'pp_value',
							array('pp_page' => $title->getArticleID(), 'pp_propname' => "defaultsort"),
							__METHOD__);
						if($sortValue === false) {
							$sortValues[] = $value->getShortHTMLText();
						} else {
							$sortValues[] = $sortValue;
						}
					} else {
						$sortValues[] = $value->getShortHTMLText();
					}
				}
			}

			$printout = [
				'label' => $label,
				'type' => $type,
				'params' => $params,
				'values' => $values,
				'formatted values' => $formatted
			];
			if($useDefaultSort) {
				$printout['sort values'] = $sortValues;
			}
			$printouts[ md5( $printRequest->getHash() ) ] = $printout;

			$isFirstColumn = false;
		}

		return [
			'printouts' => $printouts,
			'data' => $this->mItemData,
		];
	}
}
