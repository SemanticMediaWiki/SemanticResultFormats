'use strict';

const path = require( 'path' );
const sinon = require( 'sinon' );

// ext.srf.jqplot.chart.js defines the container plugin plus srfjqPlotTheme;
// ext.srf.jqplot.chart.bar.js consumes them, matching the real ResourceLoader
// bundle order declared for "ext.srf.jqplot.bar" in Resources.php. The vendored
// jqPlot library itself is canvas-bound and stays out of jsdom; the plotting
// call boundary ($.jqplot) is stubbed per test instead.
require( path.resolve( __dirname, '../../formats/jqplot/resources/ext.srf.jqplot.chart.js' ) );
require( path.resolve( __dirname, '../../formats/jqplot/resources/ext.srf.jqplot.chart.bar.js' ) );

/**
 * Chart data as emitted by SRFjqPlotSeries::getFormatSettings() /
 * SRFjqPlot::getCommonParams() on the PHP side, reduced to the fields the
 * client-side modules read.
 *
 * @param {Object} overrides own properties replace the defaults; a nested
 *  `parameters` object is merged key-by-key
 * @return {Object}
 */
function makeChartData( overrides ) {
	overrides = overrides || {};

	const data = Object.assign( {
		data: [ [ [ 'Page A', 10 ], [ 'Page B', 20 ] ] ],
		series: [ { label: 'Series 1' } ],
		ticks: [ 0, 10, 20 ],
		total: 30,
		fcolumntypeid: '_wpg',
		mode: 'series',
		renderer: 'bar',
		legendLabels: [ 'Series 1' ],
		sask: ''
	}, overrides );

	data.parameters = Object.assign( {
		numbersaxislabel: '',
		labelaxislabel: '',
		charttitle: '',
		charttext: '',
		infotext: '',
		theme: null,
		valueformat: '%d',
		ticklabels: true,
		highlighter: false,
		autoscale: false,
		gridview: 'none',
		direction: 'vertical',
		smoothlines: false,
		cursor: 'none',
		chartlegend: 'none',
		colorscheme: null,
		pointlabels: false,
		datalabels: 'none',
		stackseries: false,
		grid: null,
		seriescolors: [ '#ddd' ],
		hideZeroes: false
	}, overrides.parameters );

	return data;
}

/**
 * Minimal stand-in for the vendored jqPlot library: a callable spy carrying
 * the renderer constructors and config object the bar module dereferences.
 *
 * @return {Function} the spy installed as $.jqplot
 */
function stubJqplot() {
	const jqplot = sinon.spy( () => ( {} ) );

	[
		'CanvasAxisLabelRenderer', 'CanvasAxisTickRenderer', 'LinearAxisRenderer',
		'CategoryAxisRenderer', 'BarRenderer', 'LineRenderer', 'EnhancedLegendRenderer'
	].forEach( ( renderer ) => {
		jqplot[ renderer ] = function () {};
	} );
	jqplot.config = {};

	$.jqplot = jqplot;
	return jqplot;
}

QUnit.module( 'ext.srf.jqplot.chart', ( hooks ) => {

	let barSpy, pieSpy, bubbleSpy;

	hooks.beforeEach( () => {
		barSpy = sinon.replace( $.fn, 'srfjqPlotBarChartData', sinon.fake() );
		// The pie/bubble modules are not loaded here; provide their plugin
		// entry points so the dispatch can be observed
		pieSpy = $.fn.srfjqPlotPieChart = sinon.fake();
		bubbleSpy = $.fn.srfjqPlotBubbleChartData = sinon.fake();
	} );

	hooks.afterEach( () => {
		delete $.fn.srfjqPlotPieChart;
		delete $.fn.srfjqPlotBubbleChartData;
	} );

	/**
	 * @param {Object} data chart data, stored under the container id "chart-1"
	 *  as srfjqPlotChartContainer() reads it back via mw.config
	 * @return {jQuery} the chart wrapper attached to the document
	 */
	function createChart( data ) {
		mw.config.set( 'chart-1', data );

		return $(
			'<div class="srf-jqplot bar">' +
			'<div class="container" id="chart-1" style="display:none"><canvas></canvas></div>' +
			'</div>'
		).appendTo( document.body );
	}

	QUnit.test( 'bar renderer data is dispatched to the bar plugin', ( assert ) => {
		const chart = createChart( makeChartData() );

		chart.srfjqPlotChartContainer();

		assert.strictEqual( barSpy.callCount, 1, 'srfjqPlotBarChartData() was called' );
		assert.strictEqual( barSpy.firstCall.args[ 0 ].id, 'chart-1-plot', 'the plotting area id was passed' );
		assert.strictEqual( barSpy.firstCall.args[ 0 ].data.renderer, 'bar', 'the parsed chart data was passed' );
		assert.strictEqual( pieSpy.callCount, 0, 'the pie plugin was not involved' );
		assert.strictEqual( bubbleSpy.callCount, 0, 'the bubble plugin was not involved' );
	} );

	QUnit.test( 'JSON string data is parsed before dispatch', ( assert ) => {
		const chart = createChart( JSON.stringify( makeChartData( { renderer: 'line' } ) ) );

		chart.srfjqPlotChartContainer();

		assert.strictEqual( barSpy.firstCall.args[ 0 ].data.renderer, 'line', 'the JSON string was parsed into an object' );
	} );

	QUnit.test( 'pie and donut renderers are dispatched to the pie plugin', ( assert ) => {
		createChart( makeChartData( { renderer: 'pie' } ) ).srfjqPlotChartContainer();

		assert.strictEqual( pieSpy.callCount, 1, 'srfjqPlotPieChart() was called for renderer "pie"' );

		createChart( makeChartData( { renderer: 'donut' } ) ).srfjqPlotChartContainer();

		assert.strictEqual( pieSpy.callCount, 2, 'srfjqPlotPieChart() was called for renderer "donut"' );
		assert.strictEqual( barSpy.callCount, 0, 'the bar plugin was not involved' );
	} );

	QUnit.test( 'bubble renderer is dispatched to the bubble plugin', ( assert ) => {
		createChart( makeChartData( { renderer: 'bubble' } ) ).srfjqPlotChartContainer();

		assert.strictEqual( bubbleSpy.callCount, 1, 'srfjqPlotBubbleChartData() was called' );
	} );

	QUnit.test( 'container is released for display with a plotting area', ( assert ) => {
		const chart = createChart( makeChartData() );

		chart.srfjqPlotChartContainer();

		const container = chart.find( '.container' );
		assert.notStrictEqual( container.css( 'display' ), 'none', 'the container was shown' );
		assert.strictEqual( container.find( 'canvas' ).length, 0, 'stale canvas elements were removed' );
		assert.strictEqual( container.find( '#chart-1-plot.srf-jqplot-plot.bar' ).length, 1, 'the plotting area div was created' );
	} );

	QUnit.test( 'chart text is prepended when the charttext parameter is set', ( assert ) => {
		const chart = createChart( makeChartData( { parameters: { charttext: 'About this chart' } } ) );

		chart.srfjqPlotChartContainer();

		const text = chart.find( '.srf-jqplot-chart-text' );
		assert.strictEqual( text.length, 1, 'the chart text element was created' );
		assert.strictEqual( text.text(), 'About this chart', 'the chart text was inserted' );
		assert.true( text.hasClass( 'bar' ), 'the renderer class was added to the text element' );
	} );
} );

QUnit.module( 'ext.srf.jqplot.chart.bar', () => {

	QUnit.module( 'srfjqPlotBarChartData()', ( hooks ) => {

		let plotSpy;

		hooks.beforeEach( () => {
			plotSpy = sinon.replace( $.fn, 'srfjqPlotBarChart', sinon.fake() );
		} );

		/**
		 * @param {Object} data
		 * @return {Object} the options srfjqPlotBarChart() was called with
		 */
		function transform( data ) {
			$( '<div>' ).srfjqPlotBarChartData( { id: 'plot-1', data: data, height: 400, width: 600, chart: $( '<div>' ) } );

			return plotSpy.firstCall.args[ 0 ];
		}

		QUnit.test( 'vertical category series keeps plain value arrays', ( assert ) => {
			const options = transform( makeChartData() );

			assert.deepEqual( options.barData, [ [ 10, 20 ] ], 'values were extracted per series' );
			assert.deepEqual( options.labels, [ 'Page A', 'Page B' ], 'first-column labels were collected separately' );
		} );

		QUnit.test( 'horizontal direction produces [value, position] pairs', ( assert ) => {
			const options = transform( makeChartData( { parameters: { direction: 'horizontal' } } ) );

			assert.deepEqual( options.barData, [ [ [ 10, 1 ], [ 20, 2 ] ] ], 'values were paired with their 1-based position' );
		} );

		QUnit.test( 'numeric first column keeps [x, y] pairs', ( assert ) => {
			const options = transform( makeChartData( {
				data: [ [ [ 1, 10 ], [ 2, 20 ] ] ],
				fcolumntypeid: '_num'
			} ) );

			assert.deepEqual( options.barData, [ [ [ 1, 10 ], [ 2, 20 ] ] ], 'numeric x-values were preserved' );
		} );

		QUnit.test( 'stacked series of unequal length bail out with an error', ( assert ) => {
			mw.msg = () => 'unequal series length';

			const context = $( '<div>' );
			context.srfjqPlotBarChartData( {
				id: 'plot-1',
				data: makeChartData( {
					data: [ [ [ 'Page A', 10 ], [ 'Page B', 20 ] ], [ [ 'Page A', 30 ] ] ],
					parameters: { stackseries: true }
				} ),
				height: 400,
				width: 600,
				chart: $( '<div>' )
			} );

			assert.strictEqual( plotSpy.callCount, 0, 'no plotting was attempted' );
			assert.strictEqual( context.text(), 'unequal series length', 'the error message was shown instead' );
		} );
	} );

	QUnit.module( 'srfjqPlotBarChart()', ( hooks ) => {

		let themeSpy;

		hooks.beforeEach( () => {
			themeSpy = sinon.replace( $.fn, 'srfjqPlotTheme', sinon.fake() );
		} );

		hooks.afterEach( () => {
			delete $.jqplot;
		} );

		/**
		 * @param {Object} data
		 * @return {Object} the configuration passed to $.jqplot()
		 */
		function plot( data ) {
			const jqplot = stubJqplot();

			$( '<div>' ).srfjqPlotBarChart( {
				id: 'plot-1',
				data: data,
				barData: [ [ 10, 20 ] ],
				labels: data.fcolumntypeid === '_num' ? [ 1, 2 ] : [ 'Page A', 'Page B' ],
				height: 400,
				width: 600,
				chart: $( '<div>' )
			} );

			return jqplot.firstCall.args[ 2 ];
		}

		QUnit.test( 'chartcursor zoom enables the cursor plugin in zoom mode', ( assert ) => {
			const config = plot( makeChartData( {
				fcolumntypeid: '_num',
				parameters: { cursor: 'zoom' }
			} ) );

			assert.deepEqual(
				config.cursor,
				{ show: true, zoom: true, looseZoom: true, showTooltip: false },
				'the cursor plugin was configured for zooming'
			);
		} );

		QUnit.test( 'chartcursor tooltip enables the cursor plugin in tooltip mode', ( assert ) => {
			const config = plot( makeChartData( {
				fcolumntypeid: '_num',
				parameters: { cursor: 'tooltip' }
			} ) );

			assert.deepEqual(
				config.cursor,
				{ show: true, zoom: false, looseZoom: false, showTooltip: true },
				'the cursor plugin was configured for tooltips'
			);
		} );

		QUnit.test( 'default chartcursor none keeps the cursor plugin off', ( assert ) => {
			const config = plot( makeChartData( { fcolumntypeid: '_num' } ) );

			assert.deepEqual(
				config.cursor,
				{ show: false, zoom: false, looseZoom: false, showTooltip: false },
				'the cursor plugin stayed off'
			);
		} );

		QUnit.test( 'cursor plugin stays hidden for non-numeric first columns', ( assert ) => {
			const config = plot( makeChartData( { parameters: { cursor: 'zoom' } } ) );

			assert.false( config.cursor.show, 'zooming needs a numeric or date first column' );
			assert.true( config.cursor.zoom, 'the zoom flag itself follows the parameter' );
		} );

		QUnit.test( 'bar renderer and vertical axes are configured', ( assert ) => {
			const config = plot( makeChartData() );

			assert.strictEqual( config.seriesDefaults.renderer, $.jqplot.BarRenderer, 'bars use the bar renderer' );
			assert.deepEqual( config.axes.xaxis.ticks, [ 'Page A', 'Page B' ], 'the x-axis carries the category labels' );
			assert.deepEqual( config.axes.yaxis.ticks, [ 0, 10, 20 ], 'the y-axis carries the number ticks' );
			assert.strictEqual( config.legend, null, 'chartlegend "none" disables the legend' );
			assert.strictEqual( themeSpy.callCount, 1, 'theming was applied to the plot' );
		} );
	} );
} );
