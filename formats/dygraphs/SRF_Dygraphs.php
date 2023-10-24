<?php
use MediaWiki\MediaWikiServices;

/**
 * A query printer that uses the dygraphs JavaScript library
 *
 * @see http://www.semantic-mediawiki.org/wiki/Help:Flot_timeseries_chart
 * @license GPL-2.0-or-later
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
		if ( $data === [] ) {
			return $result->addErrors( [ wfMessage( 'srf-warn-empy-chart' )->inContentLanguage()->text() ] );
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
		$aggregatedValues = [];

		while ( $rows = $result->getNext() ) { // Objects (pages)
			$annotation = [];
			$dataSource = false;

			/**
			 * @var SMWResultArray $field
			 * @var SMWDataValue $dataValue
			 */
			foreach ( $rows as $field ) {

				// Use the subject marker to identify a possible data file
				$subject = $field->getResultSubject();
				if ( $this->params['datasource'] === 'file' && $subject->getTitle()->getNamespace(
					) === NS_FILE && !$dataSource ) {
					$aggregatedValues['subject'] = $this->makePageFromTitle( $subject->getTitle() )->getLongHTMLText(
						$this->getLinker( $field->getResultSubject() )
					);
					if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
						$aggregatedValues['url'] = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $subject->getTitle() )->getUrl();
					} else {
						// Before  MW 1.34
						$aggregatedValues['url'] = wfFindFile( $subject->getTitle() )->getUrl();
					}
					
					$dataSource = true;
				}

				// Proceed only where a label is known otherwise items are of no use
				// for being a potential object identifier
				if ( $field->getPrintRequest()->getLabel() !== '' ) {
					$propertyLabel = $field->getPrintRequest()->getLabel();
				} else {
					continue;
				}

				while ( ( $dataValue = $field->getNextDataValue() ) !== false ) { // Data values

					// Jump the column (indicated by continue) because we don't want the data source being part of the annotation array
					$dataItem = $dataValue->getDataItem();

					if ( $dataItem->getDIType() === SMWDataItem::TYPE_WIKIPAGE ) {
						$title = $dataItem->getTitle();
					}

					if ( $dataItem->getDIType() == SMWDataItem::TYPE_WIKIPAGE && $this->params['datasource'] === 'raw' && !$dataSource ) {
						// Support data source = raw which pulls the url from a wikipage in raw format
						$aggregatedValues['subject'] = $this->makePageFromTitle(
							$title
						)->getLongHTMLText( $this->getLinker( $field->getResultSubject() ) );
						$aggregatedValues['url'] = $title->getLocalURL( 'action=raw' );
						$dataSource = true;
						continue;
					} elseif ( $dataItem->getDIType() == SMWDataItem::TYPE_WIKIPAGE && $this->params['datasource'] === 'file' && $title->getNamespace() === NS_FILE && !$dataSource ) {
						// Support data source = file which pulls the url from a uploaded file
						$aggregatedValues['subject'] = $this->makePageFromTitle(
							$title
						)->getLongHTMLText( $this->getLinker( $field->getResultSubject() ) );
						$aggregatedValues['url'] = wfFindFile( $title )->getUrl();
						$dataSource = true;
						continue;
					} elseif ( $dataItem->getDIType() == SMWDataItem::TYPE_URI && $this->params['datasource'] === 'url' && !$dataSource ) {
						// Support data source = url, pointing to an url data source
						$aggregatedValues['link'] = $dataValue->getShortHTMLText( $this->getLinker( false ) );
						$aggregatedValues['url'] = $dataValue->getURL();
						$dataSource = true;
						continue;
					}

					// The annotation should adhere outlined conventions as the label identifies the array object key
					// series -> Required The name of the series to which the annotated point belongs
					// x -> Required The x value of the point
					// shortText -> Text that will appear as annotation flag
					// text -> A longer description of the annotation
					// @see  http://dygraphs.com/annotations.html
					if ( in_array( $propertyLabel, [ 'series', 'x', 'shortText', 'text' ] ) ) {
						if ( $dataItem->getDIType() == SMWDataItem::TYPE_NUMBER ) {
							// Set unit if available
							$dataValue->setOutputFormat( $this->params['unit'] );
							// Check if unit is available
							$annotation[$propertyLabel] = $dataValue->getUnit() !== '' ? $dataValue->getShortWikiText(
							) : $dataValue->getNumber();
						} else {
							$annotation[$propertyLabel] = $dataValue->getWikiValue();
						}
					}
				}
			}
			// Sum-up collected row items in a single array
			if ( $annotation !== [] ) {
				$aggregatedValues['annotation'][] = $annotation;
			}
		}
		return $aggregatedValues;
	}

	private function makePageFromTitle( \Title $title ) {
		$dataValue = new SMWWikiPageValue( '_wpg' );
		$dataItem = SMWDIWikiPage::newFromTitle( $title );
		$dataValue->setDataItem( $dataItem );
		return $dataValue;
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
		if ( $this->params['datasource'] === 'page' ) {
			foreach ( $data as $key => $values ) {
				$dataObject[] = [ 'label' => $key, 'data' => $values ];
			}
		} else {
			$dataObject['source'] = $data;
		}

		// Prepare transfer array
		$chartData = [
			'data' => $dataObject,
			'sask' => $options['sask'],
			'parameters' => [
				'width' => $this->params['width'],
				'height' => $this->params['height'],
				'xlabel' => $this->params['xlabel'],
				'ylabel' => $this->params['ylabel'],
				'charttitle' => $this->params['charttitle'],
				'charttext' => $this->params['charttext'],
				'infotext' => $this->params['infotext'],
				'datasource' => $this->params['datasource'],
				'rollerperiod' => $this->params['mavg'],
				'gridview' => $this->params['gridview'],
				'errorbar' => $this->params['errorbar'],
			]
		];

		// Array encoding and output
		$requireHeadItem = [ $chartID => FormatJson::encode( $chartData ) ];
		SMWOutputs::requireHeadItem( $chartID, SRFUtils::makeVariablesScript( $requireHeadItem ) );

		SMWOutputs::requireResource( 'ext.srf.dygraphs' );

		if ( $this->params['gridview'] === 'tabs' ) {
			SMWOutputs::requireResource( 'ext.srf.util.grid' );
		}

		// Chart/graph placeholder
		$chart = Html::rawElement(
			'div',
			[ 'id' => $chartID, 'class' => 'container', 'style' => "display:none;" ],
			null
		);

		// Processing/loading image
		$processing = SRFUtils::htmlProcessingElement( $this->isHTML );

		// Beautify class selector
		$class = $this->params['class'] ? ' ' . $this->params['class'] : ' dygraphs-common';

		// General output marker
		return Html::rawElement(
			'div',
			[ 'class' => 'srf-dygraphs' . $class ],
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

		$params['datasource'] = [
			'message' => 'srf-paramdesc-datasource',
			'default' => 'file',
			'values' => [ 'file', 'raw', 'url' ],
		];

		$params['errorbar'] = [
			'message' => 'srf-paramdesc-errorbar',
			'default' => '',
			'values' => [ 'fraction', 'sigma', 'range' ],
		];

		$params['min'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-minvalue',
			'default' => '',
		];

		$params['mavg'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-movingaverage',
			'default' => 14,
			'lowerbound' => 0,
		];

		$params['gridview'] = [
			'message' => 'srf-paramdesc-gridview',
			'default' => 'none',
			'values' => [ 'none', 'tabs' ],
		];

		$params['infotext'] = [
			'message' => 'srf-paramdesc-infotext',
			'default' => '',
		];

		$params['unit'] = [
			'message' => 'srf-paramdesc-unit',
			'default' => '',
		];

		$params['height'] = [
			'type' => 'integer',
			'message' => 'srf_paramdesc_chartheight',
			'default' => 400,
			'lowerbound' => 1,
		];

		$params['width'] = [
			'message' => 'srf_paramdesc_chartwidth',
			'default' => '100%',
		];

		$params['charttitle'] = [
			'message' => 'srf_paramdesc_charttitle',
			'default' => '',
		];

		$params['charttext'] = [
			'message' => 'srf-paramdesc-charttext',
			'default' => '',
		];

		$params['infotext'] = [
			'message' => 'srf-paramdesc-infotext',
			'default' => '',
		];

		$params['ylabel'] = [
			'message' => 'srf-paramdesc-yaxislabel',
			'default' => '',
		];

		$params['xlabel'] = [
			'message' => 'srf-paramdesc-xaxislabel',
			'default' => '',
		];

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		return $params;
	}
}
