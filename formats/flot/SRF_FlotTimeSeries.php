<?php

/**
 * A query printer for timeseries using the flot plotting JavaScript library
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
 * @file SRF_FlotTimeseries.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */
class SRFFlotTimeseries extends SMWResultPrinter {

	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return wfMsg( 'srf-printername-timeseries' );
	}

	/**
	 * Returns an array with the numerical data in the query result.
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {
		if ( $this->params['layout'] === '' ) {
			return $result->addErrors( array( wfMsgForContent( 'srf-error-missing-layout' ) ) );
		}

		$data = $this->getAggregatedTimeSeries( $result, $outputMode );

		if ( count( $data ) == 0 ) {
			return $result->addErrors( array( wfMsgForContent( 'srf-warn-empy-chart' ) ) );
		} else {
			return $this->getFormatOutput( $data );
		}
	}

	/**
	 * Returns an array with the numerical data in the query result.
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getAggregatedTimeSeries( SMWQueryResult $result, $outputMode ) {
		$values = array();
		$aggregatedValues = array ();

		while ( /* array of SMWResultArray */ $row = $result->getNext() ) { // Objects (pages)
			$timeStamp = '';
			$value     = '';
			$series = array();

			foreach ( $row as /* SMWResultArray */ $field ) {
				$value  = array();
				$sum    = array();
				$rowSum = array();

				// Group by subject (page object)  or property
				if ( $this->params['groupedby'] == 'subject' ){
					$groupedBy = $field->getResultSubject()->getTitle()->getText();
				} else {
					$groupedBy = $field->getPrintRequest()->getLabel();
				}

				while ( ( /* SMWDataValue */ $dataValue = $field->getNextDataValue() ) !== false ) { // Data values

					// Find the timestamp
					if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_TIME ){
						// We work with a timestamp, we have to use intval because DataItem
						// returns a string but we want a numeric representation of the timestamp
						$timeStamp = intval( $dataValue->getDataItem()->getMwTimestamp() );
					}

					// Find the values (numbers only)
					if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_NUMBER ){
						$sum[] = $dataValue->getNumber();
					}
				}
				// Aggegate individual values into a sum
				$rowSum = array_sum( $sum );

				// Check the sum and threshold/min
				if ( $timeStamp !== '' && $rowSum == true && $rowSum >= $this->params['min'] ) {
					$series[$groupedBy] = array ( $timeStamp , $rowSum ) ;
				}
			}
				$values[] = $series ;
		}

		// Re-assign values according to their group
		foreach ( $values as $key => $value ) {
			foreach ( $values[$key] as $row => $rowvalue ) {
					$aggregatedValues[$row][] = $rowvalue;
			}
		}
		return $aggregatedValues;
	}

	/**
	 * Prepare data for the output
	 *
	 * @since 1.8
	 *
	 * @param array $data
	 */
	protected function getFormatOutput( array $data ) {

		// Object count
		static $statNr = 0;
		$chartID = 'flot-timeseries-' . ++$statNr;

		$this->isHTML = true;

		// Reorganize the raw data
			foreach ( $data as $key => $values ) {
			$dataObject[] = array ( 'label' => $key, 'data' => $values );
		}

		// Prepare transfer array
		$chartData = array (
			'data' => $dataObject,
			'parameters' => array (
				'width'       => $this->params['width'],
				'height'      => $this->params['height'],
				'charttitle'  => $this->params['charttitle'],
				'charttext'   => $this->params['charttext'],
				'layout'      => $this->params['layout'],
				'groupedby'   => $this->params['groupedby'],
				'datatable'   => $this->params['datatable'],
				'zoom'        => $this->params['zoom'],
			)
		);

		// Array encoding and output
		$requireHeadItem = array ( $chartID => FormatJson::encode( $chartData ) );
		SMWOutputs::requireHeadItem( $chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		// RL module
		$resource = 'ext.srf.flot.timeseries';
		SMWOutputs::requireResource( $resource );

		// Chart/graph placeholder
		$chart = Html::rawElement( 'div', array(
			'id' => $chartID,
			'class' => 'container',
			'style' => "display:none;"
			), null
		);

		// Processing/loading image
		$processing = SRFUtils::htmlProcessingElement();

		// Beautify class selector
		//$class = $this->params['layout'] ?  '-' . $this->params['layout'] : '';
		$class = $this->params['class'] ? ' ' . $this->params['class'] : ' flot-chart-common';

		// Return D3 wrappper
		return Html::rawElement( 'div', array(
			'class' => 'srf-flot-timeseries' . $class
			), $processing . $chart
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

		$params['layout'] = new Parameter( 'layout', Parameter::TYPE_STRING, 'line' );
		$params['layout']->setMessage( 'srf-paramdesc-layout' );
		$params['layout']->addCriteria( new CriterionInArray( 'line' , 'bar' ) );

		$params['min'] = new Parameter( 'min', Parameter::TYPE_INTEGER );
		$params['min']->setMessage( 'srf-paramdesc-min' );
		$params['min']->setDefault( false, false );

		$params['groupedby'] = new Parameter( 'groupedby', Parameter::TYPE_STRING, 'property' );
		$params['groupedby']->setMessage( 'srf-paramdesc-groupedby' );
		$params['groupedby']->addCriteria( new CriterionInArray( 'property' , 'subject' ) );

		$params['zoom'] = new Parameter( 'zoom', Parameter::TYPE_STRING, 'bottom' );
		$params['zoom']->setMessage( 'srf-paramdesc-zoom' );
		$params['zoom']->addCriteria( new CriterionInArray( 'none' , 'bottom', 'top' ) );

		$params['datatable'] = new Parameter( 'datatable', Parameter::TYPE_STRING, 'bottom' );
		$params['datatable']->setMessage( 'srf-paramdesc-datatable' );
		$params['datatable']->addCriteria( new CriterionInArray( 'none' , 'bottom', 'top' ) );

		$params['height'] = new Parameter( 'height', Parameter::TYPE_INTEGER, 400 );
		$params['height']->setMessage( 'srf_paramdesc_chartheight' );

		$params['width'] = new Parameter( 'width', Parameter::TYPE_INTEGER, 400 );
		$params['width']->setMessage( 'srf_paramdesc_chartwidth' );

		$params['charttitle'] = new Parameter( 'charttitle', Parameter::TYPE_STRING, '' );
		$params['charttitle']->setMessage( 'srf_paramdesc_charttitle' );

		$params['charttext'] = new Parameter( 'charttext', Parameter::TYPE_STRING, '' );
		$params['charttext']->setMessage( 'srf-paramdesc-charttext' );

		$params['class'] = new Parameter( 'class', Parameter::TYPE_STRING );
		$params['class']->setMessage( 'srf-paramdesc-class' );
		$params['class']->setDefault( '' );

		return $params;
	}

}