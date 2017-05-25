<?php

namespace SRF;
use SMW, Html;

/**
 * DataTables and SMWAPI.
 *
 * @since 1.9
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 */
class DataTables extends SMW\ApiResultPrinter {

	/**
	 * Corresponding message name
	 *
	 */
	public function getName() {
		return $this->getContext()->msg( 'srf-printername-datatables' )->text();
	}

	/**
	 * Prepare html output
	 *
	 * @since 1.9
	 *
	 * @param array $data
	 * @return string
	 */
	protected function getHtml( array $data ) {

		// Init
		$this->isHTML = true;
		$id = $this->getId();

		// Add options
		$data['version'] = '0.2.5';

		// Encode data object
		$this->encode( $id, $data );

		// Init RL module
		$this->addResources( 'ext.srf.datatables' );

		// Element includes info, spinner, and container placeholder
		return Html::rawElement( 'div', [
				'class' => 'srf-datatables' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ),
				'data-theme' => $this->params['theme'],
			], Html::element( 'div', [
					'class' => 'top'
					]
				) . $this->loading() .
				Html::element( 'div', [
					'id' => $id,
					'class' => 'container',
					'style' => 'display:none;'
					]
				)
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
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		$params['theme'] = [
			'message' => 'srf-paramdesc-theme',
			'default' => 'bootstrap',
			'values' =>  [ 'bootstrap' ] // feel free to add more designs
		];

		return $params;
	}
}
