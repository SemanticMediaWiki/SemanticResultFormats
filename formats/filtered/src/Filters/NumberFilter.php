<?php

namespace SRF\Filtered\Filter;

/**
 * File holding the SRF_FF_Number class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

use SMWPropertyValue;
use SRF\Filtered\ResultItem;

/**
 * The SRF_FF_Number class.
 *
 * Available parameters for this filter:
 *   number filter origin: the point from which the number is measured (address or geo coordinate)
 *   number filter property: the property containing the point to which number is measured - not implemented yet
 *   number filter unit: the unit in which the number is measured
 *
 * @ingroup SemanticResultFormats
 */
class NumberFilter extends Filter {

	/**
	 * Returns the name (string) or names (array of strings) of the resource
	 * modules to load.
	 *
	 * @return string|array
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.number-filter';
	}

	/**
	 * @param ResultItem $row
	 *
	 * @return array|null
	 */
	public function getJsDataForRow( ResultItem $row ) {
		$propertyName = $this->getPrintRequest()->getData()->getInceptiveProperty()->getKey();

		foreach ( $row->getValue() as $field ) {

			$printRequest = $field->getPrintRequest();

			if ( $printRequest->getData() instanceof SMWPropertyValue &&
				$printRequest->getData()->getInceptiveProperty()->getKey() === $propertyName &&
				( $field->reset() instanceof \SMWDINumber || $field->reset() instanceof \SMWDITime )
			) {

				$values = []; // contains plain text
				$value = $field->getNextDataValue();

				while ( $value instanceof \SMWNumberValue || $value instanceof \SMWTimeValue ) {

					if ( $value instanceof \SMWNumberValue ) {
						$cuv = $value->getConvertedUnitValues();
						$values[] = $cuv[$value->getCanonicalMainUnit()];
					} else {
						$values[] = $value->getYear();
					}
					$value = $field->getNextDataItem();
				}

				return [ 'values' => $values ];
			}
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function isValidFilterForPropertyType() {
		$typeID = $this->getPrintRequest()->getTypeID();
		return $typeID === '_num' || $typeID === '_qty' || '_dat';
	}

	protected function buildJsConfig() {
		parent::buildJsConfig();
		$this->addValueToJsConfig( 'number filter collapsible', 'collapsible' );
		$this->addValueToJsConfig( 'number filter max value', 'max' );
		$this->addValueToJsConfig( 'number filter min value', 'min' );
		$this->addValueToJsConfig( 'number filter step', 'step' );
		$this->addValueToJsConfig( 'number filter sliders', 'sliders' );
		$this->addValueToJsConfig( 'number filter label', 'caption', $this->getPrintRequest()->getOutputFormat() );
		$this->addValueListToJsConfig( 'number filter values', 'values' );
		$this->addValueListToJsConfig( 'number filter switches', 'switches' );
	}
}
