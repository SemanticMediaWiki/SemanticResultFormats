<?php

namespace SRF\Tests\Integration\JSONScript;

use SMW\Tests\Integration\JSONScript\JsonTestCaseScriptRunnerTest as SMWJsonTestCaseScriptRunnerTest;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/tree/master/tests#write-integration-tests-using-json-script
 *
 * `JsonTestCaseScriptRunner` provisioned by SMW is a base class allowing to use a JSON
 * format to create test definitions with the objective to compose "real" content
 * and test integration with MediaWiki, Semantic MediaWiki, and Scribunto.
 *
 * @group SRF
 * @group SMWExtension
 *
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author Stephan Gambke
 */
class JsonTestCaseScriptRunnerTest extends SMWJsonTestCaseScriptRunnerTest {

	/**
	 * @see \SMW\Tests\JsonTestCaseScriptRunner::getTestCaseLocation
	 * @return string
	 */
	protected function getTestCaseLocation() {
		return __DIR__ . '/TestCases';
	}

	/**
	 * @return string[]
	 * @since 3.0
	 */
	protected function getPermittedSettings() {
		$settings = parent::getPermittedSettings();

		$settings[] = 'srfgMapProvider';

		return $settings;
	}

}
