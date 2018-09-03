<?php

/**
 * A query printer for timeseries using the flot plotting JavaScript library
 *
 * @see http://www.semantic-mediawiki.org/wiki/Help:Flot_timeseries_chart
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */
class SRFTimeseries extends SMWResultPrinter {

	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf-printername-timeseries' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {

		// Data processing
		$data = $this->getAggregatedTimeSeries( $result, $outputMode );

		// Post-data processing check
		if ( $data === [] ) {
			return $result->addErrors( [ wfMessage( 'srf-warn-empy-chart' )->inContentLanguage()->text() ] );
		} else {
			$options['sask'] = SRFUtils::htmlQueryResultLink( $this->getLink( $result, SMW_OUTPUT_HTML ) );
			return $this->getFormatOutput( $data, $options );
		}
	}

	/**
	 * Returns an array with numerical data
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getAggregatedTimeSeries( SMWQueryResult $result, $outputMode ) {
		$values = [];
		$aggregatedValues = [];

		while ( /* array of SMWResultArray */
		$row = $result->getNext() ) { // Objects (pages)
			$timeStamp = '';
			$series = [];

			foreach ( $row as /* SMWResultArray */
					  $field ) {
				$sum = [];

				// Group by subject (page object)  or property
				if ( $this->params['group'] == 'subject' ) {
					$group = $field->getResultSubject()->getTitle()->getText();
				} else {
					$group = $field->getPrintRequest()->getLabel();
				}

				while ( ( /* SMWDataValue */
					$dataValue = $field->getNextDataValue() ) !== false ) { // Data values

					// Find the timestamp
					if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_TIME ) {
						// We work with a timestamp, we have to use intval because DataItem
						// returns a string but we want a numeric representation of the timestamp
						$timeStamp = intval( $dataValue->getDataItem()->getMwTimestamp() );
					}

					// Find the values (numbers only)
					if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_NUMBER ) {
						$sum[] = $dataValue->getNumber();
					}
				}
				// Aggegate individual values into a sum
				$rowSum = array_sum( $sum );

				// Check the sum and threshold/min
				if ( $timeStamp !== '' && $field->getPrintRequest()->getTypeID(
					) !== '_dat' && $rowSum >= $this->params['min'] ) {
					$series[$group] = [ $timeStamp, $rowSum ];
				}
			}
			$values[] = $series;
		}

		// Re-assign values according to their group
		foreach ( $values as $key => $value ) {
			foreach ( $values[$key] as $row => $rowvalue ) {
				$aggregatedValues[$row][] = $rowvalue;
			}
		}
		return $aggregatedValues;
	}

	/**
	 * Prepare data for the output
	 *
	 * @since 1.8
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	protected function getFormatOutput( array $data, $options ) {

		// Object count
		static $statNr = 0;
		$chartID = 'timeseries-' . ++$statNr;

		$this->isHTML = true;

		// Reorganize the raw data
		foreach ( $data as $key => $values ) {
			$dataObject[] = [ 'label' => $key, 'data' => $values ];
		}

		// Series colour
		$seriescolors = $this->params['chartcolor'] !== '' ? array_filter(
			explode( ",", $this->params['chartcolor'] )
		) : [];

		// Prepare transfer array
		$chartData = [
			'data' => $dataObject,
			'fcolumntypeid' => '_dat',
			'sask' => $options['sask'],
			'parameters' => [
				'width' => $this->params['width'],
				'height' => $this->params['height'],
				'charttitle' => $this->params['charttitle'],
				'charttext' => $this->params['charttext'],
				'infotext' => $this->params['infotext'],
				'charttype' => $this->params['charttype'],
				'gridview' => $this->params['gridview'],
				'zoom' => $this->params['zoompane'],
				'seriescolors' => $seriescolors
			]
		];

		// Array encoding and output
		$requireHeadItem = [ $chartID => FormatJson::encode( $chartData ) ];
		SMWOutputs::requireHeadItem( $chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		// RL module
		SMWOutputs::requireResource( 'ext.srf.timeseries.flot' );

		if ( $this->params['gridview'] === 'tabs' ) {
			SMWOutputs::requireResource( 'ext.srf.util.grid' );
		}

		// Chart/graph placeholder
		$chart = Html::rawElement(
			'div',
			[
				'id' => $chartID,
				'class' => 'container',
				'style' => "display:none;"
			],
			null
		);

		// Processing/loading image
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// Beautify class selector
		$class = $this->params['class'] ? ' ' . $this->params['class'] : ' flot-chart-common';

		// General output marker
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-timeseries' . $class
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

		$params['charttype'] = [
			'message' => 'srf-paramdesc-layout',
			'default' => 'line',
			'values' => [ 'line', 'bar' ],
		];

		$params['min'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-minvalue',
			'default' => '',
		];

		$params['gridview'] = [
			'message' => 'srf-paramdesc-gridview',
			'default' => 'none',
			'values' => [ 'none', 'tabs' ],
		];

		$params['group'] = [
			'message' => 'srf-paramdesc-group',
			'default' => 'subject',
			'values' => [ 'property', 'subject' ],
		];

		$params['zoompane'] = [
			'message' => 'srf-paramdesc-zoompane',
			'default' => 'bottom',
			'values' => [ 'none', 'bottom', 'top' ],
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

		$params['infotext'] = [
			'message' => 'srf-paramdesc-infotext',
			'default' => '',
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
}
