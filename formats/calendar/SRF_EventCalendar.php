<?php

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
 * @file SRF_EventCalendar.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @author mwjames
 */
class SRFEventCalendar extends SMWResultPrinter {

	/**
	 * Corresponding message name
	 *
	 */
	public function getName() {
		return wfMsg( 'srf-printername-eventcalendar' );
	}

	/**
	 * Returns string of the query result
	 *
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {

		// Fetch the data set
		$data = $this->getEventData( $result, $outputMode );

		// Check data availability
		if ( $data === array() ) {
			return $result->addErrors( array( wfMsgForContent( 'srf-error-empty-calendar' ) ) );
		} else {
			return $this->getCalendarOutput( $data );
		}
	}

	/**
	 * Returns an array of events
	 *
	 * The array index corresponds to FullCalendar eventObject specification
	 *
	 * id - Uniquely identifies the given event
	 * title - Required, The text on an event's element
	 * start - Required, The date/time an event begins
	 * end - Optional, The date/time an event ends
	 * url - Optional, A URL that will be used as href for when the event is clicked
	 * className - A CSS class (or array of classes) that will be attached to this event's element
	 * color - Sets an event's background and border color
	 * description is a non-standard Event Object field
	 * allDay if set false it will show the time
	 *
	 * @see http://arshaw.com/fullcalendar/docs/event_data/Event_Object/
	 * @see http://arshaw.com/fullcalendar/docs/event_rendering/eventRender/
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $res
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getEventData( SMWQueryResult $res, $outputMode ) {
		$data = array();

		while ( $row = $res->getNext() ) {
			// Loop over available fields (properties)
			$rowData = array();
			$rowDesc = array();
			$description = false;

			/**
			 * Loop over the subject row
			 *
			 * @var SMWResultArray $field
			 */
			foreach ( $row as $field ) {
				// Property label
				$property = $field->getPrintRequest()->getLabel();
				// @todo FIXME: Unused local variable.
				$subject = $field->getResultSubject()->getTitle()->getText();

				/**
				 * Loop over all values for a property
				 *
				 * @var SMWDataValue $object
				 */
				while ( ( $object = $field->getNextDataValue() ) !== false ) {

					if ( $object->getDataItem()->getDIType() == SMWDataItem::TYPE_WIKIPAGE ) {

						// Catch event icon
						if ( $field->getPrintRequest()->getLabel() === $this->params['eventicon'] && $this->params['eventicon'] !== '' ) {
							$rowData['eventicon'] = $object->getWikiValue();
						} else {
							$rowData['title'] = $object->getWikiValue();
							$this->getLinker( $this->mLinker ) !== null ? $rowData['url'] = $object->getTitle()->getFullURL() : '';
						}
					} elseif ( $object->getDataItem()->getDIType() == SMWDataItem::TYPE_TIME ){
						// If the start date was set earlier we interfere that the next date
						// we found in the same row is an end date
						if ( array_key_exists( 'start', $rowData ) ) {
							$rowData['end'] = $object->getISO8601Date();
							// No time for an event means it is an all day event
							$rowData['allDay'] = $object->getTimeString() === '00:00:00' ? true : false;
						} else {
							$rowData['start'] = $object->getISO8601Date();
						}
					} elseif ( $object->getDataItem()->getDIType() == SMWDataItem::TYPE_URI ){
						// Get holiday feed url (google calendar etc.)
						// if ( $field->getPrintRequest()->getLabel() === $this->params['holidaycal'] && $this->params['holidaycal'] !== '' ) {
						//	$this->holidayCal = $object->getURI();
						// }
					} else {
						// If one of the leftover properties is an assigned event color property
						if ( $field->getPrintRequest()->getLabel() === $this->params['eventcolor'] && $this->params['eventcolor'] !== '' ) {
							$rowData['color'] = $object->getWikiValue();
						} elseif ( array_key_exists( 'title', $rowData ) && $description ) {
							// Title has already been set therefore add description
							$rowDesc[] = $this->mShowHeaders === SMW_HEADERS_HIDE ? $object->getWikiValue() : $property . ': ' . $object->getWikiValue();
						} else {
							// Override the title (normally used to make sure that subobject titles
							// can use a different label other than the subject label itself)
							$rowData['title'] = $object->getWikiValue();
							$description = true;
						}
					}
				}
				// Pull all descriptions into one field
				$rowData['description'] = implode (', ', $rowDesc );
			}
			// Check if the array has actual data
			if ( $rowData !== array() ) {
				$data[]= $rowData;
			}
		}
		return $data;
	}

	/**
	 * Prepare calendar output
	 *
	 * @since 1.8
	 *
	 * @param array $events
	 */
	protected function getCalendarOutput( array $events ) {

		// Init
		static $statNr = 0;
		$calendarID = 'calendar-' . ++$statNr;

		$this->isHTML = true;

		// Somewhat silly but we have to convert the names otherwise fullCalendar throws an error
		$defaultVS   = array ( 'day', 'week');
		$defaultVR   = array ( 'Day', 'Week');
		$defaultView = str_replace ( $defaultVS, $defaultVR, $this->params['defaultview'] );

		// Add options
		$dataObject['events']  = $events;
		$dataObject['options'] = array(
			'defaultview' => $defaultView,
			'dayview'     => $this->params['dayview'],
			'firstday'    => date( 'N', strtotime( $this->params['firstday'] ) ),
			'theme'       => in_array( $this->params['theme'], array( 'vector' ) ),
			'views' => 'month,' .
				( strpos( $defaultView, 'Week') === false ? 'basicWeek' : $defaultView ) . ',' .
				( strpos( $defaultView, 'Day' ) === false ? 'agendaDay' : $defaultView ),
		);

		// Encode data objects
		$requireHeadItem = array ( $calendarID => FormatJson::encode( $dataObject ) );
		SMWOutputs::requireHeadItem( $calendarID, Skin::makeVariablesScript($requireHeadItem ) );

		// RL module
		SMWOutputs::requireResource( 'ext.srf.eventcalendar' );

		// Processing placeholder
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// Container placeholder
		$calendar = Html::rawElement(
			'div',
			array( 'id' => $calendarID, 'class' => 'container', 'style' => 'display:none;' ),
			null
		);

		// Beautify class selector
		$class = $this->params['class'] ? ' ' . $this->params['class'] : '';

		// General wrappper
		return Html::rawElement(
			'div',
			array( 'class' => 'srf-eventcalendar' . $class ),
			$processing . $calendar
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

		$params['firstday'] = array(
			'message' => 'srf-paramdesc-calendarfirstday',
			'default' => 'Sunday',
			'values' => array ( "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" )
		);

		$params['defaultview'] = array(
			'message' => 'srf-paramdesc-calendardefaultview',
			'default' => 'month',
			'values' => array ( 'month', 'basicweek', 'basicday', 'agendaweek', 'agendaday' )
		);

		$params['eventicon'] = array(
			'message' => 'srf-paramdesc-eventicon',
			'default' => '',
		);

		$params['eventcolor'] = array(
			'message' => 'srf-paramdesc-eventcolor',
			'default' => '',
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