<?php

namespace SRF\Tests\Filtered;

use SRF\Filtered\View\MapsGeoJsonProvider;

/**
 * @covers \SRF\Filtered\View\MapsGeoJsonProvider
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 6.0.0
 */
class MapsGeoJsonProviderTest extends \PHPUnit\Framework\TestCase {

	public function testReturnsNoGeoJsonWhenMapsDoesNotProvideFetcher() {
		$provider = new class extends MapsGeoJsonProvider {
			protected function getGeoJsonFetcher() {
				return null;
			}
		};

		$this->assertSame( [], $provider->getGeoJson( 'GeoJson:Demo' ) );
	}

	public function testForwardsLocationAndReturnsFetchedGeoJson() {
		$fetcher = new class {
			public string $seenLocation = '';

			public function parse( string $location ): array {
				$this->seenLocation = $location;
				return [ 'type' => 'FeatureCollection', 'features' => [] ];
			}
		};

		$geoJson = $this->providerWithFetcher( $fetcher )->getGeoJson( 'GeoJson:Demo' );

		$this->assertSame( 'GeoJson:Demo', $fetcher->seenLocation );
		$this->assertSame( [ 'type' => 'FeatureCollection', 'features' => [] ], $geoJson );
	}

	public function testReturnsNoGeoJsonWhenTheFetcherThrows() {
		$fetcher = new class {
			public function parse( string $location ): array {
				throw new \TypeError( 'Maps GeoJsonFetcher::normalizeJson is not total for scalar JSON bodies' );
			}
		};

		$this->assertSame( [], $this->providerWithFetcher( $fetcher )->getGeoJson( 'https://example.org/scalar.json' ) );
	}

	public function testMapsIsUnavailableWhenFetcherIsAbsent() {
		$provider = new class extends MapsGeoJsonProvider {
			protected function getGeoJsonFetcher() {
				return null;
			}
		};

		$this->assertFalse( $provider->mapsIsAvailable() );
	}

	public function testMapsIsAvailableWhenFetcherIsPresent() {
		$fetcher = new class {
			public function parse( string $location ): array {
				return [];
			}
		};

		$this->assertTrue( $this->providerWithFetcher( $fetcher )->mapsIsAvailable() );
	}

	private function providerWithFetcher( object $fetcher ): MapsGeoJsonProvider {
		return new class( $fetcher ) extends MapsGeoJsonProvider {
			public function __construct( private object $fetcher ) {
			}

			protected function getGeoJsonFetcher() {
				return $this->fetcher;
			}
		};
	}
}
