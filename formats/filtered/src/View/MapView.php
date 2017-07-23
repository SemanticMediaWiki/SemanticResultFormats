<?php

namespace SRF\Filtered\View;

use DataValues\Geo\Parsers\GeoCoordinateParser;
use Exception;
use SMWPropertyValue;
use SRF\Filtered\ResultItem;

class MapView extends View {

	private static $viewParams = null;

	private $mapProvider = null;


	/**
	 * @param null $mapProvider
	 */
	public function setMapProvider( $mapProvider ) {
		$this->mapProvider = $mapProvider;
	}

	/**
	 * @return null
	 */
	public function getMapProvider() {
		if ( $this->mapProvider === null ) {
			$this->setMapProvider( isset( $GLOBALS[ 'srfgMapProvider' ] ) ? $GLOBALS[ 'srfgMapProvider' ] : '' );
		}

		return $this->mapProvider;
	}

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

	/**
	 * Returns an array of config data for this view to be stored in the JS
	 * @return array
	 */
	public function getJsConfig() {
		$config = parent::getJsConfig();

		foreach ( [ 'height', 'zoom', 'min zoom', 'max zoom' ] as $key ) {
			$this->addToConfig( $config, $key );
		}

		$config[ 'map provider' ] = $this->getMapProvider();

		return $config;
	}

	/**
	 * A function to describe the allowed parameters of a query for this view.
	 *
	 * @return array of Parameter
	 */
	public static function getParameters() {

		if ( self::$viewParams === null ) {

			$params = parent::getParameters();

			$params[ 'marker position property' ] = [
				// 'type' => 'string',
				'name' => 'map view marker position property',
				'message' => 'srf-paramdesc-filtered-map-position',
				'default' => '',
				// 'islist' => false,
			];

			$params[ 'height' ] = [
				'type' => 'dimension',
				'name' => 'map view height',
				'message' => 'srf-paramdesc-filtered-map-height',
				'default' => 'auto',
				// 'islist' => false,
			];

			$params[ 'zoom' ] = [
				'type' => 'integer',
				'name' => 'map view zoom',
				'message' => 'srf-paramdesc-filtered-map-zoom',
				'default' => 14,
				// 'islist' => false,
			];

			$params[ 'min zoom' ] = [
				'type' => 'integer',
				'name' => 'map view min zoom',
				'message' => 'srf-paramdesc-filtered-map-zoom',
				'default' => -1,
				// 'islist' => false,
			];

			$params[ 'max zoom' ] = [
				'type' => 'integer',
				'name' => 'map view max zoom',
				'message' => 'srf-paramdesc-filtered-map-zoom',
				'default' => -1,
				// 'islist' => false,
			];

			self::$viewParams = $params;
		}

		return self::$viewParams;
	}

	/**
	 * Returns the name of the resource module to load.
	 *
	 * @return string
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.map-view';
	}

	/**
	 * @param array $config
	 * @param string $key
	 */
	private function addToConfig( &$config, $key ) {

		$paramDefinition = self::getParameters()[ $key ];

		$param = $this->getActualParameters()[ $paramDefinition[ 'name' ] ];

		if ( $param !== $paramDefinition[ 'default' ] ) {
			$config[ $key ] = $param;
		}

	}

	/**
	 * @return bool
	 */
	public function getInitError() {
		return $this->getMapProvider() === ''? 'srf-filtered-map-provider-missing-error' : null;
	}

}