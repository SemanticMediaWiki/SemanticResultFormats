<?php

namespace SRF\Tests\Integration\JSONScript;

use SMW\DIWikiPage;
use SMW\Store;
use SMW\Tests\Utils\UtilityFactory;

/**
 * @group semantic-result-formats
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author Stephan Gambke
 */
class ParserHtmlTestCaseProcessor extends \PHPUnit_Framework_TestCase {

	/**
	 * @var HtmlValidator
	 */
	private $htmlValidator;
	/**
	 * @var Store
	 */
	private $store;

	/**
	 * ParserHtmlTestCaseProcessor constructor.
	 * @param Store $store
	 * @param HtmlValidator $htmlValidator
	 */
	public function __construct( Store $store, HtmlValidator $htmlValidator ) {

		parent::__construct();

		$this->htmlValidator = $htmlValidator;
		$this->store = $store;
	}

	/**
	 * @param array $case
	 */
	public function process( array $case ) {

		if ( !isset( $case[ 'subject' ] ) ) {
			return;
		}

		if ( isset( $case[ 'about' ] ) ) {
			$this->setName( $case[ 'about' ] );
		}

		$this->assertParserHtmlOutputForCase( $case );
	}

	/**
	 * @param $case
	 */
	private function assertParserHtmlOutputForCase( $case ) {

		if ( !isset( $case[ 'assert-output' ] ) ) {
			return;
		}

		$outputText = $this->getOutputText( $case );

		if ( $this->isSetAndTrueish( $case[ 'assert-output' ], 'to-be-valid-html' ) ) {
			$this->htmlValidator->assertThatHtmlIsValid(
				$outputText,
				$case[ 'about' ]
			);
		}

		if ( $this->isSetAndTrueish( $case[ 'assert-output' ], 'to-contain' ) ) {
			$this->htmlValidator->assertThatHtmlContains(
				$case[ 'assert-output' ][ 'to-contain' ],
				$outputText,
				$case[ 'about' ]
			);
		}
	}

	/**
	 * @param array $case
	 * @return string
	 */
	private function getOutputText( $case ) {
		$subject = DIWikiPage::newFromText(
			$case[ 'subject' ],
			isset( $case[ 'namespace' ] ) ? constant( $case[ 'namespace' ] ) : NS_MAIN
		);

		$parserOutput = UtilityFactory::getInstance()->newPageReader()->getEditInfo( $subject->getTitle() )->output;

		if ( !$this->isSetAndTrueish( $case[ 'assert-output' ], [ 'withOutputPageContext', 'onPageView' ] ) ) {
			return $parserOutput->getText();
		}

		$context = new \RequestContext();
		$context->setTitle( $subject->getTitle() );

		if ( $this->isSetAndTrueish( $case[ 'assert-output' ], 'withOutputPageContext' ) ) {
			// Ensures the OutputPageBeforeHTML hook is run
			$context->getOutput()->addParserOutput( $parserOutput );
		} else {
			\Article::newFromTitle( $subject->getTitle(), $context )->view();
		}

		return $context->getOutput()->getHTML();

	}

	/**
	 * @param $array
	 * @param string | string[] $keys
	 * @return bool
	 */
	private function isSetAndTrueish( $array, $keys ) {
		$keys = (array)$keys;
		return array_reduce( $keys, function ( $carry, $key ) use ( $array ) { return $carry || isset( $array[ $key ] ) && $array[ $key ]; }, false );
	}

}
