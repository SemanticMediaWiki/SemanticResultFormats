<?php

/**
 * A query printer for D3 charts using the D3 JavaScript library
 * and SMWAggregatablePrinter.
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
 * @file SRF_D3Chart.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */
class SRFD3Chart extends SMWAggregatablePrinter {

	/*
	 * @see SMWResultPrinter::getName
	 *
	 */
	public function getName() {
		return wfMsg( 'srf-printername-d3chart' );
	}

	/**
	 * @see SMWResultPrinter::getFormatOutput
	 *
	 * @since 1.8
	 *
	 * @param array $data label => value
	 */
	protected function getFormatOutput( array $data ) {
		if ( $this->params['layout'] === '' ) {
			return Xml::tags( 'span', array( 'class' => "error" ), wfMsgForContent( 'srf-error-missing-layout' ) );
		}

		// Object count
		static $statNr = 0;
		$d3chartID = 'd3-chart-' . ++$statNr;

		$this->isHTML = true;

		// Reorganize the raw data
		foreach ( $data as $name => $value ) {
			if ( $value >= $this->params['min'] ) {
				$dataObject[] = array( 'label' => $name , 'value' => $value );
			}
		}

		// Prepare transfer objects
		$d3data = array (
			'data' => $dataObject,
			'parameters' => array (
				'width'       => $this->params['width'],
				'height'      => $this->params['height'],
				'colorscheme' => $this->params['colorscheme'] ? $this->params['colorscheme'] : null,
				'charttitle'  => $this->params['charttitle'],
				'charttext'   => $this->params['charttext'],
				'datalabels'  => $this->params['datalabels']
			)
		);

		// Encode the data
		$requireHeadItem = array ( $d3chartID => FormatJson::encode( $d3data ) );
		SMWOutputs::requireHeadItem( $d3chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		// RL module
		$resource = 'ext.srf.d3.chart.' . $this->params['layout'];
		SMWOutputs::requireResource( $resource );

		// Chart/graph placeholder
		$attribs = array(
			'id'    => $d3chartID,
			'class' => 'container',
			'style' => 'display:none;'
		);

		$chart = Html::rawElement( 'div', $attribs, null );

		// Processing
		$processing = SRFUtils::htmlProcessingElement();

		// Beautify class selector
		$class = $this->params['layout'] ?  '-' . $this->params['layout'] : '';
		$class = $this->params['class'] ? $class . ' ' . $this->params['class'] : $class . ' d3-chart-common';

		// D3 wrappper
		$attribs = array( 'class' => 'srf-d3-chart' . $class );
		return Html::rawElement( 'div', $attribs , $processing . $chart );
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

		$params['min'] = new Parameter( 'min', Parameter::TYPE_INTEGER );
		$params['min']->setMessage( 'srf-paramdesc-minvalue' );
		$params['min']->setDefault( false, false );

		$params['layout'] = new Parameter( 'layout', Parameter::TYPE_STRING, '' );
		$params['layout']->setMessage( 'srf-paramdesc-layout' );
		$params['layout']->addCriteria( new CriterionInArray( 'treemap', 'bubble' ) );

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

		$params['datalabels'] = new Parameter( 'datalabels', Parameter::TYPE_STRING, '' );
		$params['datalabels']->setMessage( 'srf-paramdesc-datalabels' );
		$params['datalabels']->addCriteria( new CriterionInArray( 'value', 'label' ) );

		$params['colorscheme'] = new Parameter( 'colorscheme', Parameter::TYPE_STRING, '' );
		$params['colorscheme']->setMessage( 'srf-paramdesc-colorscheme' );
		$params['colorscheme']->addCriteria( new CriterionInArray( $GLOBALS['srfgColorScheme'] ) );

		$params['chartcolor'] = new Parameter( 'chartcolor', Parameter::TYPE_STRING, '' );
		$params['chartcolor']->setMessage( 'srf-paramdesc-chartcolor' );

		return $params;
	}
}