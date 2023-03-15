<?php

namespace SRF;

use Html;
use SMW\ResultPrinter;
use SMWQueryResult as QueryResult;

/**
 * DataTables and SMWAPI.
 *
 * @since 1.9
 * @license GPL-2.0-or-later
 *
 * @author mwjames
 * @author thomas-topway-it
 */
class DataTablesLegacy extends ResultPrinter {

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->msg( 'srf-printername-datatables-legacy' )->text();
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		$params['theme'] = [
			'message' => 'srf-paramdesc-theme',
			'default' => 'basic',
			'values' => [ 'bootstrap', 'basic' ] // feel free to add more designs
		];

		$params['pagelength'] = [
			'message' => 'srf-paramdesc-pagelength',
			'default' => '20',
		];

		$params['lengthmenu'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-lengthmenu',
			'default' => '',
		];

		$params['columnstype'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-columnstype',
			'default' => '',
		];

		return $params;
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {
		$resourceFormatter = new ResourceFormatter();
		$data = $resourceFormatter->getData( $res, $outputmode, $this->params );

		$this->isHTML = true;
		$id = $resourceFormatter->session();

		$context = \RequestContext::getMain();
	
		// the following unfortunately does not work with $wgCachePages
		// so we append it in the html elements' attribute below
		// $context->getOutput()->addJsConfigVars( [
		// 	'wgCategoryCollation' => $GLOBALS['wgCategoryCollation'],
		// 	'smwgEntityCollation' => $GLOBALS['smwgEntityCollation'],
		// ]);

		// Add options
		$data['version'] = '0.2.5';

		// Encode data object
		$resourceFormatter->encode( $id, $data );

		// Init RL module
		$resourceFormatter->registerResources( [
			'ext.srf.datatablesLegacy',
			'ext.srf.datatablesLegacy.' . $this->params['theme']
		] );

		// Element includes info, spinner, and container placeholder
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-datatables' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ),
				'data-theme' => $this->params['theme'],
				'data-columnstype' => ( !empty( $this->params['columnstype'] ) ? $this->params['columnstype'] : null ),
				'data-collation' => !empty( $GLOBALS['smwgEntityCollation'] ) ? $GLOBALS['smwgEntityCollation'] : $GLOBALS['wgCategoryCollation'],
			],
			Html::element(
				'div',
				[
					'class' => 'top'
				],
				''
			) . $resourceFormatter->placeholder() . Html::element(
				'div',
				[
					'id' => $id,
					'class' => 'container',
					'style' => 'display:none;'
				]
			)
		);
	}

}
