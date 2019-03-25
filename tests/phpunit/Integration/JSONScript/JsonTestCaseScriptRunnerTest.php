<?php

namespace SMW\Tests\Integration\JSONScript;
use SMW\ApplicationFactory;
use SMW\DataValueFactory;
use SMW\EventHandler;
use SMW\PropertySpecificationLookup;
use SMW\SPARQLStore\TurtleTriplesBuilder;
use SMW\Tests\JsonTestCaseFileHandler;
use SMW\Tests\JsonTestCaseScriptRunner;

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
class JsonTestCaseScriptRunnerTest extends JsonTestCaseScriptRunner {

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

	/**
	 * @see JsonTestCaseScriptRunner::getDependencyDefinitions
	 */
	protected function getDependencyDefinitions() {
		return [
			'Mermaid' => function( $val, &$reason ) {
				if ( !defined( 'SM_VERSION' ) ) {
					$reason = "Dependency: Mermaid as requirement is not available!";
					return false;
				}
				list( $compare, $requiredVersion ) = explode( ' ', $val );
				$version = SM_VERSION;
				if ( !version_compare( $version, $requiredVersion, $compare ) ) {
					$reason = "Dependency: Required version of Mermaid ($requiredVersion $compare $version) is not available!";
					return false;
				}
				return true;
			}
		];
	}


	/**
	 * @see JsonTestCaseScriptRunner::runTestCaseFile
	 *
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	protected function runTestCaseFile( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {
		$this->checkEnvironmentToSkipCurrentTest( $jsonTestCaseFileHandler );
		// Setup
		$this->prepareTest( $jsonTestCaseFileHandler );
		// Before test execution
		$this->doRunBeforeTest( $jsonTestCaseFileHandler );
		// Run test cases
		$this->doRunParserTests( $jsonTestCaseFileHandler );
		$this->doRunSpecialTests( $jsonTestCaseFileHandler );
		$this->doRunRdfTests( $jsonTestCaseFileHandler );
		$this->doRunQueryTests( $jsonTestCaseFileHandler );
		$this->doRunParserHtmlTests( $jsonTestCaseFileHandler );
		$this->doRunApiTests( $jsonTestCaseFileHandler );
	}

}
