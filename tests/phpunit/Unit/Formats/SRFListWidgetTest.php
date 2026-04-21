<?php

namespace SRF\Tests\Unit\Formats;

use MediaWiki\Linker\Linker;
use PHPUnit\Framework\TestCase;
use SMW\MediaWiki\Renderer\WikitextTemplateRenderer;
use SMW\Query\PrintRequest;
use SMW\Query\QueryResult;
use SMW\Query\Result\ResultArray;
use SMW\Query\ResultPrinters\ListResultPrinter\ParameterDictionary;
use SMW\Query\ResultPrinters\ListResultPrinter\TemplateRendererFactory;
use SMW\Query\ResultPrinters\ListResultPrinter\TemplateRowBuilder;
use SMW\Query\ResultPrinters\ListResultPrinter\ValueTextsBuilder;
use SMWQuery;
use SRFListWidget;

/**
 * Unit tests for the listwidget template parameter pipeline.
 *
 * These tests run without a MediaWiki parser or SMW store. They verify:
 *  1. SRFListWidget exposes the `template` param definition.
 *  2. TemplateRowBuilder (SMW) generates a template call that includes
 *     the subject title as positional argument 1.
 *
 * @covers SRFListWidget
 * @covers \SMW\Query\ResultPrinters\ListResultPrinter\TemplateRowBuilder
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @license GPL-2.0-or-later
 */
class SRFListWidgetTest extends TestCase {

	/**
	 * SRFListWidget must expose the `template` parameter so that #ask can
	 * pass a template name to the format.
	 *
	 * Without this declaration, $this->params['template'] would be undefined
	 * and no template call would ever be generated.
	 */
	public function testGetParamDefinitionsIncludesTemplate(): void {
		$printer = new SRFListWidget( 'listwidget' );
		$params = $printer->getParamDefinitions( [] );

		$this->assertArrayHasKey( 'template', $params,
			'SRFListWidget must declare a "template" parameter' );
		$this->assertSame( '', $params['template']['default'],
			'The default value for "template" must be an empty string' );
	}

	/**
	 * TemplateRowBuilder must pass the subject title as positional argument 1
	 * to the template call so that {{{1}}} in the template resolves to the
	 * page title.
	 *
	 * This test isolates TemplateRowBuilder from the SMW store by mocking
	 * TemplateRendererFactory.getTemplateRenderer() to return a plain
	 * WikitextTemplateRenderer, and by mocking ValueTextsBuilder to return
	 * a known subject title string.
	 *
	 * If this test fails (e.g. "TestPage" is absent from $result), then the
	 * bug is in SMW's TemplateRowBuilder and needs to be fixed upstream.
	 * If it passes, the bug is outside this class (parser processing, param
	 * passing, etc.) and should be investigated there.
	 */
	public function testTemplateRowBuilderPassesSubjectTitleAsPositionalArg(): void {
		// A real WikitextTemplateRenderer — its render() output is the
		// wikitext template call, e.g. {{MyTemplate|\n|1=TestPage\n|#=0\n...}}
		$renderer = new WikitextTemplateRenderer();

		// Mock the factory so that getTemplateRenderer() returns a fresh clone
		// without triggering getRowCount() → SMW store access.
		$factory = $this->createMock( TemplateRendererFactory::class );
		$factory->method( 'getTemplateRenderer' )
			->willReturnCallback( static fn () => clone $renderer );

		// Mock ValueTextsBuilder to return a known title string.
		// In the real code this calls $dataValue->getShortText() on the result.
		$vtb = $this->createMock( ValueTextsBuilder::class );
		$vtb->method( 'getValuesText' )->willReturn( 'TestPage' );

		// Mock PrintRequest with empty label → subject column → positional arg 1.
		$printReq = $this->createMock( PrintRequest::class );
		$printReq->method( 'getLabel' )->willReturn( '' );

		// Mock ResultArray — only getPrintRequest() is used by getFieldLabel().
		$field = $this->createMock( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printReq );

		// Wire up TemplateRowBuilder.
		$rowBuilder = new TemplateRowBuilder( $factory );
		$rowBuilder->setValueTextsBuilder( $vtb );

		$config = new ParameterDictionary();
		$config->set( 'template', 'MyTestTemplate' );
		// 'named args' is intentionally NOT set (mirrors SRFListWidget behaviour)
		// → getFieldLabel() falls back to positional numbering for empty labels.
		$rowBuilder->setConfiguration( $config );

		$result = $rowBuilder->getRowText( [ $field ], 0 );

		$this->assertStringContainsString( '{{MyTestTemplate', $result,
			'TemplateRowBuilder must generate a wikitext template call' );

		$this->assertStringContainsString( 'TestPage', $result,
			'The subject title must appear as an argument in the template call' );

		// The subject column has an empty label → getFieldLabel() returns 1
		// → WikitextTemplateRenderer stores it as "1=TestPage".
		$this->assertStringContainsString( '1=TestPage', $result,
			'Subject title must be passed as named arg "1=" so that {{{1}}} resolves to it' );
	}

	/**
	 * When `named args` is explicitly false, positional arg numbering must
	 * still be used (same result as when the key is absent).
	 */
	public function testTemplateRowBuilderWithExplicitNamedArgsFalse(): void {
		$renderer = new WikitextTemplateRenderer();

		$factory = $this->createMock( TemplateRendererFactory::class );
		$factory->method( 'getTemplateRenderer' )
			->willReturnCallback( static fn () => clone $renderer );

		$vtb = $this->createMock( ValueTextsBuilder::class );
		$vtb->method( 'getValuesText' )->willReturn( 'AnotherPage' );

		$printReq = $this->createMock( PrintRequest::class );
		$printReq->method( 'getLabel' )->willReturn( '' );

		$field = $this->createMock( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printReq );

		$rowBuilder = new TemplateRowBuilder( $factory );
		$rowBuilder->setValueTextsBuilder( $vtb );

		$config = new ParameterDictionary();
		$config->set( 'template', 'MyTestTemplate' );
		$config->set( 'named args', false );
		$rowBuilder->setConfiguration( $config );

		$result = $rowBuilder->getRowText( [ $field ], 0 );

		$this->assertStringContainsString( '1=AnotherPage', $result,
			'With named args = false, subject must still map to positional arg 1' );
	}

	/**
	 * With named args = true and a non-empty label, the label must be the
	 * template argument name.
	 */
	public function testTemplateRowBuilderUsesLabelWhenNamedArgsTrue(): void {
		$renderer = new WikitextTemplateRenderer();

		$factory = $this->createMock( TemplateRendererFactory::class );
		$factory->method( 'getTemplateRenderer' )
			->willReturnCallback( static fn () => clone $renderer );

		$vtb = $this->createMock( ValueTextsBuilder::class );
		$vtb->method( 'getValuesText' )->willReturn( 'SomeValue' );

		$printReq = $this->createMock( PrintRequest::class );
		$printReq->method( 'getLabel' )->willReturn( 'MyProp' );

		$field = $this->createMock( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printReq );

		$rowBuilder = new TemplateRowBuilder( $factory );
		$rowBuilder->setValueTextsBuilder( $vtb );

		$config = new ParameterDictionary();
		$config->set( 'template', 'MyTestTemplate' );
		$config->set( 'named args', true );
		$rowBuilder->setConfiguration( $config );

		$result = $rowBuilder->getRowText( [ $field ], 0 );

		$this->assertStringContainsString( 'MyProp=SomeValue', $result,
			'With named args = true and a non-empty label, label must be the arg name' );
	}

	// -------------------------------------------------------------------------
	// SRFListWidget::getResultText() end-to-end tests
	// -------------------------------------------------------------------------

	/**
	 * Build a minimal SRFListWidget instance with $this->params pre-set.
	 *
	 * We use an anonymous subclass to bypass the normal ResultPrinter
	 * parameter-processing bootstrap while still exercising getResultText().
	 *
	 * @param array $params Values that end up in $this->params (e.g. 'template').
	 */
	private function newListWidgetWithParams( array $params ): SRFListWidget {
		$defaults = [
			'template'    => '',
			'listtype'    => 'unordered',
			'widget'      => 'alphabet',
			'pageitems'   => 5,
			'class'       => '',
			'offset'      => 0,
			'link-first'  => true,
			'link-others' => false,
			'show-headers' => SMW_HEADERS_SHOW,
		];

		return new class( array_merge( $defaults, $params ) ) extends SRFListWidget {

			private array $testParams;

			public function __construct( array $testParams ) {
				// Do NOT call parent::__construct — avoids full MW bootstrap.
				$this->testParams = $testParams;
			}

			/**
			 * Public wrapper so tests can call the protected getResultText().
			 * Also injects the required base-class fields.
			 */
			public function invokeGetResultText( QueryResult $res, int $outputmode ): string {
				$this->params       = $this->testParams;
				$this->mLinkFirst   = $this->testParams['link-first'];
				$this->mLinkOthers  = $this->testParams['link-others'];
				$this->mShowHeaders = $this->testParams['show-headers'];
				$this->mLinker      = new Linker();
				return $this->getResultText( $res, $outputmode );
			}
		};
	}

	/**
	 * Build a mock QueryResult that returns one row with one field whose
	 * DataValue::getShortText() returns $pageTitle.
	 */
	private function newQueryResultMock( string $pageTitle ): QueryResult {
		// DataValue mock — getShortText() returns the page title.
		$dataValue = $this->createMock( \SMWDataValue::class );
		$dataValue->method( 'getShortText' )->willReturn( $pageTitle );
		$dataValue->method( 'getLongText' )->willReturn( $pageTitle );

		// ResultArray (one column / subject field).
		$printReq = $this->createMock( PrintRequest::class );
		$printReq->method( 'getLabel' )->willReturn( '' );
		$printReq->method( 'getMode' )->willReturn( PrintRequest::PRINT_THIS );

		$field = $this->createMock( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printReq );
		// getNextDataValue(): return $dataValue on first call, false on second.
		$field->method( 'getNextDataValue' )
			->willReturnOnConsecutiveCalls( $dataValue, false );
		$field->method( 'reset' )->willReturn( null );

		// SMWQuery stub — ListResultBuilder reads getOffset() and the query string.
		$query = $this->createMock( SMWQuery::class );
		$query->method( 'getOffset' )->willReturn( 0 );
		$query->method( 'getLimit' )->willReturn( 50 );
		$query->method( 'getQueryString' )->willReturn( '' );

		// Store mock — only needed when template is set: TemplateRendererFactory
		// calls getRowCount() → getStore()->getQueryResult() to fill #rowcount.
		$store = $this->createMock( \SMW\Store::class );
		$store->method( 'getQueryResult' )->willReturn( 1 );

		// QueryResult mock — getNext() returns one row, then false.
		$queryResult = $this->createMock( QueryResult::class );
		$queryResult->method( 'getNext' )
			->willReturnOnConsecutiveCalls( [ $field ], false );
		$queryResult->method( 'reset' )->willReturn( false );
		$queryResult->method( 'getCount' )->willReturn( 1 );
		$queryResult->method( 'hasFurtherResults' )->willReturn( false );
		$queryResult->method( 'getQuery' )->willReturn( $query );
		$queryResult->method( 'getQueryString' )->willReturn( '' );
		$queryResult->method( 'getStore' )->willReturn( $store );

		return $queryResult;
	}

	/**
	 * When `template` is set, SRFListWidget::getResultText() must:
	 *  (a) set $this->hasTemplates = true  (triggers wikitext post-processing
	 *      in the parent ResultPrinter::handleNonFileResult())
	 *  (b) pass the template name to ListResultBuilder so that each row is
	 *      rendered as a {{TemplateName|…}} call rather than plain HTML.
	 *
	 * The output of getResultText() is raw wikitext (not yet parsed), so we
	 * can assert the template call is present before any wiki-parser runs.
	 */
	public function testGetResultTextWithTemplateSetsHasTemplatesAndContainsTemplateCall(): void {
		$printer = $this->newListWidgetWithParams( [ 'template' => 'MyListTemplate' ] );
		$queryResult = $this->newQueryResultMock( 'TestPageTitle' );

		$html = $printer->invokeGetResultText( $queryResult, SMW_OUTPUT_WIKI );

		// (a) hasTemplates must be true after the call.
		$ref = new \ReflectionProperty( \SMW\Query\ResultPrinters\ResultPrinter::class, 'hasTemplates' );
		$ref->setAccessible( true );
		$this->assertTrue( $ref->getValue( $printer ),
			'$this->hasTemplates must be true when a template is configured' );

		// (b) The output must contain a wikitext template call for the template name.
		$this->assertStringContainsString( '{{MyListTemplate', $html,
			'getResultText() must embed a {{TemplateName...}} call when template param is set' );

		// (c) The subject title must appear inside the template call.
		$this->assertStringContainsString( 'TestPageTitle', $html,
			'The subject page title must be passed to the template' );
	}

	/**
	 * Without a template, hasTemplates must remain false and the output must
	 * contain the plain HTML list structure (no template calls).
	 */
	public function testGetResultTextWithoutTemplateDoesNotSetHasTemplates(): void {
		$printer = $this->newListWidgetWithParams( [ 'template' => '' ] );
		$queryResult = $this->newQueryResultMock( 'TestPageTitle' );

		$printer->invokeGetResultText( $queryResult, SMW_OUTPUT_WIKI );

		$ref = new \ReflectionProperty( \SMW\Query\ResultPrinters\ResultPrinter::class, 'hasTemplates' );
		$ref->setAccessible( true );
		$this->assertFalse( $ref->getValue( $printer ),
			'$this->hasTemplates must be false when no template is configured' );
	}
}
