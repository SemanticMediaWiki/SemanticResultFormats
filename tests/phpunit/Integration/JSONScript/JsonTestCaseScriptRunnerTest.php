<?php

namespace SRF\Tests\Integration\JSONScript;

use SMW\Tests\Integration\JSONScript\JsonTestCaseScriptRunnerTest as SMWJsonTestCaseScriptRunnerTest;
use SMW\Tests\JsonTestCaseFileHandler;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/tree/master/tests#write-integration-tests-using-json-script
 *
 * `JsonTestCaseScriptRunner` provisioned by SMW is a base class allowing to use a JSON
 * format to create test definitions with the objective to compose "real" content
 * and test integration with MediaWiki, Semantic MediaWiki, and Scribunto.
 *
 * The focus is on describing test definitions with its content and specify assertions
 * to control the expected base line.
 *
 * `JsonTestCaseScriptRunner` will handle the tearDown process and ensures that no test
 * data are leaked into a production system but requires an active DB connection.
 *
 * @group SRF
 * @group SMWExtension
 *
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author mwjames
 */
class JsonTestCaseScriptRunnerTest extends SMWJsonTestCaseScriptRunnerTest {

	/**
	 * @var ParserHtmlTestCaseProcessor
	 */
	private $parserHtmlTestCaseProcessor;

	/**
	 * @see \SMW\Tests\JsonTestCaseScriptRunner::getTestCaseLocation
	 * @return string
	 */
	protected function getTestCaseLocation() {
		return __DIR__ . '/TestCases';
	}

	protected function setUp() {
		parent::setUp();

		$htmlValidator = new HtmlValidator();

		$this->parserHtmlTestCaseProcessor = new ParserHtmlTestCaseProcessor(
			$this->getStore(),
			$htmlValidator
		);

	}

	protected function runTestCaseFile( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {
		parent::runTestCaseFile( $jsonTestCaseFileHandler );
		$this->doRunParserHtmlTests( $jsonTestCaseFileHandler );

	}

	/**
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	private function doRunParserHtmlTests( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'parser-html' ) as $case ) {

			if ( $jsonTestCaseFileHandler->requiredToSkipFor( $case, $this->connectorId ) ) {
				continue;
			}

			$this->parserHtmlTestCaseProcessor->process( $case );
		}
	}

}
