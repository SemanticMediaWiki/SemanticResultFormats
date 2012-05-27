<?php

/**
 * Abstract class to hold common functionality for the jqPlot result printers.
 *
 * @since 0.1
 *
 * @file
 * @ingroup SemanticResultFormats
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author mwjames
 * @author Yaron Koren
 * @author Sanyam Goyal
 */
abstract class SRFjqPlot extends SMWAggregatablePrinter {

	public static function getCommonParams() {
		global $srfgjqPlotSettings, $srfgColorScheme;

		$params = array();

		$params['min'] = array(
			'type' => 'integer',
			'message' => 'srf-paramdesc-minvalue',
			'default' => false,
			'manipulatedefault' => false,
		);

		$params['renderer'] = array(
			'message' => 'srf-paramdesc-renderer',
			'default' => 'pie',
			'values' => $srfgjqPlotSettings['pierenderer'],
		);

		$params['height'] = array(
			'type' => 'integer',
			'message' => 'srf_paramdesc_chartheight',
			'default' => 400,
			'lowerbound' => 1,
		);

		// TODO: this is a string to allow for %, but better handling would be nice
		$params['height'] = array(
			'message' => 'srf_paramdesc_chartwidth',
			'default' => '400',
		);

		$params['charttitle'] = array(
			'message' => 'srf_paramdesc_charttitle',
			'default' => '',
		);

		$params['charttext'] = array(
			'message' => 'srf-paramdesc-charttext',
			'default' => '',
		);

		$params['theme'] = array(
			'message' => 'srf-paramdesc-theme',
			'default' => '',
			'values' => array( '', 'vector', 'mono' ),
		);

		$params['colorscheme'] = array(
			'message' => 'srf-paramdesc-colorscheme',
			'default' => '',
			'values' => $srfgColorScheme,
		);

		$params['chartcolor'] = array(
			'message' => 'srf-paramdesc-chartcolor',
			'default' => '',
		);

		$params['chartclass'] = array(
			'message' => 'srf-paramdesc-chartclass',
			'default' => '',
		);

		$params['valueformat'] = array(
			'message' => 'srf-paramdesc-valueformat',
			'default' => '%d',
		);

		foreach ( $params as $name => &$param ) {
			$param['name'] = $name;
		}

		return $params;
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

		$params['filling'] = array(
			'type' => 'boolean',
			'message' => 'srf-paramdesc-filling',
			'default' => true,
		);

		$params['datalabels'] = array(
			'message' => 'srf-paramdesc-datalabels',
			'default' => '',
			'values' => array( '', 'percent','value', 'label' ),
		);

		foreach ( $params as $name => &$param ) {
			$param['name'] = $name;
		}

		return array_merge( parent::getParamDefinitions( $definitions ), $params );
	}

}