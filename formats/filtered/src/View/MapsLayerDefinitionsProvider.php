<?php

namespace SRF\Filtered\View;

use Maps\MapsFactory;
use MediaWiki\Registration\ExtensionRegistry;

/**
 * Bridges the filtered map view to the custom Leaflet base-layer definitions the Maps
 * extension exposes through $egMapsLeafletLayerDefinitions. When Maps is absent or too old
 * to provide the accessor, no definitions are returned and the map view falls back to
 * treating every layer name as a leaflet-providers provider string.
 */
class MapsLayerDefinitionsProvider {

	/**
	 * @param string[] $names
	 * @return array<string, array{url: string, options: array, wms: bool}> Definitions for the
	 *         requested names that Maps defines. Names Maps does not know about are omitted.
	 */
	public function getDefinitions( array $names ): array {
		$layerDefinitions = $this->getMapsLayerDefinitions();

		if ( $layerDefinitions === null ) {
			return [];
		}

		return $layerDefinitions->getDefinitions( $names );
	}

	/**
	 * @return object|null Maps' LeafletLayerDefinitions, which exposes
	 *         getDefinitions( string[] ): array, or null when the Maps extension is not loaded
	 *         or predates the getLeafletLayerDefinitions() accessor.
	 */
	protected function getMapsLayerDefinitions() {
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'Maps' ) ) {
			return null;
		}

		// @phan-suppress-next-line PhanUndeclaredClassReference Maps is an optional dependency
		if ( !method_exists( MapsFactory::class, 'getLeafletLayerDefinitions' ) ) {
			return null;
		}

		// @phan-suppress-next-line PhanUndeclaredClassMethod Maps is an optional dependency, guarded above
		return MapsFactory::globalInstance()->getLeafletLayerDefinitions();
	}
}
