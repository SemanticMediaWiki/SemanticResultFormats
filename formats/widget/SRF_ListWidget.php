<?php

use SMW\Query\ResultPrinters\ListResultPrinter\ListResultBuilder;
use SMW\Query\ResultPrinters\ResultPrinter;

/**
 * Extends the list result printer (SMW_QP_List.php) with a JavaScript
 * navigation widget
 *
 * @since 1.8
 *
 * @author mwjames
 *
 * @ingroup SemanticResultFormats
 * @file SRF_ListWidget.php
 */
class SRFListWidget extends ResultPrinter {

	/**
	 * Get a human readable label for this printer.
	 *
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf-printername-listwidget' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $res
	 * @param array $params
	 * @param $outputmode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		// Initialize
		static $statNr = 0;
		//$this->isHTML = true;

		$listType = $this->params[ 'listtype' ] === 'ordered' || $this->params[ 'listtype' ] === 'ol' ? 'ol' : 'ul';

		$builder = new ListResultBuilder( $res, $this->mLinker );

		$builder->set( $this->params );
		$builder->set( [
			'format' => $listType,
			'link-first' => $this->mLinkFirst,
			'link-others' => $this->mLinkOthers,
			'show-headers' => $this->mShowHeaders,
		] );

		// Get results from SMWListResultPrinter
		$result = $builder->getResultText();

		// Count widgets
		$listwidgetID = 'listwidget-' . ++$statNr;

		// OL/UL container items
		$result = Html::rawElement(
			'div',
			[
				'id' => $listwidgetID,
				'class' => 'listwidget-container',
				'style' => 'display:none; position: relative; margin-bottom:5px; margin-top:5px;'
			],
			$result
		);

		// Placeholder
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// RL module
		$resource = 'ext.srf.listwidget.' . $this->params['widget'];
		SMWOutputs::requireResource( $resource );

		// Wrap results
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-listwidget ' . htmlspecialchars( $this->params['class'] ),
				'data-listtype' => $listType,
				'data-widget' => $this->params['widget'],
				'data-pageitems' => $this->params['pageitems'],
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

		$params['class'] = [
			'name' => 'class',
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		$params['listtype'] = [
			'name' => 'listtype',
			'message' => 'srf-paramdesc-listtype',
			'values' => [ 'unordered', 'ordered' ],
			'default' => 'unordered'
		];

		$params['widget'] = [
			'name' => 'widget',
			'message' => 'srf-paramdesc-widget',
			'values' => [ 'alphabet', 'menu', 'pagination' ],
			'default' => 'alphabet'
		];

		$params['pageitems'] = [
			'type' => 'integer',
			'name' => 'pageitems',
			'message' => 'srf-paramdesc-pageitems',
			'default' => 5,
		];

		return $params;
	}
}