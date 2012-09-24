<?php

/**
 * A query printer that uses the dygraphs JavaScript library
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
 * @see http://www.semantic-mediawiki.org/wiki/Help:Flot_timeseries_chart
 *
 * @file
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */
class SRFDygraphs extends SMWResultPrinter {

	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf-printername-dygraphs' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {

		// Output mode is fixed
		$outputMode = SMW_OUTPUT_HTML;

		// Data processing
		$data = $this->getResultData( $result, $outputMode );

		// Post-data processing check
		if ( $data === array() ) {
			return $result->addErrors( array( wfMessage( 'srf-warn-empy-chart' )->inContentLanguage()->text() ) );
		} else {
			$options['sask'] = SRFUtils::htmlQueryResultLink( $this->getLink( $result, SMW_OUTPUT_HTML ) );
			return $this->getFormatOutput( $data, $options );
		}
	}

	/**
	 * Returns an array with numerical data
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getResultData( SMWQueryResult $result, $outputMode ) {
		$aggregatedValues = array();
		
		while ( $rows = $result->getNext() ) { // Objects (pages)
			$annotation = array();
			/**
			 * @var SMWResultArray $field
			 * @var SMWDataValue $dataValue
			 */
			foreach ( $rows as $field ) {

				// Use the subject marker to identify a possible data file
				// Should we check the file mime type getMimeType() as to be text/plain?
				$subject = $field->getResultSubject(); 
				if ( $this->params['datasource'] === 'file' && $subject->getTitle()->getNamespace() === NS_FILE ){
						$aggregatedValues['subject'] = SMWWikiPageValue::makePageFromTitle( $subject->getTitle() )->getLongHTMLText( $this->getLinker( $field->getResultSubject() ) );
						$aggregatedValues['url'] = wfFindFile( $subject->getTitle() )->getUrl();
				}

				// Proceed with those items where a property label is known otherwise
				// we are not able to use those as annotation object key identifiers
				if ( $field->getPrintRequest()->getLabel() !== ''){
					$property = $field->getPrintRequest()->getLabel();
				}else{
					continue;
				}

				while ( ( $dataValue = $field->getNextDataValue() ) !== false ) { // Data values

					// In case the data source points to an url, fetch the first url data source
					if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_URI && $this->params['datasource'] === 'url' ){
						$aggregatedValues['link'] = $dataValue->getShortHTMLText( $this->getLinker( false ) );
						$aggregatedValues['url'] = $dataValue->getURL();
						// We don't want the data source url to be published as annotation therefore we jump the column
						continue;
					}

					// For those items with a label, the label text should adhere conventions
					// outlined as the name indicates the array object key
					// @see  http://dygraphs.com/annotations.html
					if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_NUMBER ){
						// Set unit if available
						$dataValue->setOutputFormat( $this->params['unit'] );
						// Check if unit is available
						$annotation[$property] = $dataValue->getUnit() !== '' ? $dataValue->getShortWikiText() : $dataValue->getNumber() ;
					}else{
						$annotation[$property] = $dataValue->getWikiValue();
					}
				}
			}
			// Sum-up collected row items in a single array
			if ( $annotation !== array() ){
				$aggregatedValues['annotation'][] =  $annotation;
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
	 *
	 * @return string
	 */
	protected function getFormatOutput( $data, $options ) {

		// Object count
		static $statNr = 0;
		$chartID = 'srf-dygraphs-' . ++$statNr;

		$this->isHTML = true;

		// Reorganize the raw data
		if ( $this->params['datasource'] === 'page' ){
			foreach ( $data as $key => $values ) {
				$dataObject[] = array ( 'label' => $key, 'data' => $values );
			}
		}else{
				$dataObject['source'] = $data;
		}

		// Prepare transfer array
		$chartData = array (
			'data' => $dataObject,
			'sask' => $options['sask'],
			'parameters' => array (
				'width'        => $this->params['width'],
				'height'       => $this->params['height'],
				'xlabel'       => $this->params['xlabel'],
				'ylabel'       => $this->params['ylabel'],
				'charttitle'   => $this->params['charttitle'],
				'charttext'    => $this->params['charttext'],
				'datasource'   => $this->params['datasource'],
				'rollerperiod' => $this->params['mavg'],
				'datatable'    => $this->params['tableview'],
				'errorbar'     => $this->params['errorbar'],
			)
		);

		// Array encoding and output
		$requireHeadItem = array ( $chartID => FormatJson::encode( $chartData ) );
		SMWOutputs::requireHeadItem( $chartID, Skin::makeVariablesScript( $requireHeadItem ) );

		SMWOutputs::requireResource( 'ext.srf.dygraphs' );

		if ( $this->params['tableview'] === 'tabs' ) {
			SMWOutputs::requireResource( 'ext.srf.util.tableview' );
		}

		// Chart/graph placeholder
		$chart = Html::rawElement(
			'div',
			array('id' => $chartID, 'class' => 'container', 'style' => "display:none;" ),
			null
		);

		// Processing/loading image
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// Beautify class selector
		$class = $this->params['class'] ? ' ' . $this->params['class'] : ' dygraphs-common';

		// General output marker
		return Html::rawElement(
			'div',
			array( 'class' => 'srf-dygraphs' . $class	),
			$processing . $chart
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

		$params['datasource'] = array(
			'message' => 'srf-paramdesc-datasource',
			'default' => 'file',
			'values' => array( 'file', 'url' ),
		);

		$params['errorbar'] = array(
			'message' => 'srf-paramdesc-errorbar',
			'default' => '',
			'values' => array( 'fraction', 'sigma', 'range' ),
		);

		$params['min'] = array(
			'type' => 'integer',
			'message' => 'srf-paramdesc-minvalue',
			'default' => '',
		);

		$params['mavg'] = array(
			'type' => 'integer',
			'message' => 'srf-paramdesc-movingaverage',
			'default' => 14,
			'lowerbound' => 0,
		);

		$params['tableview'] = array(
			'message' => 'srf-paramdesc-tableview',
			'default' => 'none',
			'values' => array( 'none' , 'tabs' ),
		);

		$params['infotext'] = array(
			'message' => 'srf-paramdesc-infotext',
			'default' => '',
		);

		$params['unit'] = array(
			'message' => 'srf-paramdesc-unit',
			'default' => '',
		);

		$params['height'] = array(
			'type' => 'integer',
			'message' => 'srf_paramdesc_chartheight',
			'default' => 400,
			'lowerbound' => 1,
		);

		$params['width'] = array(
			'message' => 'srf_paramdesc_chartwidth',
			'default' => '100%',
		);

		$params['charttitle'] = array(
			'message' => 'srf_paramdesc_charttitle',
			'default' => '',
		);

		$params['charttext'] = array(
			'message' => 'srf-paramdesc-charttext',
			'default' => '',
		);

		$params['ylabel'] = array(
			'message' => 'srf-paramdesc-yaxislabel',
			'default' => '',
		);

		$params['xlabel'] = array(
			'message' => 'srf-paramdesc-xaxislabel',
			'default' => '',
		);

		$params['class'] = array(
			'message' => 'srf-paramdesc-class',
			'default' => '',
		);

		return $params;
	}
}