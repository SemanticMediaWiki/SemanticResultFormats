<?php

/**
 * A query printer for D3 charts using the D3 JavaScript library
 * and SMWAggregatablePrinter.
 *
 * @file SRF_D3Chart.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */
class SRFD3Chart extends SMWAggregatablePrinter {

	/*
	 * @see SMWResultPrinter::getName
	 *
	 */
	public function getName() {
		return wfMessage( 'srf-printername-d3chart' )->text();
	}

	/**
	 * @see SMWResultPrinter::getFormatOutput
	 *
	 * @since 1.8
	 *
	 * @param array $data label => value
	 *
	 * @return string
	 */
	protected function getFormatOutput( array $data ) {

		// Object count
		static $statNr = 0;
		$d3chartID = 'd3-chart-' . ++$statNr;

		$this->isHTML = true;

		// Reorganize the raw data
		foreach ( $data as $name => $value ) {
			if ( $value >= $this->params['min'] ) {
				$dataObject[] = [ 'label' => $name, 'value' => $value ];
			}
		}

		// Ensure right conversion
		$width = strstr( $this->params['width'], "%" ) ? $this->params['width'] : $this->params['width'] . 'px';

		// Prepare transfer objects
		$d3data = [
			'data' => $dataObject,
			'parameters' => [
				'colorscheme' => $this->params['colorscheme'] ? $this->params['colorscheme'] : null,
				'charttitle' => $this->params['charttitle'],
				'charttext' => $this->params['charttext'],
				'datalabels' => $this->params['datalabels']
			]
		];

		// Encoding
		$requireHeadItem = [ $d3chartID => FormatJson::encode( $d3data ) ];
		SMWOutputs::requireHeadItem( $d3chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		// RL module
		$resource = 'ext.srf.d3.chart.' . $this->params['charttype'];
		SMWOutputs::requireResource( $resource );

		// Chart/graph placeholder
		$chart = Html::rawElement(
			'div',
			[
				'id' => $d3chartID,
				'class' => 'container',
				'style' => 'display:none;'
			],
			null
		);

		// Processing placeholder
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// Beautify class selector
		$class = $this->params['charttype'] ? '-' . $this->params['charttype'] : '';
		$class = $this->params['class'] ? $class . ' ' . $this->params['class'] : $class . ' d3-chart-common';

		// D3 wrappper
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-d3-chart' . $class,
				'style' => "width:{$width}; height:{$this->params['height']}px;"
			],
			$processing . $chart
		);
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
		$params = parent::getParamDefinitions( $definitions );

		$params['min'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-minvalue',
			'default' => false,
			'manipulatedefault' => false,
		];

		$params['charttype'] = [
			'message' => 'srf-paramdesc-charttype',
			'default' => 'treemap',
			'values' => [ 'treemap', 'bubble' ],
		];

		$params['height'] = [
			'type' => 'integer',
			'message' => 'srf_paramdesc_chartheight',
			'default' => 400,
			'lowerbound' => 1,
		];

		$params['width'] = [
			'message' => 'srf_paramdesc_chartwidth',
			'default' => '100%',
		];

		$params['charttitle'] = [
			'message' => 'srf_paramdesc_charttitle',
			'default' => '',
		];

		$params['charttext'] = [
			'message' => 'srf-paramdesc-charttext',
			'default' => '',
		];

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		$params['datalabels'] = [
			'message' => 'srf-paramdesc-datalabels',
			'default' => 'none',
			'values' => [ 'value', 'label' ],
		];

		$params['colorscheme'] = [
			'message' => 'srf-paramdesc-colorscheme',
			'default' => '',
			'values' => $GLOBALS['srfgColorScheme'],
		];

		$params['chartcolor'] = [
			'message' => 'srf-paramdesc-chartcolor',
			'default' => '',
		];

		return $params;
	}
}
