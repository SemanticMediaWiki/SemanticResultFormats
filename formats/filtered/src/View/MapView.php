<?php

namespace SRF\Filtered\View;

use DataValues\Geo\Parsers\GeoCoordinateParser;
use Exception;
use SMWPropertyValue;
use SRF\Filtered\ResultItem;

class MapView extends View {

	private $markerPositionPropertyId = null;

	/**
	 * @param ResultItem $row
	 * @return array|null
	 */
	public function getJsDataForRow( ResultItem $row ) {

		$markerPositionPropertyName = $this->getActualParameters()[ 'map view marker position property' ];

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

					$coordParser = new GeoCoordinateParser();
					while ( $value instanceof \SMWDataItem ) {
						try {
							$latlng = $coordParser->parse( $value->getSerialization() );
							$values[] = [ 'lat' => $latlng->getLatitude(), 'lng' => $latlng->getLongitude() ];
							$value = $field->getNextDataItem();
						} catch ( Exception $exception ) {
							$this->getQueryPrinter()->addError( "Error on '$value': " . $exception->getMessage() );
						}
					}

				}

				return [ 'positions' => $values, ];
			}
		}

		return null;
	}

	public static function getParameters() {

		$params = parent::getParameters();

		$params[] = [
			// 'type' => 'string',
			'name'    => 'map view marker position property',
			'message' => 'srf-paramdesc-filtered-map-position',
			'default' => '',
			// 'islist' => false,
		];

		return $params;
	}

	public function getResourceModules() {
		return 'ext.srf.filtered.map-view';
	}

}