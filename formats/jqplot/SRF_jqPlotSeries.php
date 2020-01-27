<?php

/**
 * A query printer for charts series using the jqPlot JavaScript library.
 *
 * @since 1.8
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 */
class SRFjqPlotSeries extends SMWResultPrinter {

	/**
	 * @see SMWResultPrinter::getName
	 */
	public function getName() {
		return wfMessage( 'srf-printername-jqplotseries' )->text();
	}

	/**
	 * Returns an array with the numerical data in the query result.
	 *
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {

		// Get data set
		$data = $this->getResultData( $result, $outputMode );

		// Check data availability
		if ( $data['series'] === [] ) {
			return $result->addErrors(
				[
					wfMessage( 'srf-warn-empy-chart' )
						->inContentLanguage()->text() ]
			);
		} else {
			$options['sask'] = SRFUtils::htmlQueryResultLink( $this->getLink( $result, SMW_OUTPUT_HTML ) );
			return $this->getFormatOutput( $this->getFormatSettings( $this->getNumbersTicks( $data ), $options ) );
		}
	}

	/**
	 * Returns an array with the numerical data
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getResultData( SMWQueryResult $res, $outputMode ) {
		$data = [];
		$data['series'] = [];

		while ( $row = $res->getNext() ) {
			// Loop over their fields (properties)
			$label = '';
			$i = 0;

			foreach ( $row as /* SMWResultArray */
					  $field ) {
				$i++;
				$rowNumbers = [];

				// Grouping by subject (page object) or property
				if ( $this->params['group'] === 'subject' ) {
					$groupedBy = $field->getResultSubject()->getTitle()->getText();
				} else {
					$groupedBy = $field->getPrintRequest()->getLabel();
				}

				// Property label
				$property = $field->getPrintRequest()->getLabel();

				// First column property typeid
				$i == 1 ? $data['fcolumntypeid'] = $field->getPrintRequest()->getTypeID() : '';

				// Loop over all values for the property.
				while ( ( /* SMWDataValue */
					$object = $field->getNextDataValue() ) !== false ) {

					if ( $object->getDataItem()->getDIType() == SMWDataItem::TYPE_NUMBER ) {
						$number = $object->getNumber();

						// Checking against the row and in case the first column is a numeric
						// value it is handled as label with the remaining steps continue to work
						// as it were a text label
						// The first column container will not be part of the series container
						if ( $i == 1 ) {
							$label = $number;
							continue;
						}

						if ( $label !== '' && $number >= $this->params['min'] ) {

							// Reference array summarize all items per row
							$rowNumbers += [ 'subject' => $label, 'value' => $number, 'property' => $property ];

							// Store plain numbers for simpler handling
							$data['series'][$groupedBy][] = $number;
						}
					} elseif ( $object->getDataItem()->getDIType() == SMWDataItem::TYPE_TIME ) {
						$label = $object->getShortWikiText();
					} else {
						$label = $object->getWikiValue();
					}
				}
				// Only for array's with numbers
				if ( count( $rowNumbers ) > 0 ) {

					// For cases where mainlabel=- we assume that the subject should not be
					// used as identifier and therefore we try to match the groupby
					// with the first available text label
					if ( $this->params['mainlabel'] == '-' && $this->params['group'] === 'subject' ) {
						$data[$this->params['group']][$label][] = $rowNumbers;
					} else {
						$data[$this->params['group']][$groupedBy][] = $rowNumbers;
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Data set sorting
	 *
	 * @since 1.8
	 *
	 * @param array $data label => value
	 *
	 * @return array
	 */
	private function getFormatSettings( $data, $options ) {

		// Init
		$dataSet = [];
		$options['mode'] = 'series';
		$options['autoscale'] = false;

		// Available markers
		$marker = [ 'circle', 'diamond', 'square', 'filledCircle', 'filledDiamond', 'filledSquare' ];

		// Series colour(has to be null otherwise jqplot runs with a type error)
		$seriescolors = $this->params['chartcolor'] !== '' ? array_filter(
			explode( ",", $this->params['chartcolor'] )
		) : null;

		// Re-grouping
		foreach ( $data[$this->params['group']] as $rowKey => $row ) {
			$values = [];

			foreach ( $row as $key => $value ) {
				// Switch labels according to the group parameter
				$label = $this->params['grouplabel'] === 'property' ? $value['property'] : $value['subject'];
				$values[] = [ $label, $value['value'] ];
			}
			$dataSet[] = $values;
		}

		// Series plotting parameters
		foreach ( $data[$this->params['group']] as $key => $row ) {
			$series[] = [
				'label' => $key,
				'xaxis' => 'xaxis', // xaxis could also be xaxis2 or ...
				'yaxis' => 'yaxis',
				'fill' => $this->params['stackseries'],
				'showLine' => $this->params['charttype'] !== 'scatter',
				'showMarker' => true,
				'trendline' => [
					'show' => in_array( $this->params['trendline'], [ 'exp', 'linear' ] ),
					'shadow' => $this->params['theme'] !== 'simple',
					'type' => $this->params['trendline'],
				],
				'markerOptions' => [
					'style' => $marker[array_rand( $marker )],
					'shadow' => $this->params['theme'] !== 'simple'
				],
				'rendererOptions' => [ 'barDirection' => $this->params['direction'] ]
			];
		};

		// Basic parameters
		$parameters = [
			'numbersaxislabel' => $this->params['numbersaxislabel'],
			'labelaxislabel' => $this->params['labelaxislabel'],
			'charttitle' => $this->params['charttitle'],
			'charttext' => $this->params['charttext'],
			'infotext' => $this->params['infotext'],
			'theme' => $this->params['theme'] ? $this->params['theme'] : null,
			'valueformat' => $this->params['datalabels'] === 'label' ? '' : $this->params['valueformat'],
			'ticklabels' => $this->params['ticklabels'],
			'highlighter' => $this->params['highlighter'],
			'autoscale' => $options['autoscale'],
			'gridview' => $this->params['gridview'],
			'direction' => $this->params['direction'],
			'smoothlines' => $this->params['smoothlines'],
			'cursor' => $this->params['cursor'],
			'chartlegend' => $this->params['chartlegend'] !== '' ? $this->params['chartlegend'] : 'none',
			'colorscheme' => $this->params['colorscheme'] !== '' ? $this->params['colorscheme'] : null,
			'pointlabels' => $this->params['datalabels'] === 'none' ? false : $this->params['datalabels'],
			'datalabels' => $this->params['datalabels'],
			'stackseries' => $this->params['stackseries'],
			'grid' => $this->params['theme'] === 'vector' ? [ 'borderColor' => '#a7d7f9' ] : ( $this->params['theme'] === 'simple' ? [ 'borderColor' => '#ddd' ] : null ),
			'seriescolors' => $seriescolors,
			'hideZeroes' => $this->params['hidezeroes']
		];

		return [
			'data' => $dataSet,
			//'rawdata'      => $data , // control array
			'series' => $series,
			'ticks' => $data['numbersticks'],
			'total' => $data['total'],
			'fcolumntypeid' => $data['fcolumntypeid'],
			'sask' => $options['sask'],
			'mode' => $options['mode'],
			'renderer' => $this->params['charttype'],
			'parameters' => $parameters
		];
	}

	/**
	 * Fetch numbers ticks
	 *
	 * @since 1.8
	 *
	 * @param array $data
	 */
	protected function getNumbersTicks( array $data ) {

		// Only look for numeric values that have been stored
		$numerics = array_values( $data['series'] );

		// Find min and max values to determine the graphs axis parameter
		$maxValue = count( $numerics ) == 0 ? 0 : max( array_map( "max", $numerics ) );

		if ( $this->params['min'] === false ) {
			$minValue = count( $numerics ) == 0 ? 0 : min( array_map( "min", $numerics ) );
		} else {
			$minValue = $this->params['min'];
		}

		// Get ticks info
		$data['numbersticks'] = SRFjqPlot::getNumbersTicks( $minValue, $maxValue );
		$data['total'] = array_sum( array_map( "array_sum", $numerics ) );

		return $data;
	}

	/**
	 * Add resource definitions
	 *
	 * @since 1.8
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	protected function addResources() {
		// RL module
		switch ( $this->params['charttype'] ) {
			case 'bubble':
				SMWOutputs::requireResource( 'ext.srf.jqplot.bubble' );
				break;
			case 'donut':
				SMWOutputs::requireResource( 'ext.srf.jqplot.donut' );
				break;
			case 'scatter':
			case 'line':
			case 'bar':
				SMWOutputs::requireResource( 'ext.srf.jqplot.bar' );
				break;
		}

		// Trendline plugin
		if ( in_array( $this->params['trendline'], [ 'exp', 'linear' ] ) ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.trendline' );
		}

		// Cursor plugin
		if ( in_array( $this->params['cursor'], [ 'zoom', 'tooltip' ] ) ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.cursor' );
		}

		// Highlighter plugin
		if ( $this->params['highlighter'] ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.highlighter' );
		}

		// Enhancedlegend plugin
		if ( $this->params['chartlegend'] ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.enhancedlegend' );
		}

		// gridview plugin
		if ( in_array( $this->params['gridview'], [ 'tabs' ] ) ) {
			SMWOutputs::requireResource( 'ext.srf.util.grid' );
		}

		// Pointlabels plugin
		if ( in_array( $this->params['datalabels'], [ 'value', 'label', 'percent' ] ) ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.pointlabels' );
		}
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
	protected function getFormatOutput( array $data ) {

		$this->isHTML = true;

		static $statNr = 0;
		$chartID = 'jqplot-series-' . ++$statNr;

		// Encoding
		$requireHeadItem = [ $chartID => FormatJson::encode( $data ) ];
		SMWOutputs::requireHeadItem( $chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		// Add RL resources
		$this->addResources();

		// Processing placeholder
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// Conversion due to a string as value that can contain %
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
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = array_merge( parent::getParamDefinitions( $definitions ), SRFjqPlot::getCommonParams() );

		$params['infotext'] = [
			'message' => 'srf-paramdesc-infotext',
			'default' => '',
		];

		$params['stackseries'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-stackseries',
			'default' => false,
		];

		$params['group'] = [
			'message' => 'srf-paramdesc-group',
			'default' => 'subject',
			'values' => [ 'property', 'subject' ],
		];

		$params['grouplabel'] = [
			'message' => 'srf-paramdesc-grouplabel',
			'default' => 'subject',
			'values' => [ 'property', 'subject' ],
		];

		$params['charttype'] = [
			'message' => 'srf-paramdesc-charttype',
			'default' => 'bar',
			'values' => [ 'bar', 'line', 'donut', 'bubble', 'scatter' ],
		];

		$params['trendline'] = [
			'message' => 'srf-paramdesc-trendline',
			'default' => 'none',
			'values' => [ 'none', 'exp', 'linear' ],
		];

		$params['cursor'] = [
			'message' => 'srf-paramdesc-chartcursor',
			'default' => 'none',
			'values' => [ 'none', 'zoom', 'tooltip' ],
		];

		$params['gridview'] = [
			'message' => 'srf-paramdesc-gridview',
			'default' => 'none',
			'values' => [ 'none', 'tabs' ],
		];

		$params['hidezeroes'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-hidezeroes',
			'default' => false,
		];

		return $params;
	}
}
