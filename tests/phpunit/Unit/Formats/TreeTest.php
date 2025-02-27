<?php

namespace SRF\Test;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use Parser;
use SMW\Tests\QueryPrinterRegistryTestCase;
use SMW\Tests\Utils\Mock\CoreMockObjectRepository;
use SMW\Tests\Utils\Mock\MockObjectBuilder;
use SMWQueryProcessor;
use SRF\Formats\Tree\TreeResultPrinter;

/**
 * Class TreeTest
 *
 * @since 2.5
 *
 * @ingroup SemanticResultFormats
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @author Stephan Gambke
 */
class TreeTest extends QueryPrinterRegistryTestCase {

	private $parser;
	private $title;

	private static $initial_parser;
	private static $initial_title;

	/**
	 * Keep the global state and restore it on tearDown to avoid influencing
	 * other tests in case this one fails in between.
	 */
	public static function setUpBeforeClass(): void {
		self::$initial_parser = MediaWikiServices::getInstance()->getParser();
		self::$initial_title = $GLOBALS['wgTitle'];
	}

	protected function tearDown(): void {
		$this->replaceParser( self::$initial_parser );
		$GLOBALS['wgTitle'] = self::$initial_title;

		parent::tearDown();
	}

	/**
	 * Returns the names of the formats being tested.
	 *
	 * @return string[]
	 */
	public function getFormats() {
		return [ 'tree' ];
	}

	/**
	 * Returns the name of the class being tested.
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRF\Formats\Tree\TreeResultPrinter';
	}

	/**
	 * @covers Tree getResult_NoParentProperty
	 */
	public function testGetResult_NoParentProperty() {
		$this->prepareGlobalState();

		$mockBuilder = new MockObjectBuilder();
		$mockBuilder->registerRepository( new CoreMockObjectRepository() );

		/** @var \PHPUnit_Framework_MockObject_MockObject $queryResult */
		$queryResult = $mockBuilder->newObject( 'QueryResult', [ 'getCount' => 1 ] );

		$queryResult->expects( $this->once() )
			->method( 'addErrors' )
			->willReturn( null );

		$params = SMWQueryProcessor::getProcessedParams( [ 'format' => 'tree' ], [] );

		$testObject = new TreeResultPrinter( 'tree' );

		$this->assertSame(
			null,
			$testObject->getResult( $queryResult, $params, SMW_OUTPUT_HTML ),
			'Result should be empty.'
		);

		// Restore GLOBAL state to ensure that preceding tests do not use a
		// mocked instance
		$this->replaceParser( $this->parser );
		$GLOBALS['wgTitle'] = $this->title;
	}

	protected function prepareGlobalState() {
		// Store current state
		$this->parser = MediaWikiServices::getInstance()->getParser();
		$this->title = $GLOBALS['wgTitle'];

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutput->expects( $this->any() )
			->method( 'getHeadItems' )
			->willReturn( [] );

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$parser->expects( $this->any() )
			->method( 'parse' )
			->willReturn( $parserOutput );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		// Careful!!
		$this->replaceParser( $parser );
		$GLOBALS['wgTitle'] = $title;
	}

	/**
	 * Replaces the global Parser service.
	 *
	 * @param Parser $parser
	 */
	protected function replaceParser( Parser $parser ) {
		// Direct access to the wgParser global was removed in SMW 4.0.0.
		if ( ExtensionRegistry::getInstance()->isLoaded( 'SemanticMediaWiki', '<4.0.0' ) ) {
			$GLOBALS['wgParser'] = $parser;
		} else {
			MediaWikiServices::getInstance()->disableService( 'Parser' );
			MediaWikiServices::getInstance()->redefineService(
				'Parser',
				static function () use ( $parser ) {
					return $parser;
				}
			);
		}
	}

	/**
	 * @return array
	 */
	protected function provideQueryParamsAndResults() {
		$mockBuilder = new MockObjectBuilder();
		$mockBuilder->registerRepository( new CoreMockObjectRepository() );

		/** @var \SMW\Query\Result\ResultArray[]|false $resultRow */
		$resultRow = $mockBuilder->newObject( 'ResultArray' );

		// $resultRow->add( $resultCell );

		$resultSet[] = [];

		$resultSet[] = $resultRow;

		/** @var array(\SMW\Query\Result\ResultArray[]|false) $resultSet */
		$resultSet[] = false;

		$queryResult = $mockBuilder->newObject(
			'QueryResult',
			[
				'getCount' => 1,
			]
		);

		$queryResult->expects( $this->any() )
			->method( 'getNext' )
			->will( call_user_func( [ $this, 'onConsecutiveCalls' ], $resultSet ) );

		$queryResult = $mockBuilder->newObject(
			'QueryResult',
			[
				'getCount' => 1,
			]
		);

		$params = SMWQueryProcessor::getProcessedParams( [ 'format' => 'tree' ], [] );

		$expected = '';

		return [ $queryResult, $params, $expected ];
	}
}
