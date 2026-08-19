<?php

namespace SRF\Filtered\View;

use DataValues\Geo\Parsers\LatLongParser;
use Exception;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use SMW\DataValues\PropertyValue;
use SMW\Localizer\Message;
use SRF\Filtered\ResultItem;

class MapView extends View {

	private static $viewParams = null;

	private $mapProvider = null;
	private $mapProviderDark = null;
	private ?MapsLayerDefinitionsProvider $layerDefinitionsProvider = null;
	private ?MapsGeoJsonProvider $geoJsonProvider = null;

	/**
	 * @param string $mapProvider
	 */
	public function setMapProvider( $mapProvider ) {
		$this->mapProvider = $mapProvider;
	}

	/**
	 * @return null
	 */
	public function getMapProvider() {
		if ( $this->mapProvider === null ) {
			$this->setMapProvider( $GLOBALS['srfgMapProvider'] ?? '' );
		}

		return $this->mapProvider;
	}

	/**
	 * @param string $mapProviderDark
	 */
	public function setMapProviderDark( $mapProviderDark ) {
		$this->mapProviderDark = $mapProviderDark;
	}

	public function getMapProviderDark() {
		if ( $this->mapProviderDark === null ) {
			$this->setMapProviderDark( $GLOBALS['srfgMapProviderDark'] ?? '' );
		}

		return $this->mapProviderDark;
	}

	public function setLayerDefinitionsProvider( MapsLayerDefinitionsProvider $provider ): void {
		$this->layerDefinitionsProvider = $provider;
	}

	public function getLayerDefinitionsProvider(): MapsLayerDefinitionsProvider {
		if ( $this->layerDefinitionsProvider === null ) {
			$this->layerDefinitionsProvider = new MapsLayerDefinitionsProvider();
		}

		return $this->layerDefinitionsProvider;
	}

	public function setGeoJsonProvider( MapsGeoJsonProvider $provider ): void {
		$this->geoJsonProvider = $provider;
	}

	public function getGeoJsonProvider(): MapsGeoJsonProvider {
		if ( $this->geoJsonProvider === null ) {
			$this->geoJsonProvider = new MapsGeoJsonProvider();
		}

		return $this->geoJsonProvider;
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
			if ( $printRequest->getData() instanceof PropertyValue &&
				$printRequest->getData()->getInceptiveProperty()->getKey() === $markerPositionPropertyName &&
				( $value instanceof \SMWDIGeoCoord || $value instanceof \SMWDIBlob )
			) {
				if ( $value instanceof \SMWDIGeoCoord ) {
					// Geographic coordinates are already serialized as { lat, lng } pairs in
					// the shared per-printout values. The map view reads them from there
					// (p[position].v) instead of duplicating them per row.
					return null;
				}

				// Coordinates stored in a text property need server-side parsing, so they
				// are still emitted per row.
				$values = [];
				$coordParser = new LatLongParser();

				while ( $value instanceof \SMWDataItem ) {
					try {
						$latlng = $coordParser->parse( $value->getSerialization() );
						$values[] = [ 'lat' => $latlng->getLatitude(), 'lng' => $latlng->getLongitude() ];
						$value = $field->getNextDataItem();
					} catch ( Exception $exception ) {
						$this->getQueryPrinter()->addError( "Error on '$value': " . $exception->getMessage() );
					}
				}

				return [ 'positions' => $values ];
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
		$this->addMarkerPositionToConfig( $config );

		$config['map provider'] = $this->getMapProvider();
		$config['map provider dark'] = $this->getMapProviderDark();

		$this->addLayersToConfig( $config );
		$this->addGeoJsonToConfig( $config );

		return $config;
	}

	/**
	 * When the 'map view layers' parameter is set, its names become the map's base layers (the
	 * first being initially active) and the map provider settings are ignored client-side. Names
	 * matching a Maps custom layer definition are emitted under 'layer definitions'; the rest pass
	 * through under 'layers' as leaflet-providers provider strings.
	 *
	 * @param array &$config
	 */
	private function addLayersToConfig( array &$config ): void {
		$layerNames = $this->getActualParameters()['map view layers'];

		if ( $layerNames === [] ) {
			return;
		}

		$config['layers'] = array_values( $layerNames );

		$definitions = $this->getLayerDefinitionsProvider()->getDefinitions( $layerNames );

		if ( $definitions !== [] ) {
			$config['layer definitions'] = $definitions;
		}
	}

	/**
	 * When the 'map view geojson' parameter names a GeoJson: page (or a URL), the parsed GeoJSON
	 * is emitted under 'geojson' for the client to render as an overlay, with the parameter value
	 * passed through as 'geojson source' for the overlay's layer-control label. Nothing is emitted
	 * when the location cannot be resolved (missing page, invalid JSON, blocked URL). When the
	 * parameter is set but the Maps extension, which does the fetching, is unavailable, a query
	 * error is added instead of an overlay.
	 *
	 * @param array &$config
	 */
	private function addGeoJsonToConfig( array &$config ): void {
		$location = $this->getActualParameters()['map view geojson'];

		if ( $location === '' ) {
			return;
		}

		$provider = $this->getGeoJsonProvider();

		if ( !$provider->mapsIsAvailable() ) {
			// @phan-suppress-next-line PhanUndeclaredClassMethod SMW is not visible to Phan; usage matches Filtered.php
			$this->getQueryPrinter()->addError( Message::get( 'srf-filtered-map-geojson-requires-maps' ) );
			return;
		}

		$geoJson = $provider->getGeoJson( $location );

		if ( $geoJson !== [] ) {
			$config['geojson'] = $geoJson;
			$config['geojson source'] = $location;
		}
	}

	/**
	 * When the marker position property is a geographic coordinate printout, its index
	 * within the printrequests is stored so the client can read the marker positions from
	 * the shared per-printout values (p[position].v). Coordinates stored in a text property
	 * are parsed server-side and emitted per row instead (see getJsDataForRow).
	 *
	 * @param array &$config
	 */
	private function addMarkerPositionToConfig( &$config ) {
		$property = $this->getActualParameters()['map view marker position property'];

		if ( $property === '' ) {
			return;
		}

		$index = $this->getPropertyId( $property );
		$printrequests = $this->getQueryPrinter()->getPrintrequests();

		if ( $index !== null && ( $printrequests[$index]['type'] ?? null ) === '_geo' ) {
			$config['position'] = $index;
		}
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

			$params['layers'] = [
				'name' => 'map view layers',
				'message' => 'srf-paramdesc-filtered-map-layers',
				'default' => [],
				'islist' => true,
			];

			$params['geojson'] = [
				'name' => 'map view geojson',
				'message' => 'srf-paramdesc-filtered-map-geojson',
				'default' => '',
			];

			$params['height'] = [
				'type' => 'dimension',
				'name' => 'map view height',
				'message' => 'srf-paramdesc-filtered-map-height',
				'default' => 'auto',
			];

			$params['zoom'] = [
				'type' => 'integer',
				'name' => 'map view zoom',
				'message' => 'srf-paramdesc-filtered-map-zoom',
				'default' => '',
			];

			$params['minZoom'] = [
				'type' => 'integer',
				'name' => 'map view min zoom',
				'message' => 'srf-paramdesc-filtered-map-min-zoom',
				'default' => '',
			];

			$params['maxZoom'] = [
				'type' => 'integer',
				'name' => 'map view max zoom',
				'message' => 'srf-paramdesc-filtered-map-max-zoom',
				'default' => '',
			];

			// markercluster
			$params['marker cluster'] = [
				'type' => 'boolean',
				'name' => 'map view marker cluster',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster',
				'default' => true,
			];

			$params['marker cluster max zoom'] = [
				'type' => 'integer',
				'name' => 'map view marker cluster max zoom',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster-max-zoom',
				'default' => '',
			];

			// clustermaxradius - maxClusterRadius: The maximum radius that a cluster will cover from the central marker (in pixels). Default 80.
			$params['maxClusterRadius'] = [
				'type' => 'integer',
				'name' => 'map view marker cluster radius',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster-max-radius',
				'default' => '',
			];

			// clusterzoomonclick - zoomToBoundsOnClick: When you click a cluster we zoom to its bounds.
			$params['zoomToBoundsOnClick'] = [
				'type' => 'boolean',
				'name' => 'map view marker cluster zoom on click',
				'message' => 'srf-paramdesc-filtered-map-marker-cluster-zoom-on-click',
				'default' => true,
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
	 * @param array &$config
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
	 * @param &$config
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

			$file = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle(
				Title::newFromText( $icon, NS_FILE )
			)->getFile();

			if ( $file->exists() ) {
				$ret[$key] = $file->getUrl();
			} else {
				// TODO: $this->getQueryPrinter()->addError( NO_SUCH_FILE );
			}
		}

		return $ret;
	}

	/**
	 * @return string|null
	 */
	public function getInitError() {
		if ( $this->getActualParameters()['map view layers'] !== [] ) {
			return null;
		}

		return $this->getMapProvider() === '' ? 'srf-filtered-map-provider-missing-error' : null;
	}

}
