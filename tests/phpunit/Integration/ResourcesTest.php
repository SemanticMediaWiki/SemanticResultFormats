<?php

namespace SRF\Tests\Integration;

use ResourceLoader;
use ResourceLoaderContext;
use ResourceLoaderModule;

/**
 * Tests for resource definitions and files
 *
 * @file
 * @since 1.9
 *
 * @ingroup SRF
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */
class ResourcesTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Helper method to load resources only valid for this extension
	 *
	 * @return array
	 */
	private function getSRFResourceModules() {
		global $srfgIP;
		return include $srfgIP . '/' . 'Resources.php';
	}

	public function moduleDataProvider() {
		$resourceLoader = new ResourceLoader();
		$context = ResourceLoaderContext::newDummyContext();
		$modules = $this->getSRFResourceModules();

		return [ [ $modules, $resourceLoader, $context ] ];
	}

	/**
	 * @dataProvider moduleDataProvider
	 */
	public function testModulesScriptsFilesAreAccessible( $modules, ResourceLoader $resourceLoader, $context ) {
		foreach ( $modules as $name => $values ) {
			$module = $resourceLoader->getModule( $name );
			$scripts = $module->getScript( $context );
			$this->assertInternalType( 'string', $scripts );
		}
	}

	/**
	 * Test styles accessibility
	 *
	 * @dataProvider moduleDataProvider
	 */
	public function testModulesStylesFilesAreAccessible( $modules, ResourceLoader $resourceLoader, $context ) {

		foreach ( $modules as $name => $values ) {

			// Get module details
			$module = $resourceLoader->getModule( $name );

			// Get styles per module
			$styles = $module->getStyles( $context );
			$this->assertContainsOnly( 'string', $styles );
		}
	}

}