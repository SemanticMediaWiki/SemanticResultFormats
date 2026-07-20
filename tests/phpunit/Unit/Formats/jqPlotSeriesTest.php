<?php

namespace SRF\Tests\Unit\Formats;

use ReflectionClass;
use SMW\Tests\QueryPrinterRegistryTestCase;
use SRF\Tests\ResultPrinterReflector;

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
		$definitions = $this->newInstance( 'jqplotseries', true )->getParamDefinitions( [] );

		$this->assertArrayHasKey( 'chartcursor', $definitions );
		$this->assertSame( 'none', $definitions['chartcursor']['default'] );
		$this->assertSame( [ 'none', 'zoom', 'tooltip' ], $definitions['chartcursor']['values'] );
	}

	/**
	 * `cursor` is reserved by SMW >= 7.0 for keyset pagination tokens, and is
	 * registered globally by `SMW\Query\Processor\DefaultParamDefinition` with
	 * an empty default. A format that adds — or overwrites — a definition of
	 * that name feeds its own default into the pagination machinery, where it
	 * is rejected as a malformed token.
	 *
	 * Passing SMW's own definition in asserts the stronger property: the format
	 * must leave an inherited `cursor` untouched, not merely refrain from
	 * adding one.
	 *
	 * @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1078
	 *
	 * @covers \SRFjqPlotSeries::getParamDefinitions
	 */
	public function testDoesNotDefineReservedCursorParameter() {
		$reserved = [ 'cursor' => [ 'default' => '' ] ];

		$definitions = $this->newInstance( 'jqplotseries', true )->getParamDefinitions( $reserved );

		$this->assertSame( '', $definitions['cursor']['default'] );
	}

	/**
	 * The parameter is `chartcursor` on the wiki side, but the chart data JSON
	 * handed to the client keeps the historical `cursor` key, which
	 * `ext.srf.jqplot.chart.bar.js` reads. Renaming one without the other
	 * silently disables the jqPlot cursor plugin.
	 *
	 * @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1078
	 *
	 * @covers \SRFjqPlotSeries::getFormatSettings
	 */
	public function testChartCursorIsEmittedAsCursorInChartData() {
		$settings = $this->newFormatSettings( [ 'chartcursor' => 'zoom' ] );

		$this->assertSame( 'zoom', $settings['parameters']['cursor'] );
		$this->assertArrayNotHasKey( 'chartcursor', $settings['parameters'] );
	}

	/**
	 * @covers \SRFjqPlotSeries::getFormatSettings
	 */
	public function testChartCursorDefaultIsEmittedAsCursorInChartData() {
		$settings = $this->newFormatSettings();

		$this->assertSame( 'none', $settings['parameters']['cursor'] );
	}

	/**
	 * Invokes the private `getFormatSettings()` against a minimal single-series
	 * data set, with the printer's parameters set to the format's own defaults
	 * plus any overrides.
	 *
	 * @param array $params overrides for the default parameter values
	 *
	 * @return array the chart data as handed to the client
	 */
	private function newFormatSettings( array $params = [] ): array {
		$instance = $this->newInstance( 'jqplotseries', true );

		( new ResultPrinterReflector() )->addParameters(
			$instance,
			array_merge( [
				'group' => 'subject',
				'grouplabel' => 'subject',
				'charttype' => 'bar',
				'chartcolor' => '',
				'chartcursor' => 'none',
				'chartlegend' => '',
				'charttitle' => '',
				'charttext' => '',
				'infotext' => '',
				'colorscheme' => '',
				'datalabels' => 'none',
				'direction' => 'vertical',
				'gridview' => 'none',
				'hidezeroes' => false,
				'highlighter' => false,
				'labelaxislabel' => '',
				'numbersaxislabel' => '',
				'smoothlines' => false,
				'stackseries' => false,
				'theme' => '',
				'ticklabels' => true,
				'trendline' => 'none',
				'valueformat' => '%d',
			], $params )
		);

		$data = [
			'subject' => [
				'Page A' => [
					[ 'subject' => 'Page A', 'value' => 10, 'property' => 'Has value' ],
				],
			],
			'numbersticks' => [ 0, 10 ],
			'total' => 10,
			'fcolumntypeid' => '_num',
		];

		$reflector = new ReflectionClass( $instance );
		$method = $reflector->getMethod( 'getFormatSettings' );
		$method->setAccessible( true );

		return $method->invoke( $instance, $data, [ 'sask' => '' ] );
	}

}
