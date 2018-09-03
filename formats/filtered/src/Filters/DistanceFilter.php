<?php

namespace SRF\Filtered\Filter;

/**
 * File holding the SRF_FF_Distance class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

use DataValues\Geo\Parsers\LatLongParser;
use Exception;
use SMWPropertyValue;
use SRF\Filtered\ResultItem;

/**
 * The SRF_FF_Distance class.
 *
 * Available parameters for this filter:
 *   distance filter origin: the point from which the distance is measured (address or geo coordinate)
 *   distance filter property: the property containing the point to which distance is measured - not implemented yet
 *   distance filter unit: the unit in which the distance is measured
 *
 * @ingroup SemanticResultFormats
 */
class DistanceFilter extends Filter {

	private $jsConfig;

	/**
	 * Returns the name (string) or names (array of strings) of the resource
	 * modules to load.
	 *
	 * @return string|array
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.distance-filter';
	}

	protected function buildJsConfig() {

		parent::buildJsConfig();

		if ( !array_key_exists( 'distance filter origin', $this->getActualParameters() ) ) {
			$label = $this->getPrintRequest()->getLabel();
			$this->getQueryPrinter()->addError( "Missing origin for distance filter on '$label'." );
			return [];
		}

		try {

			$geoCoordinateParser = new LatLongParser();

			$callback = function ( $value ) use ( $geoCoordinateParser ) {
				$latlng = $geoCoordinateParser->parse( $value );
				return [ 'lat' => $latlng->getLatitude(), 'lng' => $latlng->getLongitude() ];
			};

			$this->addValueToJsConfig( 'distance filter origin', 'origin', null, $callback );

		}
		catch ( Exception $exception ) {
			$label = $this->getPrintRequest()->getLabel();
			$this->getQueryPrinter()->addError( "Distance filter on $label: " . $exception->getMessage() );
			return [];
		}

		$this->addValueToJsConfig( 'distance filter collapsible', 'collapsible' );
		$this->addValueToJsConfig( 'distance filter initial value', 'initial value' );
		$this->addValueToJsConfig( 'distance filter max distance', 'max' );
		$this->addValueToJsConfig( 'distance filter unit', 'unit' );
		$this->addValueListToJsConfig( 'distance filter switches', 'switches' );

	}

	/**
	 * @param ResultItem $row
	 *
	 * @return array|null
	 */
	public function getJsDataForRow( ResultItem $row ) {

		$markerPositionPropertyName = $this->getPrintRequest()->getData()->getInceptiveProperty()->getKey();

		foreach ( $row->getValue() as $field ) {

			$printRequest = $field->getPrintRequest();
			$field->reset();

			$value = $field->getNextDataItem();
			if ( $printRequest->getData() instanceof SMWPropertyValue &&
				$printRequest->getData()->getInceptiveProperty()->getKey() === $markerPositionPropertyName &&
				( $value instanceof \SMWDIGeoCoord || $value instanceof \SMWDIBlob )
			) {
				$values = []; // contains plain text

				if ( $value instanceof \SMWDIGeoCoord ) {

					while ( $value instanceof \SMWDIGeoCoord ) {
						$values[] = [ 'lat' => $value->getLatitude(), 'lng' => $value->getLongitude() ];
						$value = $field->getNextDataItem();
					}

				} else {

					$coordParser = new LatLongParser();
					while ( $value instanceof \SMWDataItem ) {
						try {
							$latlng = $coordParser->parse( $value->getSerialization() );
							$values[] = [ 'lat' => $latlng->getLatitude(), 'lng' => $latlng->getLongitude() ];
						}
						catch ( \Exception $exception ) {
							$this->getQueryPrinter()->addError( "Error on '$value': " . $exception->getMessage() );
						}
						$value = $field->getNextDataItem();
					}

				}

				return [ 'positions' => $values, ];
			}
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function isValidFilterForPropertyType() {
		return $this->getPrintRequest()->getTypeID() === '_geo' || $this->getPrintRequest()->getTypeID() === '_txt';
	}
}
