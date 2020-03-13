<?php

namespace SRF\Test;

use SMW\Test\QueryPrinterRegistryTestCase;
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
	public static function setUpBeforeClass() {
		self::$initial_parser = $GLOBALS['wgParser'];
		self::$initial_title = $GLOBALS['wgTitle'];
	}

	protected function tearDown() {
		$GLOBALS['wgParser'] = self::$initial_parser;
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
	 */
	public function testGetResult_NoParentProperty() {

		$this->prepareGlobalState();

		$mockBuilder = new MockObjectBuilder();
		$mockBuilder->registerRepository( new CoreMockObjectRepository() );

		/** @var \PHPUnit_Framework_MockObject_MockObject $queryResult */
		$queryResult = $mockBuilder->newObject( 'QueryResult', [ 'getCount' => 1 ] );

		$queryResult->expects( $this->once() )
			->method( 'addErrors' )
			->will( $this->returnValue( null ) );

		$params = SMWQueryProcessor::getProcessedParams( [ 'format' => 'tree' ], [] );

		$testObject = new TreeResultPrinter( 'tree' );

		$this->assertEquals(
			'',
			$testObject->getResult( $queryResult, $params, SMW_OUTPUT_HTML ),
			'Result should be empty.'
		);

		// Restore GLOBAL state to ensure that preceding tests do not use a
		// mocked instance
		$GLOBALS['wgParser'] = $this->parser;
		$GLOBALS['wgTitle'] = $this->title;
	}

	protected function prepareGlobalState() {

		// Store current state
		$this->parser = $GLOBALS['wgParser'];
		$this->title = $GLOBALS['wgTitle'];

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutput->expects( $this->any() )
			->method( 'getHeadItems' )
			->will( $this->returnValue( [] ) );

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$parser->expects( $this->any() )
			->method( 'parse' )
			->will( $this->returnValue( $parserOutput ) );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		// Careful!!
		$GLOBALS['wgParser'] = $parser;
		$GLOBALS['wgTitle'] = $title;
	}

	/**
	 * @return array
	 */
	protected function provideQueryParamsAndResults() {
		$mockBuilder = new MockObjectBuilder();
		$mockBuilder->registerRepository( new CoreMockObjectRepository() );

		/** @var \SMWResultArray[]|false $resultRow */
		$resultRow = $mockBuilder->newObject( 'ResultArray' );

		//$resultRow->add( $resultCell );

		$resultSet[] = [];

		$resultSet[] = $resultRow;

		/** @var array(SMWResultArray[]|false) $resultSet */
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
