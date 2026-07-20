<?php

declare( strict_types=1 );

namespace SRF\Math;

use SMW\DataValueFactory;
use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\ResultPrinter;
use SMWDataItem;

/**
 * Result printer for the math result formats. The statistical functions
 * themselves live in MathFormats.
 *
 * @license GPL-3.0-or-later
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Yaron Koren
 * @author Nathan Yergler
 * @author Florian Breitenlacher
 */

class Math extends ResultPrinter {

	/**
	 * (non-PHPdoc)
	 * @see ResultPrinter::getName()
	 */
	public function getName() {
		// Give grep a chance to find the usages:
		// srf_printername_max, srf_printername_min, srf_printername_sum,
		// srf_printername_product, srf_printername_average, srf_printername_median
		return wfMessage( 'srf_printername_' . $this->mFormat )->text();
	}

	/**
	 * @see ResultPrinter::buildResult
	 *
	 * @since 1.8
	 *
	 * @param QueryResult $results
	 *
	 * @return string
	 */
	protected function buildResult( QueryResult $results ) {
		$number = $this->getResultText( $results, SMW_OUTPUT_HTML );

		if ( count( $results->getPrintRequests() ) > 1 ) {
			$outputformat = $results->getPrintRequests()[1]->getOutputFormat();
		} else {
			// no mainlabel
			$outputformat = $results->getPrintRequests()[0]->getOutputFormat();
		}

		// if raw-format ("-") than skip formatNum()
		if ( $outputformat != "-" ) {
			$dataValue = DataValueFactory::getInstance()->newDataValueByType( '_num' );
			$number = $dataValue->getLocalizedFormattedNumber( $number );
		}

		return (string)$number;
	}

	/**
	 * @see ResultPrinter::getResultText()
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {
		$numbers = $this->getNumbers( $res );

		if ( count( $numbers ) == 0 ) {
			return $this->params['default'];
		}

		switch ( $this->mFormat ) {
			case 'max':
				return MathFormats::maxFunction( $numbers );
			case 'min':
				return MathFormats::minFunction( $numbers );
			case 'sum':
				return MathFormats::sumFunction( $numbers );
			case 'product':
				return MathFormats::productFunction( $numbers );
			case 'average':
				return MathFormats::averageFunction( $numbers );
			case 'median':
				return MathFormats::medianFunction( $numbers );
			case 'variance':
				return MathFormats::varianceFunction( $numbers );
			case 'samplevariance':
				return MathFormats::samplevarianceFunction( $numbers );
			case 'samplestandarddeviation':
				return MathFormats::samplestandarddeviationFunction( $numbers );
			case 'standarddeviation':
				return MathFormats::standarddeviationFunction( $numbers );
			case 'range':
				return MathFormats::rangeFunction( $numbers );
			case 'quartillower':
				return MathFormats::quartillowerIncFunction( $numbers );
			case 'quartilupper':
				return MathFormats::quartilupperIncFunction( $numbers );
			case 'quartillower.exc':
				return MathFormats::quartillowerExcFunction( $numbers );
			case 'quartilupper.exc':
				return MathFormats::quartilupperExcFunction( $numbers );
			case 'interquartilerange':
				return MathFormats::interquartilerangeIncFunction( $numbers );
			case 'interquartilerange.exc':
				return MathFormats::interquartilerangeExcFunction( $numbers );
			case 'mode':
				return MathFormats::modeFunction( $numbers );
			case 'interquartilemean':
				return MathFormats::interquartilemeanFunction( $numbers );
		}
	}

	/**
	 * @param QueryResult $res
	 *
	 * @return float[]
	 */
	private function getNumbers( QueryResult $res ) {
		$numbers = [];

		while ( $row = $res->getNext() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			foreach ( $row as $resultArray ) {
				foreach ( $resultArray->getContent() as $dataItem ) {
					self::addNumbersForDataItem( $dataItem, $numbers );
				}
			}
		}

		return $numbers;
	}

	/**
	 * @param SMWDataItem $dataItem
	 * @param float[] &$numbers
	 */
	private function addNumbersForDataItem( SMWDataItem $dataItem, array &$numbers ) {
		switch ( $dataItem->getDIType() ) {
			case SMWDataItem::TYPE_NUMBER:
				$numbers[] = $dataItem->getNumber();
				break;
			case SMWDataItem::TYPE_CONTAINER:
				foreach ( $dataItem->getDataItems() as $di ) {
					self::addNumbersForDataItem( $di, $numbers );
				}
				break;
			default:
		}
	}

}
