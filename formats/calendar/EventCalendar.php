<?php

namespace SRF;

use SMW\ApiResultPrinter;
use Html;

/**
 * An event calendar printer using the FullCalendar JavaScript library
 * and SMWAPI.
 *
 * @since 1.9
 *
 * @file
 * @ingroup QueryPrinter
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */

/**
 * Query printer supporting a JavaScript Event calendar using the
 * Semantic MediaWiki Api
 *
 * @ingroup QueryPrinter
 */
class EventCalendar extends ApiResultPrinter {

	/**
	 * Corresponding message name
	 *
	 */
	public function getName() {
		return $this->msg( 'srf-printername-eventcalendar' )->text();
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
		$data['version'] = '0.8.0';

		// Encode data object
		$this->encode( $id, $data );

		// Init RL module
		$this->addResources( 'ext.srf.eventcalendar' );

		// Element includes info, spinner, and container placeholder
		return Html::rawElement(
			'div',
			[ 'class' => 'srf-eventcalendar', 'data-external-class' => ( $this->params['class'] ? $this->params['class'] : '' ) ],
				Html::element( 'div', [ 'class' => 'srf-top' ], '' ) .  $this->loading() . Html::element(
				'div',
				[ 'id' => $id, 'class' => 'srf-container', 'style' => 'display:none;' ],
				''
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

		$params['defaultview'] = [
			'message' => 'srf-paramdesc-calendardefaultview',
			'default' => 'month',
			'values' =>  [ 'month', 'basicweek', 'basicday', 'agendaweek', 'agendaday' ]
		];

		$params['firstday'] = [
			'message' => 'srf-paramdesc-calendarfirstday',
			'default' => 'Sunday',
			'values' =>  [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ]
		];

		$params['start'] = [
			'message' => 'srf-paramdesc-calendarstart',
			'default' => 'current',
			'values' =>  [ 'current', 'earliest', 'latest' ]
		];

		$params['legend'] = [
			'message' => 'srf-paramdesc-calendarlegend',
			'default' => 'none',
			'values' =>  [ 'none', 'top', 'bottom', 'tooltip', 'pane' ]
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
			'values' =>  [ 'basic', 'vector' ]
		];

		$params['clicktarget'] = [
			'message' => 'srf-paramdesc-clicktarget',
			'default' => 'none'
		];		
		return $params;
	}
}
