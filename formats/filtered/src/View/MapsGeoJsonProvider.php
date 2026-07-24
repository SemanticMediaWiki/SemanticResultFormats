<?php

namespace SRF\Filtered\View;

use Maps\MapsFactory;
use MediaWiki\Registration\ExtensionRegistry;

/**
 * Bridges the filtered map view to the Maps extension's GeoJSON fetcher, which resolves a
 * GeoJson: namespace page name or a URL to the parsed GeoJSON array (guarding URLs against
 * SSRF and returning an empty array on any failure — missing page, invalid JSON, blocked URL).
 * When Maps is absent or too old to provide the fetcher, no GeoJSON is returned and the map
 * view emits no overlay.
 */
class MapsGeoJsonProvider {

	/**
	 * @return array The parsed GeoJSON for the given location, or [] when Maps is unavailable
	 *         or the location cannot be resolved.
	 */
	public function getGeoJson( string $location ): array {
		$fetcher = $this->getGeoJsonFetcher();

		if ( $fetcher === null ) {
			return [];
		}

		// Maps' GeoJsonFetcher::normalizeJson declares an array return type but can emit a scalar
		// for a URL whose body is scalar JSON, so parse() may raise a TypeError (an \Error, hence
		// \Throwable rather than \Exception). Degrade to no overlay instead of surfacing a fatal.
		try {
			return $fetcher->parse( $location );
		} catch ( \Throwable ) {
			return [];
		}
	}

	public function mapsIsAvailable(): bool {
		return $this->getGeoJsonFetcher() !== null;
	}

	/**
	 * @return object|null Maps' GeoJsonFetcher, which exposes parse( string ): array, or null
	 *         when the Maps extension is not loaded or predates the newGeoJsonFetcher() factory.
	 */
	protected function getGeoJsonFetcher() {
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'Maps' ) ) {
			return null;
		}

		// @phan-suppress-next-line PhanUndeclaredClassReference Maps is an optional dependency
		if ( !method_exists( MapsFactory::class, 'newGeoJsonFetcher' ) ) {
			return null;
		}

		// @phan-suppress-next-line PhanUndeclaredClassMethod Maps is an optional dependency, guarded above
		return MapsFactory::globalInstance()->newGeoJsonFetcher();
	}
}
