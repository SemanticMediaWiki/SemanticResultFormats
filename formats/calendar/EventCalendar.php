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
			array( 'class' => 'srf-eventcalendar', 'data-external-class' => ( $this->params['class'] ? $this->params['class'] : '' ) ),
				Html::element( 'div', array( 'class' => 'srf-top' ), '' ) .  $this->loading() . Html::element(
				'div',
				array( 'id' => $id, 'class' => 'srf-container', 'style' => 'display:none;' ),
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

		$params['defaultview'] = array(
			'message' => 'srf-paramdesc-calendardefaultview',
			'default' => 'month',
			'values' => array ( 'month', 'basicweek', 'basicday', 'agendaweek', 'agendaday' )
		);

		$params['firstday'] = array(
			'message' => 'srf-paramdesc-calendarfirstday',
			'default' => 'Sunday',
			'values' => array ( "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" )
		);

		$params['start'] = array(
			'message' => 'srf-paramdesc-calendarstart',
			'default' => 'current',
			'values' => array ( 'current', 'earliest', 'latest' )
		);

		$params['legend'] = array(
			'message' => 'srf-paramdesc-calendarlegend',
			'default' => 'none',
			'values' => array ( 'none', 'top', 'bottom', 'tooltip', 'pane' )
		);

		$params['dayview'] = array(
			'type' => 'boolean',
			'message' => 'srf-paramdesc-dayview',
			'default' => false
		);

		$params['class'] = array(
			'message' => 'srf-paramdesc-class',
			'default' => '',
		);

		$params['theme'] = array(
			'message' => 'srf-paramdesc-theme',
			'default' => 'basic',
			'values' => array ( 'basic', 'vector' )
		);

		return $params;
	}
}
