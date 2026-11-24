<?php

namespace SRF\Tests\Filtered;

use MediaWikiIntegrationTestCase;
use SRF\Filtered\Filtered;

/**
 * Regression test for the filtered format in combination with `|@deferred`.
 *
 * SMW loads a deferred query through the parse API and copies only the
 * `jsconfigvars` of that response into mw.config. The configuration of the
 * filtered instance therefore has to be part of the ParserOutput's JS config
 * vars; the OutputPage hook (Hooks::onOutputPageParserOutput) does not run
 * for API parses.
 *
 * @covers \SRF\Filtered\Filtered
 * @group semantic-result-formats
 *
 * @author gesinn-it-gea
 */
class DeferredQueryConfigTest extends MediaWikiIntegrationTestCase {

	private function newPrinter( \ParserOutput $parserOutput ): Filtered {
		$parser = $this->createMock( \Parser::class );
		$parser->method( 'getOutput' )->willReturn( $parserOutput );
		// Filtered::getParser() swaps in a fresh parser when getOptions() is null.
		$parser->method( 'getOptions' )->willReturn( $this->createStub( \ParserOptions::class ) );

		$printer = new Filtered( null );
		$printer->setParser( $parser );

		return $printer;
	}

	private function addConfigToOutput( Filtered $printer, string $id, array $config ): void {
		$method = new \ReflectionMethod( Filtered::class, 'addConfigToOutput' );
		$method->setAccessible( true );
		$method->invoke( $printer, $id, $config );
	}

	/**
	 * Returns the configuration of all instances found in the JS config vars,
	 * independent of how the vars are keyed.
	 */
	private function collectInstanceConfigs( array $jsConfigVars ): array {
		$found = [];

		foreach ( $jsConfigVars as $key => $value ) {
			if ( $key === 'srfFilteredConfig' ) {
				$found += $value;
			} elseif ( strpos( $key, 'srfFilteredConfig-' ) === 0 ) {
				$found[substr( $key, strlen( 'srfFilteredConfig-' ) )] = $value;
			}
		}

		return $found;
	}

	public function testConfigIsAvailableInJsConfigVarsOfParserOutput() {
		$parserOutput = new \ParserOutput();

		$this->addConfigToOutput(
			$this->newPrinter( $parserOutput ),
			'filtered-1',
			[ 'views' => [ 'list' ] ]
		);

		$this->assertSame(
			[ 'filtered-1' => [ 'views' => [ 'list' ] ] ],
			$this->collectInstanceConfigs( $parserOutput->getJsConfigVars() )
		);
	}

	public function testTwoInstancesInOneParseKeepTheirOwnConfigAndDoNotThrow() {
		$parserOutput = new \ParserOutput();
		$printer = $this->newPrinter( $parserOutput );

		$this->addConfigToOutput( $printer, 'filtered-1', [ 'views' => [ 'list' ] ] );
		$this->addConfigToOutput( $printer, 'filtered-2', [ 'views' => [ 'table' ] ] );

		$this->assertSame(
			[
				'filtered-1' => [ 'views' => [ 'list' ] ],
				'filtered-2' => [ 'views' => [ 'table' ] ],
			],
			$this->collectInstanceConfigs( $parserOutput->getJsConfigVars() )
		);
	}

	public function testSameInstanceRenderedTwiceDoesNotThrow() {
		$parserOutput = new \ParserOutput();
		$printer = $this->newPrinter( $parserOutput );

		$this->addConfigToOutput( $printer, 'filtered-1', [ 'views' => [ 'list' ] ] );
		$this->addConfigToOutput( $printer, 'filtered-1', [ 'views' => [ 'list' ] ] );

		$this->assertCount( 1, $this->collectInstanceConfigs( $parserOutput->getJsConfigVars() ) );
	}
}
