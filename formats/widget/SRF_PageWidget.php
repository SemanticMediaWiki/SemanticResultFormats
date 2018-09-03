<?php

/**
 * Extends the SMWEmbeddedResultPrinter with a JavaScript carousel widget
 *
 * @since 1.8
 *
 * @author mwjames
 *
 * @ingroup SemanticResultFormats
 * @file
 */
class SRFPageWidget extends SMWEmbeddedResultPrinter {

	/**
	 * Get a human readable label for this printer.
	 *
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf-printername-pagewidget' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $res
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputMode ) {

		// Initialize
		static $statNr = 0;

		// Get results from SMWListResultPrinter
		$result = parent::getResultText( $res, $outputMode );

		// Count widgets
		$widgetID = 'pagewidget-' . ++$statNr;

		// Container items
		$result = Html::rawElement(
			'div',
			[
				'id' => $widgetID,
				'class' => 'pagewidget-container',
				'data-embedonly' => $this->params['embedonly'],
				'style' => 'display:none;'
			],
			$result
		);

		// Placeholder
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// RL module
		SMWOutputs::requireResource( 'ext.srf.pagewidget.carousel' );

		// Beautify class selector
		$class = $this->params['class'] ? ' ' . $this->params['class'] : '';

		// Wrap results
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-pagewidget' . $class,
			],
			$processing . $result
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

		$params['embedformat'] = [
			'message' => 'smw-paramdesc-embedformat',
			'default' => 'ul',
			'values' => [ 'ul' ],
		];

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		$params['widget'] = [
			'message' => 'srf-paramdesc-widget',
			'default' => 'carousel',
			'values' => [ 'carousel' ],
		];

		return $params;
	}
}
