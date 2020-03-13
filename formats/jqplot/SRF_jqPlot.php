<?php

/**
 * Abstract class to hold common functionality for the jqPlot result printers.
 *
 * @since 1.8
 *
 * @licence GNU GPL v2+
 * @author mwjames
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Yaron Koren
 * @author Sanyam Goyal
 */
abstract class SRFjqPlot extends SMWAggregatablePrinter {

	public static function getCommonParams() {

		$params = [];

		$params['min'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-minvalue',
			'default' => false,
			'manipulatedefault' => false,
		];

		$params['direction'] = [
			'message' => 'srf-paramdesc-direction',
			'default' => 'vertical',
			'values' => [ 'horizontal', 'vertical' ],
		];

		$params['charttitle'] = [
			'message' => 'srf_paramdesc_charttitle',
			'default' => '',
		];

		$params['charttext'] = [
			'message' => 'srf-paramdesc-charttext',
			'default' => '',
		];

		$params['numbersaxislabel'] = [
			'message' => 'srf_paramdesc_barnumbersaxislabel',
			'default' => '',
		];

		$params['labelaxislabel'] = [
			'message' => 'srf-paramdesc-labelaxislabel',
			'default' => '',
		];

		$params['height'] = [
			'type' => 'integer',
			'message' => 'srf_paramdesc_chartheight',
			'default' => 400,
			'lowerbound' => 1,
		];

		// TODO: this is a string to allow for %, but better handling would be nice
		$params['width'] = [
			'message' => 'srf_paramdesc_chartwidth',
			'default' => '100%',
		];

		$params['smoothlines'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-smoothlines',
			'default' => false,
		];

		// %.2f round number to 2 digits after decimal point e.g.  EUR %.2f, $ %.2f
		// %d a signed integer, in decimal
		$params['valueformat'] = [
			'message' => 'srf-paramdesc-valueformat',
			'default' => '%d',
		];

		$params['ticklabels'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-ticklabels',
			'default' => true,
		];

		$params['highlighter'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-highlighter',
			'default' => false,
		];

		$params['theme'] = [
			'message' => 'srf-paramdesc-theme',
			'default' => '',
			'values' => [ '', 'vector', 'simple' ],
		];

		$params['filling'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-filling',
			'default' => true,
		];

		$params['chartlegend'] = [
			'message' => 'srf-paramdesc-chartlegend',
			'default' => 'none',
			'values' => [ 'none', 'nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w' ],
		];

		$params['datalabels'] = [
			'message' => 'srf-paramdesc-datalabels',
			'default' => 'none',
			'values' => [ 'none', 'value', 'label', 'percent' ],
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

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		return $params;
	}

	/**
	 * Prepare jqplot specific numbers ticks
	 *
	 * @since 1.8
	 *
	 * @param array $data
	 * @param $minValue
	 * @param $maxValue
	 *
	 * @return array
	 */
	public static function getNumbersTicks( $minValue, $maxValue ) {
		$numbersticks = [];

		// Calculate the tick values for the numbers, based on the
		// lowest and highest number. jqPlot has its own option for
		// calculating ticks automatically - "autoscale" - but it
		// currently (September 2010, it also fails with the jpPlot 1.00b 2012)
		// fails for numbers less than 1, and negative numbers.
		// If both max and min are 0, just escape now.
		if ( $maxValue == 0 && $minValue == 0 ) {
			return null;
		}

		// Make the max and min slightly larger and bigger than the
		// actual max and min, so that the bars don't directly touch
		// the top and bottom of the graph
		if ( $maxValue > 0 ) {
			$maxValue += .001;
		}

		if ( $minValue < 0 ) {
			$minValue -= .001;
		}

		if ( $maxValue == 0 ) {
			$multipleOf10 = 0;
			$maxAxis = 0;
		} else {
			$multipleOf10 = pow( 10, floor( log( $maxValue, 10 ) ) );
			$maxAxis = ceil( $maxValue / $multipleOf10 ) * $multipleOf10;
		}

		if ( $minValue == 0 ) {
			$negativeMultipleOf10 = 0;
			$minAxis = 0;
		} else {
			$negativeMultipleOf10 = -1 * pow( 10, floor( log( ( abs( $minValue ) ), 10 ) ) );
			$minAxis = ceil( $minValue / $negativeMultipleOf10 ) * $negativeMultipleOf10;
		}

		$biggerMultipleOf10 = max( $multipleOf10, -1 * $negativeMultipleOf10 );
		$lowestTick = floor( $minAxis / $biggerMultipleOf10 + .001 );
		$highestTick = ceil( $maxAxis / $biggerMultipleOf10 - .001 );

		for ( $i = $lowestTick; $i <= $highestTick; $i++ ) {
			$numbersticks[] = ( $i * $biggerMultipleOf10 );
		}

		return $numbersticks;
	}
}