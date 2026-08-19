<?php

namespace SRF\Tests\Filtered;

use MediaWikiIntegrationTestCase;
use SRF\Filtered\Filtered;
use SRF\Filtered\Hooks;

/**
 * Regression test for https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1039
 *
 * When a page contains more than one {{#ask:format=filtered}} instance, each instance
 * used to call ParserOutput::setJsConfigVar( 'srfFilteredConfig', $accumulatedConfig )
 * with a different array value for the same key, which MediaWiki core rejects with
 * "Multiple conflicting values given for srfFilteredConfig" (InvalidArgumentException).
 *
 * @covers \SRF\Filtered\Filtered
 * @covers \SRF\Filtered\Hooks
 * @group semantic-result-formats
 *
 * @author gesinn-it-gea
 */
class MultipleFilteredInstancesTest extends MediaWikiIntegrationTestCase {

	private function addConfigToOutput( Filtered $printer, $id, array $config ) {
		$method = new \ReflectionMethod( Filtered::class, 'addConfigToOutput' );
		$method->setAccessible( true );
		$method->invoke( $printer, $id, $config );
	}

	public function testTwoFilteredInstancesOnSamePage_doNotThrow() {
		$parserOutput = new \ParserOutput();
		$parser = $this->createMock( \Parser::class );
		$parser->method( 'getOutput' )->willReturn( $parserOutput );
		// Filtered::getParser() replaces the parser with a fresh real one (and a fresh,
		// empty ParserOutput) whenever getOptions() is null — see issue #802. Stub it so
		// this test's mock, and the $parserOutput above, are actually used.
		$parser->method( 'getOptions' )->willReturn( $this->createStub( \ParserOptions::class ) );

		$firstInstance = new Filtered( null );
		$firstInstance->setParser( $parser );

		$secondInstance = new Filtered( null );
		$secondInstance->setParser( $parser );

		// Simulate two independent #ask calls with format=filtered on the same page.
		$this->addConfigToOutput( $firstInstance, 'filtered-1', [ 'views' => [ 'list' ] ] );
		$this->addConfigToOutput( $secondInstance, 'filtered-2', [ 'views' => [ 'table' ] ] );

		$mergedConfig = $parserOutput->getExtensionData( 'srf-filtered-config' );
		$this->assertSame(
			[
				'filtered-1' => [ 'views' => [ 'list' ] ],
				'filtered-2' => [ 'views' => [ 'table' ] ],
			],
			$mergedConfig
		);

		// This is the call that used to throw InvalidArgumentException before the fix,
		// because Filtered::addConfigToOutput no longer calls setJsConfigVar() directly.
		$outputPage = new \OutputPage( \RequestContext::getMain() );
		Hooks::onOutputPageParserOutput( $outputPage, $parserOutput );

		$this->assertSame( $mergedConfig, $outputPage->getProperty( 'srf-filtered-config' ) );
		$this->assertSame( $mergedConfig, $outputPage->getJsConfigVars()['srfFilteredConfig'] );
	}
}
