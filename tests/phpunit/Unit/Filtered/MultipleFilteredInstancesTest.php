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
 * with a different array value for the same key (now every instance has its own key), which MediaWiki core rejects with
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

		$jsConfigVars = $parserOutput->getJsConfigVars();
		$this->assertSame( [ 'views' => [ 'list' ] ], $jsConfigVars['srfFilteredConfig-filtered-1'] );
		$this->assertSame( [ 'views' => [ 'table' ] ], $jsConfigVars['srfFilteredConfig-filtered-2'] );

		// Pages cached before the per-instance config vars still carry the merged
		// extension data; the hook has to keep serving them.
		$legacyConfig = [ 'filtered-1' => [ 'views' => [ 'list' ] ] ];
		$legacyOutput = new \ParserOutput();
		$legacyOutput->setExtensionData( 'srf-filtered-config', $legacyConfig );

		$outputPage = new \OutputPage( \RequestContext::getMain() );
		Hooks::onOutputPageParserOutput( $outputPage, $legacyOutput );

		$this->assertSame( $legacyConfig, $outputPage->getJsConfigVars()['srfFilteredConfig'] );
	}
}
