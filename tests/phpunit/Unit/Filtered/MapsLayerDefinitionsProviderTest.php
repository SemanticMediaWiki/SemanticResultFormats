<?php

namespace SRF\Tests\Filtered;

use SRF\Filtered\View\MapsLayerDefinitionsProvider;

/**
 * @covers \SRF\Filtered\View\MapsLayerDefinitionsProvider
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 6.0.0
 */
class MapsLayerDefinitionsProviderTest extends \PHPUnit\Framework\TestCase {

	public function testReturnsNoDefinitionsWhenMapsDoesNotProvideLayerDefinitions() {
		$provider = new class extends MapsLayerDefinitionsProvider {
			protected function getMapsLayerDefinitions() {
				return null;
			}
		};

		$this->assertSame( [], $provider->getDefinitions( [ 'Historic Test' ] ) );
	}

	public function testForwardsRequestedNamesAndReturnsMapsDefinitions() {
		$mapsDefinitions = new class {
			public array $seenNames = [];

			public function getDefinitions( array $names ): array {
				$this->seenNames = $names;
				return [ 'Historic Test' => [ 'url' => 'u', 'options' => [], 'wms' => false ] ];
			}
		};

		$provider = new class( $mapsDefinitions ) extends MapsLayerDefinitionsProvider {
			public function __construct( private object $mapsDefinitions ) {
			}

			protected function getMapsLayerDefinitions() {
				return $this->mapsDefinitions;
			}
		};

		$definitions = $provider->getDefinitions( [ 'Historic Test', 'Unknown Name' ] );

		$this->assertSame( [ 'Historic Test', 'Unknown Name' ], $mapsDefinitions->seenNames );
		$this->assertSame(
			[ 'Historic Test' => [ 'url' => 'u', 'options' => [], 'wms' => false ] ],
			$definitions
		);
	}
}
