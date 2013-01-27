<?php

namespace SRF;
use SMW, Html;

/**
 * An event calendar printer using the FullCalendar JavaScript library
 * and SMWAPI.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 1.9
 *
 * @file
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 */
class EventCalendar extends SMW\ApiResultPrinter {

	/**
	 * Corresponding message name
	 *
	 */
	public function getName() {
		return $this->getContext()->msg( 'srf-printername-eventcalendar' )->text();
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
		$data['version'] = '0.7.4';

		// The boolean value wasn't caught earlier with all other parameters
		$data['query']['ask']['parameters']['dayview'] = $this->params['dayview'];

		// Encode data object
		$this->encode( $id, $data );

		// Init RL module
		$this->addResources( 'ext.srf.eventcalendar' );

		// Element includes info, spinner, and container placeholder
		return Html::rawElement(
			'div',
			array( 'class' => 'srf-eventcalendar' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ) ),
				Html::element( 'div', array( 'class' => 'info' ), '' ) .  $this->loading() . Html::element(
				'div',
				array( 'id' => $id, 'class' => 'container', 'style' => 'display:none;' ),
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
