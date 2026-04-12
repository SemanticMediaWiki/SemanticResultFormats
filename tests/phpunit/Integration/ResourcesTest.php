<?php

namespace SRF\Tests\Integration;

use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader\Context;
use MediaWiki\ResourceLoader\ResourceLoader;

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
 * @license GPL-2.0-or-later
 * @author mwjames
 */
class ResourcesTest extends \PHPUnit\Framework\TestCase {

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
		$resourceLoader = MediaWikiServices::getInstance()->getResourceLoader();

		$context = Context::newDummyContext();
		$modules = $this->getSRFResourceModules();

		return [ [ $modules, $resourceLoader, $context ] ];
	}

	/**
	 * @covers Resources
	 * @dataProvider moduleDataProvider
	 */
	public function testModulesScriptsFilesAreAccessible( $modules, ResourceLoader $resourceLoader, $context ) {
		if ( version_compare( MW_VERSION, '1.41.0', '>=' ) ) {
			foreach ( $modules as $name => $values ) {
				$module = $resourceLoader->getModule( $name );
				$scripts = $module->getScript( $context );
				foreach ( $scripts['plainScripts'] as $key => $value ) {
					$this->assertIsString( $value['content'] );
				}
			}
		} else {
			foreach ( $modules as $name => $values ) {
				$module = $resourceLoader->getModule( $name );
				$scripts = $module->getScript( $context );
				$this->assertIsString( $scripts );
			}
		}
	}

	/**
	 * @covers Resources
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
