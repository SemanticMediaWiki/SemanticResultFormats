<?php

namespace SRF;

use Html;
use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\ResultPrinter;

/**
 * An event calendar printer using the FullCalendar JavaScript library
 * and SMWAPI.
 *
 * @since 1.9
 *
 * @file
 * @ingroup QueryPrinter
 *
 * @license GPL-2.0-or-later
 * @author mwjames
 */

/**
 * Query printer supporting a JavaScript Event calendar using the
 * Semantic MediaWiki Api
 *
 * @ingroup QueryPrinter
 */
class EventCalendar extends ResultPrinter {

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->msg( 'srf-printername-eventcalendar' )->text();
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

		$params['defaultview'] = [
			'message' => 'srf-paramdesc-calendardefaultview',
			'default' => 'month',
			'values' => [
				'month',
				'basicweek',
				'basicday',
				'agendaweek',
				'agendaday',
				'listday',
				'listweek',
				'listmonth' ]
		];

		$params['views'] = [
			'message' => 'srf-paramdesc-calendarviews',
			'default' => 'month,basicWeek,agendaDay'
		];
		$params['firstday'] = [
			'message' => 'srf-paramdesc-calendarfirstday',
			'default' => 'Sunday',
			'values' => [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ]
		];

		$params['start'] = [
			'message' => 'srf-paramdesc-calendarstart',
			'default' => 'current',
			'values' => [ 'current', 'earliest', 'latest' ]
		];

		$params['legend'] = [
			'message' => 'srf-paramdesc-calendarlegend',
			'default' => 'none',
			'values' => [ 'none', 'top', 'bottom', 'tooltip', 'pane' ]
		];

		$params['dayview'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-dayview',
			'default' => false
		];

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		$params['theme'] = [
			'message' => 'srf-paramdesc-theme',
			'default' => 'basic',
			'values' => [ 'basic', 'vector' ]
		];

		$params['clicktarget'] = [
			'message' => 'srf-paramdesc-clicktarget',
			'default' => 'none'
		];

		$params['includeend'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-includeend',
			'default' => true
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
		$data['version'] = '0.8.0';

		// Encode data object
		$resourceFormatter->encode( $id, $data );

		// Init RL module
		$resourceFormatter->registerResources( [ 'ext.srf.eventcalendar' ] );

		// Element includes info, spinner, and container placeholder
		return Html::rawElement(
			'div',
			[
				'class' => 'srf-eventcalendar',
				'data-external-class' => ( $this->params['class'] ? $this->params['class'] : '' )
			],
			Html::element(
				'div',
				[
					'class' => 'srf-top'
				],
				''
			) . $resourceFormatter->placeholder() . Html::element(
				'div',
				[
					'id' => $id,
					'class' => 'srf-container',
					'style' => 'display:none;'
				]
			)
		);
	}

}
