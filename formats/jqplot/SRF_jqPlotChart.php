<?php

/**
 * A query printer for bar, line, pie and donut chart on aggregated values
 * using the jqPlot JavaScript library.
 *
 * @since 1.8
 *
 * @file SRF_jqPlotChart.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Yaron Koren
 * @author Sanyam Goyal
 */
class SRFjqPlotChart extends SRFjqPlot {

	/**
	 * Corresponding message name
	 *
	 */
	public function getName() {
		return wfMessage( 'srf-printername-jqplotchart' )->text();
	}

	/**
	 * Prepare data output
	 *
	 * @since 1.8
	 *
	 * @param array $data label => value
	 */
	protected function getFormatOutput( array $data ) {

		static $statNr = 0;
		$chartID = 'jqplot-' . $this->params['charttype'] . '-' . ++$statNr;

		$this->isHTML = true;

		// Prepare data objects
		if ( in_array( $this->params['charttype'], [ 'bar', 'line' ] ) ) {
			// Parse bar relevant data
			$dataObject = $this->prepareBarData( $data );
		} elseif ( in_array( $this->params['charttype'], [ 'pie', 'donut' ] ) ) {
			//Parse pie/donut relevant data
			$dataObject = $this->preparePieData( $data );
		} else {
			// Return with an error
			return Html::rawElement(
				'span',
				[
					'class' => "error"
				],
				wfMessage( 'srf-error-missing-layout' )->inContentLanguage()->text()
			);
		}

		// Encode data objects
		$requireHeadItem = [ $chartID => FormatJson::encode( $dataObject ) ];
		SMWOutputs::requireHeadItem( $chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		// Processing placeholder
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// Ensure right conversion
		$width = strstr( $this->params['width'], "%" ) ? $this->params['width'] : $this->params['width'] . 'px';

		// Chart/graph placeholder
		$chart = Html::rawElement(
			'div',
			[
				'id' => $chartID,
				'class' => 'container',
				'style' => "display:none; width: {$width}; height: {$this->params['height']}px;"
			],
			null
		);

		// Beautify class selector
		$class = $this->params['charttype'] ? '-' . $this->params['charttype'] : '';
		$class = $this->params['class'] ? $class . ' ' . $this->params['class'] : $class . ' jqplot-common';

		// Chart/graph wrappper
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-jqplot' . $class,
			],
			$processing . $chart
		);
	}

	/**
	 * Prepare pie/donut chart specific data and parameters
	 *
	 * @since 1.8
	 *
	 * @param array $rawdata label => value
	 *
	 * @return array
	 */
	private function preparePieData( $rawdata ) {

		// Init
		$mode = 'single';

		// Reorganize the data in accordance with the pie chart req.
		foreach ( $rawdata as $name => $value ) {
			if ( $value >= $this->params['min'] ) {
				$data[] = [ $name, $value ];
			}
		}

		if ( $this->params['charttype'] === 'donut' ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.donut' );
		} else {
			SMWOutputs::requireResource( 'ext.srf.jqplot.pie' );
		}

		return [
			'data' => [ $data ],
			'renderer' => $this->params['charttype'],
			'mode' => $mode,
			'parameters' => $this->addCommonOptions()
		];
	}

	/**
	 * Prepare bar/line chart specific data and parameters
	 *
	 * Data can be an array of y values, or an array of [label, value] pairs;
	 * While labels are used only on the first series with labels on
	 * subsequent series being ignored
	 *
	 * @since 1.8
	 *
	 * @param array $rawdata label => value
	 *
	 * @return array
	 */
	private function prepareBarData( $rawdata ) {

		// Init
		$total = 0;
		$mode = 'single';

		// Find min and max values to determine the graphs axis parameter
		$maxValue = count( $rawdata ) == 0 ? 0 : max( $rawdata );

		if ( $this->params['min'] === false ) {
			$minValue = count( $rawdata ) == 0 ? 0 : min( $rawdata );
		} else {
			$minValue = $this->params['min'];
		}

		// Get number ticks
		$data['numbersticks'] = SRFjqPlot::getNumbersTicks( $minValue, $maxValue );

		// Reorganize the data in accordance with the bar/line chart req.
		foreach ( $rawdata as $key => $value ) {
			if ( $value >= $this->params['min'] ) {
				$data['series'][] = [ $key, $value ];
				$total = $total + $value;
			}
		}

		// Bar/line module
		SMWOutputs::requireResource( 'ext.srf.jqplot.bar' );

		// Highlighter plugin
		if ( $this->params['highlighter'] ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.highlighter' );
		}

		// Pointlabels plugin
		if ( in_array( $this->params['datalabels'], [ 'value', 'label', 'percent' ] ) ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.pointlabels' );
		}

		return [
			'data' => [ $data['series'] ],
			'ticks' => $data['numbersticks'],
			'labels' => array_keys( $data['series'] ),
			'numbers' => array_values( $data['series'] ),
			'max' => $maxValue,
			'total' => $total,
			'mode' => $mode,
			'series' => [],
			'renderer' => $this->params['charttype'],
			'parameters' => $this->addCommonOptions()
		];
	}

	/**
	 * jqPlot common parameters
	 *
	 * @since 1.8
	 *
	 */
	private function addCommonOptions() {

		// Series colour
		$seriescolors = $this->params['chartcolor'] !== '' ? array_filter(
			explode( ",", $this->params['chartcolor'] )
		) : null;

		return [
			'numbersaxislabel' => $this->params['numbersaxislabel'],
			'labelaxislabel' => $this->params['labelaxislabel'],
			'charttitle' => $this->params['charttitle'],
			'charttext' => $this->params['charttext'],
			'theme' => $this->params['theme'] ? $this->params['theme'] : null,
			'ticklabels' => $this->params['ticklabels'],
			'highlighter' => $this->params['highlighter'],
			'direction' => $this->params['direction'],
			'smoothlines' => $this->params['smoothlines'],
			'filling' => $this->params['filling'],
			'datalabels' => $this->params['datalabels'],
			'valueformat' => $this->params['valueformat'],
			'chartlegend' => $this->params['chartlegend'] !== '' ? $this->params['chartlegend'] : 'none',
			'colorscheme' => $this->params['colorscheme'] !== '' ? $this->params['colorscheme'] : null,
			'pointlabels' => $this->params['datalabels'] === 'none' ? false : $this->params['datalabels'],
			'grid' => $this->params['theme'] === 'vector' ? [ 'borderColor' => '#a7d7f9' ] : ( $this->params['theme'] === 'simple' ? [ 'borderColor' => '#ddd' ] : null ),
			'seriescolors' => $seriescolors
		];
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = self::getCommonParams();

		$params['charttype'] = [
			'message' => 'srf-paramdesc-charttype',
			'default' => 'bar',
			'values' => [ 'bar', 'line', 'pie', 'donut' ],
		];

		return array_merge( parent::getParamDefinitions( $definitions ), $params );
	}
}
