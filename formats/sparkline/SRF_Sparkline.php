<?php

/**
 * A query printer for sparklines (small inline charts) using the sparkline
 * JavaScript library.
 *
 * @since 1.8
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 */
class SRFSparkline extends SMWAggregatablePrinter {

	/**
	 * Corresponding message name
	 *
	 */
	public function getName() {
		return wfMessage( 'srf-printername-sparkline' )->text();
	}

	/**
	 * Prepare data output
	 *
	 * @since 1.8
	 *
	 * @param array $data label => value
	 */
	protected function getFormatOutput( array $data ) {

		//Init
		$dataObject = [];

		static $statNr = 0;
		$chartID = 'sparkline-' . $this->params['charttype'] . '-' . ++$statNr;

		$this->isHTML = true;

		// Prepare data array
		foreach ( $data as $key => $value ) {
			if ( $value >= $this->params['min'] ) {
				$dataObject['label'][] = $key;
				$dataObject['value'][] = $value;
			}
		}

		$dataObject['charttype'] = $this->params['charttype'];

		// Encode data objects
		$requireHeadItem = [ $chartID => FormatJson::encode( $dataObject ) ];
		SMWOutputs::requireHeadItem( $chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		// RL module
		SMWOutputs::requireResource( 'ext.srf.sparkline' );

		// Processing placeholder
		$processing = SRFUtils::htmlProcessingElement( false );

		// Chart/graph placeholder
		$chart = Html::rawElement(
			'div',
			[
				'id' => $chartID,
				'class' => 'sparkline-container',
				'style' => "display:none;"
			],
			null
		);

		// Beautify class selector
		$class = $this->params['class'] ? ' ' . $this->params['class'] : '';

		// Chart/graph wrappper
		return Html::rawElement(
			'span',
			[
				'class' => 'srf-sparkline' . $class,
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
			'default' => 'bar',
			'values' => [ 'bar', 'line', 'pie', 'discrete' ]
		];

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		return $params;
	}
}