<?php

namespace SRF\Tests\Unit\Formats;

/**
 * The d3 chart and the jqPlot pie chart drop every value below the parameter "min". When all values
 * are below it, nothing is left, and that has to give an empty data set, not an undefined variable.
 *
 * @covers \SRFD3Chart
 * @covers \SRFjqPlotChart
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 */
class ChartDataBelowMinimumTest extends \MediaWikiIntegrationTestCase {

	public function testD3ChartWithAllValuesBelowMinimumHasEmptyData() {
		$printer = new \SRFD3Chart( 'd3chart' );
		$this->setParams( $printer, [
			'min' => 100, 'width' => '400', 'colorscheme' => '', 'charttitle' => '', 'charttext' => '',
			'datalabels' => '', 'height' => '300', 'charttype' => 'treemap', 'class' => ''
		] );

		$method = new \ReflectionMethod( $printer, 'getFormatOutput' );
		$method->setAccessible( true );

		$output = $method->invoke( $printer, [ 'a' => 1, 'b' => 2 ] );

		$this->assertIsString( $output );
	}

	public function testPieChartWithAllValuesBelowMinimumHasEmptyData() {
		$printer = new \SRFjqPlotChart( 'jqplotchart' );
		$this->setParams( $printer, [
			'min' => 100, 'charttype' => 'pie', 'chartcolor' => '', 'numbersaxislabel' => '', 'labelaxislabel' => '',
			'charttitle' => '', 'charttext' => '', 'theme' => '', 'ticklabels' => false, 'highlighter' => false,
			'direction' => 'vertical', 'smoothlines' => false, 'filling' => false, 'datalabels' => 'none',
			'valueformat' => '', 'chartlegend' => '', 'colorscheme' => ''
		] );

		$method = new \ReflectionMethod( $printer, 'preparePieData' );
		$method->setAccessible( true );

		$prepared = $method->invoke( $printer, [ 'a' => 1, 'b' => 2 ] );

		$this->assertSame( [ [] ], $prepared['data'] );
	}

	private function setParams( object $printer, array $params ): void {
		$property = new \ReflectionProperty( \SMW\Query\ResultPrinters\ResultPrinter::class, 'params' );
		$property->setAccessible( true );
		$property->setValue( $printer, $params );
	}
}
