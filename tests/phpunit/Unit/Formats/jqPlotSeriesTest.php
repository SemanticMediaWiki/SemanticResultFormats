<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\QueryPrinterRegistryTestCase;

/**
 * Tests for the SRF\jqPlotSeries class.
 *
 * @file
 * @since 1.8
 *
 * @ingroup SemanticResultFormats
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @license GPL-2.0-or-later
 * @author mwjames
 */
class jqPlotSeriesTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'jqplotseries' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRFjqPlotSeries';
	}

	/**
	 * @covers \SRFjqPlotSeries::getParamDefinitions
	 */
	public function testDefinesChartCursorParameter() {
		$definitions = $this->newPrinter()->getParamDefinitions( [] );

		$this->assertArrayHasKey( 'chartcursor', $definitions );
		$this->assertSame( 'none', $definitions['chartcursor']['default'] );
		$this->assertSame( [ 'none', 'zoom', 'tooltip' ], $definitions['chartcursor']['values'] );
	}

	/**
	 * `cursor` is reserved by SMW >= 7.0 for keyset pagination tokens; a
	 * format parameter of that name is fed into the pagination machinery
	 * and rejected as a malformed token.
	 *
	 * @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1078
	 *
	 * @covers \SRFjqPlotSeries::getParamDefinitions
	 */
	public function testDoesNotDefineReservedCursorParameter() {
		$definitions = $this->newPrinter()->getParamDefinitions( [] );

		$this->assertArrayNotHasKey( 'cursor', $definitions );
	}

	private function newPrinter(): \SRFjqPlotSeries {
		return new \SRFjqPlotSeries( 'jqplotseries' );
	}

}
