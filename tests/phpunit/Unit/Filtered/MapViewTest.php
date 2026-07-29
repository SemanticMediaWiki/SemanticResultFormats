<?php

namespace SRF\Tests\Filtered;

use SRF\Filtered\Filtered;
use SRF\Filtered\View\MapsGeoJsonProvider;
use SRF\Filtered\View\MapsLayerDefinitionsProvider;
use SRF\Filtered\View\MapView;

/**
 * @covers \SRF\Filtered\View\MapView
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 6.0.0
 */
class MapViewTest extends \PHPUnit\Framework\TestCase {

	public function testConfigHasNoLayerKeysWhenLayersParameterIsEmpty() {
		$config = $this->newMapView( $this->mapViewParams() )->getJsConfig();

		$this->assertArrayNotHasKey( 'layers', $config );
		$this->assertArrayNotHasKey( 'layer definitions', $config );
		$this->assertSame( 'OpenStreetMap.Mapnik', $config['map provider'] );
	}

	public function testLayerNamesAreEmittedAsConfigLayersInGivenOrder() {
		$view = $this->newMapView(
			$this->mapViewParams( [ 'map view layers' => [ 'Historic Test', 'OpenStreetMap.Mapnik', 'Terrestris OSM WMS' ] ] ),
			$this->providerReturning( [] )
		);

		$config = $view->getJsConfig();

		$this->assertSame(
			[ 'Historic Test', 'OpenStreetMap.Mapnik', 'Terrestris OSM WMS' ],
			$config['layers']
		);
	}

	public function testMatchedDefinitionsAreEmittedWhileUnmatchedNamesPassThrough() {
		$matched = [
			'Historic Test' => [ 'url' => 'https://tiles/{z}/{x}/{y}.png', 'options' => [ 'maxZoom' => 19 ], 'wms' => false ],
			'Terrestris OSM WMS' => [ 'url' => 'https://ows/service', 'options' => [ 'layers' => 'OSM-WMS' ], 'wms' => true ],
		];

		$view = $this->newMapView(
			$this->mapViewParams( [ 'map view layers' => [ 'Historic Test', 'OpenStreetMap.Mapnik', 'Terrestris OSM WMS' ] ] ),
			$this->providerReturning( $matched )
		);

		$config = $view->getJsConfig();

		$this->assertSame(
			[ 'Historic Test', 'OpenStreetMap.Mapnik', 'Terrestris OSM WMS' ],
			$config['layers']
		);
		$this->assertSame( $matched, $config['layer definitions'] );
	}

	public function testLayerDefinitionsKeyIsOmittedWhenNoDefinitionMatches() {
		$view = $this->newMapView(
			$this->mapViewParams( [ 'map view layers' => [ 'OpenStreetMap.Mapnik' ] ] ),
			$this->providerReturning( [] )
		);

		$this->assertArrayNotHasKey( 'layer definitions', $view->getJsConfig() );
	}

	public function testConfigHasNoGeoJsonKeysWhenParameterIsEmpty() {
		$config = $this->newMapView( $this->mapViewParams() )->getJsConfig();

		$this->assertArrayNotHasKey( 'geojson', $config );
		$this->assertArrayNotHasKey( 'geojson source', $config );
	}

	public function testGeoJsonAndSourceAreEmittedWhenFetchSucceeds() {
		$geoJson = [ 'type' => 'FeatureCollection', 'features' => [ [ 'type' => 'Feature' ] ] ];

		$view = $this->newMapView(
			$this->mapViewParams( [ 'map view geojson' => 'Filtered Demo' ] ),
			null,
			$this->geoJsonProvider( true, $geoJson )
		);

		$config = $view->getJsConfig();

		$this->assertSame( $geoJson, $config['geojson'] );
		$this->assertSame( 'Filtered Demo', $config['geojson source'] );
	}

	public function testNoGeoJsonKeysAndNoErrorWhenFetchReturnsEmpty() {
		$view = $this->newMapView(
			$this->mapViewParams( [ 'map view geojson' => 'Missing Page' ] ),
			null,
			$this->geoJsonProvider( true, [] )
		);

		$config = $view->getJsConfig();

		$this->assertArrayNotHasKey( 'geojson', $config );
		$this->assertArrayNotHasKey( 'geojson source', $config );
		$this->assertCount( 0, $view->getQueryPrinter()->errors );
	}

	public function testErrorIsAddedWhenGeoJsonIsSetButMapsIsUnavailable() {
		$view = $this->newMapView(
			$this->mapViewParams( [ 'map view geojson' => 'Filtered Demo' ] ),
			null,
			$this->geoJsonProvider( false, [] )
		);

		$config = $view->getJsConfig();

		$this->assertArrayNotHasKey( 'geojson', $config );
		$this->assertCount( 1, $view->getQueryPrinter()->errors );
	}

	public function testInitErrorReportsMissingProviderWhenNoLayersAreConfigured() {
		$view = $this->newMapView( $this->mapViewParams() );
		$view->setMapProvider( '' );

		$this->assertSame( 'srf-filtered-map-provider-missing-error', $view->getInitError() );
	}

	public function testInitErrorIsSuppressedWhenLayersAreConfigured() {
		$view = $this->newMapView( $this->mapViewParams( [ 'map view layers' => [ 'Historic Test' ] ] ) );
		$view->setMapProvider( '' );

		$this->assertNull( $view->getInitError() );
	}

	public function testInitErrorIsNullWhenProviderIsSetAndNoLayers() {
		$view = $this->newMapView( $this->mapViewParams() );

		$this->assertNull( $view->getInitError() );
	}

	private function newMapView(
		array $params,
		?MapsLayerDefinitionsProvider $provider = null,
		?MapsGeoJsonProvider $geoJsonProvider = null
	): MapView {
		$results = [];
		$printer = $this->newRecordingPrinter();
		$view = new MapView( $results, $params, $printer );
		$view->setMapProvider( 'OpenStreetMap.Mapnik' );
		$view->setMapProviderDark( '' );

		if ( $provider !== null ) {
			$view->setLayerDefinitionsProvider( $provider );
		}

		if ( $geoJsonProvider !== null ) {
			$view->setGeoJsonProvider( $geoJsonProvider );
		}

		return $view;
	}

	private function newRecordingPrinter(): Filtered {
		return new class( null ) extends Filtered {
			public array $errors = [];

			public function addError( $errorMessage ): void {
				$this->errors[] = $errorMessage;
			}
		};
	}

	private function mapViewParams( array $overrides = [] ): array {
		return array_merge( [
			'map view marker position property' => '',
			'map view marker icon property' => '',
			'map view marker icons' => [],
			'map view height' => 'auto',
			'map view zoom' => '',
			'map view min zoom' => '',
			'map view max zoom' => '',
			'map view marker cluster' => true,
			'map view marker cluster max zoom' => '',
			'map view marker cluster radius' => '',
			'map view marker cluster zoom on click' => true,
			'map view layers' => [],
			'map view geojson' => '',
		], $overrides );
	}

	private function providerReturning( array $definitions ): MapsLayerDefinitionsProvider {
		return new class( $definitions ) extends MapsLayerDefinitionsProvider {
			public function __construct( private array $definitions ) {
			}

			public function getDefinitions( array $names ): array {
				return $this->definitions;
			}
		};
	}

	private function geoJsonProvider( bool $mapsAvailable, array $geoJson ): MapsGeoJsonProvider {
		return new class( $mapsAvailable, $geoJson ) extends MapsGeoJsonProvider {
			public function __construct( private bool $mapsAvailable, private array $geoJson ) {
			}

			public function mapsIsAvailable(): bool {
				return $this->mapsAvailable;
			}

			public function getGeoJson( string $location ): array {
				return $this->geoJson;
			}
		};
	}
}
