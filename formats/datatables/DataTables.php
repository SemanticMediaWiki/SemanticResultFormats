<?php

namespace SRF;

use Html;
use SMW\ResultPrinter;
use SMWQueryResult as QueryResult;

/**
 * DataTables and SMWAPI.
 *
 * @since 1.9
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 */
class DataTables extends ResultPrinter {

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->msg( 'srf-printername-datatables' )->text();
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
			'default' => 'bootstrap',
			'values' => [ 'bootstrap' ] // feel free to add more designs
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

		// Add options
		$data['version'] = '0.2.5';

		// Encode data object
		$resourceFormatter->encode( $id, $data );

		// Init RL module
		$resourceFormatter->registerResources( [ 'ext.srf.datatables' ] );

		// Element includes info, spinner, and container placeholder
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-datatables' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ),
				'data-theme' => $this->params['theme'],
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
