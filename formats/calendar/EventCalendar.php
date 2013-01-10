<?php

namespace SRF;
use SMWResultPrinter, SMWQueryResult, SMWDataItem, SMWOutputs, SRFUtils, SMWQuery;
use Html, FormatJson, Skin;

/**
 * An event calendar printer using the FullCalendar JavaScript library.
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
 * @since 1.8
 *
 * @file
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 */
class EventCalendar extends SMWResultPrinter {

	/**
	 * Corresponding message name
	 *
	 */
	public function getName() {
		return wfMessage( 'srf-printername-eventcalendar' )->text();
	}

	/**
	 * Returns string of the query result
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $queryResult, $outputMode ) {

		// Result object serialization
		$data['query']['result'] = $queryResult->toArray();

		// The serialization method allways returns a meta/count object
		if ( $data['query']['result']['meta']['count'] === 0 ) {
			$queryResult->addErrors( array( wfMessage( 'smw_result_noresults' )->inContentLanguage()->text() ) );
			return '';
		} else {

			// Add query details
			$data['query']['ask'] = $queryResult->getQuery()->toArray();

			// Add additional parameters that are only known to this printer
			foreach ( $this->params as $key => $value ) {
				if ( is_string( $value ) ) {
					$data['query']['ask']['parameters'][$key] = $value;
				}
			}

			return $this->getHtml( $data );
		}
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
		static $statNr = 0;
		$calendarID = 'srf-calendar-' . ++$statNr;

		$this->isHTML = true;

		// Consistency of names otherwise fullCalendar throws an error
		$defaultVS   = array ( 'day', 'week');
		$defaultVR   = array ( 'Day', 'Week');
		$defaultView = str_replace ( $defaultVS, $defaultVR, $this->params['defaultview'] );

		// Add options
		$data['options'] = array(
			'version'       => '0.7.2',
			'legend'        => $this->params['legend'],
			'defaultview'   => $defaultView,
			'start'         => $this->params['start'],
			'dayview'       => $this->params['dayview'],
			'firstday'      => date( 'N', strtotime( $this->params['firstday'] ) ),
			'theme'         => in_array( $this->params['theme'], array( 'vector' ) ),
			'views' => 'month,' .
				( strpos( $defaultView, 'Week') === false ? 'basicWeek' : $defaultView ) . ',' .
				( strpos( $defaultView, 'Day' ) === false ? 'agendaDay' : $defaultView ),
		);

		// Encode data object
		$requireHeadItem = array ( $calendarID => FormatJson::encode( $data ) );
		SMWOutputs::requireHeadItem( $calendarID, Skin::makeVariablesScript($requireHeadItem ) );

		// Init RL module
		SMWOutputs::requireResource( 'ext.srf.eventcalendar' );

		// Processing placeholder
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// General and Ccontainer placeholder
		return Html::rawElement(
			'div',
			array( 'class' => 'srf-eventcalendar' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ) ),
			 Html::element( 'div', array( 'class' => 'info' ), null ) . $processing . Html::element(
				'div',
				array( 'id' => $calendarID, 'class' => 'container', 'style' => 'display:none;' ),
				null
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
			'default' => '',
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