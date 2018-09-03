<?php

namespace SRF\Filtered\View;

use DataValues\Geo\Parsers\LatLongParser;
use Exception;
use Message;
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
			$this->setMapProvider( isset( $GLOBALS['srfgMapProvider'] ) ? $GLOBALS['srfgMapProvider'] : '' );
		}

		return $this->mapProvider;
	}

	/**
	 * @param ResultItem $row
	 *
	 * @return array|null
	 */
	public function getJsDataForRow( ResultItem $row ) {

		$markerPositionPropertyName = str_replace(
			' ',
			'_',
			$this->getActualParameters()['map view marker position property']
		);

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

				} elseif ( class_exists( 'DataValues\Geo\Parsers\GeoCoordinateParser' ) ) {

					$coordParser = new LatLongParser();
					while ( $value instanceof \SMWDataItem ) {
						try {
							$latlng = $coordParser->parse( $value->getSerialization() );
							$values[] = [ 'lat' => $latlng->getLatitude(), 'lng' => $latlng->getLongitude() ];
							$value = $field->getNextDataItem();
						}
						catch ( Exception $exception ) {
							$this->getQueryPrinter()->addError( "Error on '$value': " . $exception->getMessage() );
						}
					}

				} else {
					$this->getQueryPrinter()->addError(
						Message::newFromKey( 'srf-filtered-map-geocoordinateparser-missing-error' )->inContentLanguage(
						)->text()
					);
				}

				return [ 'positions' => $values, ];
			}
		}

		return null;
	}

	/**
	 * Returns an array of config data for this view to be stored in the JS
	 *
	 * @return array
	 */
	public function getJsConfig() {
		$config = parent::getJsConfig();

		$jsConfigKeys = [
			'height',
			'zoom',
			'minZoom',
			'maxZoom',
			'marker cluster',
			'marker cluster max zoom',
			'maxClusterRadius',
			'zoomToBoundsOnClick',
		];

		foreach ( $jsConfigKeys as $key ) {
			$this->addToConfig( $config, $key );
		}

		$this->addMarkerIconSetupToConfig( $config );

		$config['map provider'] = $this->getMapProvider();

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

			$params['marker position property'] = [
				// 'type' => 'string',
				'name' => 'map view marker position property',
				'message' => 'srf-paramdesc-filtered-map-position',
				'default' => '',
				// 'islist' => false,
			];

			$params['marker icon property'] = [
				// 'type' => 'string',
				'name' => 'map view marker icon property',
				'message' => 'srf-paramdesc-filtered-map-icon',
				'default' => '',
				// 'islist' => false,
			];

			$params['marker icons'] = [
				// 'type' => 'string',
				'name' => 'map view marker icons',
				'message' => 'srf-paramdesc-filtered-map-icons',
				'default' => [],
				'islist' => true,
			];

			$params['height'] = [
				'type' => 'dimension',
				'name' => 'map view height',
				'message' => 'srf-paramdesc-filtered-map-height',
				'default' => 'auto',
				// 'islist' => false,
			];

			$params['zoom'] = [
				'type' => 'integer',
				'name' => 'map view zoom',
				'message' => 'srf-paramdesc-filtered-map-zoom',
				'default' => '',
				// 'islist' => false,
			];

			$params['minZoom'] = [
				'type' => 'integer',
				'name' => 'map view min zoom',
				'message' => 'srf-paramdesc-filtered-map-min-zoom',
				'default' => '',
				// 'islist' => false,
			];

			$params['maxZoom'] = [
				'type' => 'integer',
				'name' => 'map view max zoom',
				'message' => 'srf-paramdesc-filtered-map-max-zoom',
				'default' => '',
				// 'islist' => false,
			];

			//markercluster
			$params['marker cluster'] = [
				'type' => 'boolean',
				'name' => 'map view marker cluster',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster',
				'default' => true,
				// 'islist' => false,
			];

			$params['marker cluster max zoom'] = [
				'type' => 'integer',
				'name' => 'map view marker cluster max zoom',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster-max-zoom',
				'default' => '',
				// 'islist' => false,
			];

			//clustermaxradius - maxClusterRadius: The maximum radius that a cluster will cover from the central marker (in pixels). Default 80.
			$params['maxClusterRadius'] = [
				'type' => 'integer',
				'name' => 'map view marker cluster radius',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster-max-radius',
				'default' => '',
				// 'islist' => false,
			];

			//clusterzoomonclick - zoomToBoundsOnClick: When you click a cluster we zoom to its bounds.
			$params['zoomToBoundsOnClick'] = [
				'type' => 'boolean',
				'name' => 'map view marker cluster zoom on click',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster-zoom-on-click',
				'default' => true,
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

		$paramDefinition = self::getParameters()[$key];

		$param = $this->getActualParameters()[$paramDefinition['name']];

		if ( $param !== $paramDefinition['default'] ) {
			$config[$key] = $param;
		}

	}

	/**
	 * @param $config
	 */
	protected function addMarkerIconSetupToConfig( &$config ) {

		$param = $this->getActualParameters()['map view marker icon property'];

		if ( $param !== '' ) {
			$config['marker icon property'] = $this->getPropertyId( $param );
		}

		$config['marker icons'] = $this->getMarkerIcons();
	}

	/**
	 * @param $prop
	 *
	 * @return array
	 */
	protected function getPropertyId( $prop ) {

		$prop = strtr( $prop, ' ', '_' );

		$printrequests = $this->getQueryPrinter()->getPrintrequests();
		$cur = reset( $printrequests );

		while ( $cur !== false && ( !array_key_exists( 'property', $cur ) || $cur['property'] !== $prop ) ) {
			$cur = next( $printrequests );
		}

		return key( $printrequests );
	}

	/**
	 * @return array
	 */
	private function getMarkerIcons() {

		$ret = [];

		$actualParameters = self::getActualParameters()['map view marker icons'];

		foreach ( $actualParameters as $relation ) {

			$relation = explode( '=', $relation, 2 );

			if ( count( $relation ) === 1 ) {
				$key = 'default';
				$icon = $relation[0];
			} else {
				$key = $relation[0];
				$icon = $relation[1];
			}

			$file = \WikiPage::factory( \Title::newFromText( $icon, NS_FILE ) )->getFile();

			if ( $file->exists() ) {
				$ret[$key] = $file->getUrl();
			} else {
				// TODO: $this->getQueryPrinter()->addError( NO_SUCH_FILE );
			}
		}

		return $ret;
	}

	/**
	 * @return bool
	 */
	public function getInitError() {
		return $this->getMapProvider() === '' ? 'srf-filtered-map-provider-missing-error' : null;
	}

}