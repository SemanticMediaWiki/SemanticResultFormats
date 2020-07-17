<?php

/**
 * Various mathematical functions - sum, product, average, min and max.
 *
 * @licence GNU GPL v3+
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Yaron Koren
 * @author Nathan Yergler
 */
class SRFMath extends SMWResultPrinter {

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getName()
	 */
	public function getName() {
		// Give grep a chance to find the usages:
		// srf_printername_max, srf_printername_min, srf_printername_sum,
		// srf_printername_product, srf_printername_average, srf_printername_median
		return wfMessage( 'srf_printername_' . $this->mFormat )->text();
	}

	/**
	 * @see SMWResultPrinter::buildResult
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $results
	 *
	 * @return string
	 */
	protected function buildResult( SMWQueryResult $results ) {

		$number = $this->getResultText( $results, SMW_OUTPUT_HTML );

		if ( count( $results->getPrintRequests() ) > 1 ) {
			$outputformat = $results->getPrintRequests()[1]->getOutputFormat();
		} else {
			// no mainlabel
			$outputformat = $results->getPrintRequests()[0]->getOutputFormat();
		}

		// if raw-format ("-") than skip formatNum()
		if ( $outputformat != "-" ) {
			$dataValue = \SMW\DataValueFactory::getInstance()->newDataValueByType( '_num' );
			$number = $dataValue->getLocalizedFormattedNumber( $number );
		}

		return (string)$number;
	}

	/**
	 * @see SMWResultPrinter::getResultText()
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		$numbers = $this->getNumbers( $res );

		if ( count( $numbers ) == 0 ) {
			return $this->params['default'];
		}

		switch ( $this->mFormat ) {
			case 'max':
				return max( $numbers );
				break;
			case 'min':
				return min( $numbers );
				break;
			case 'sum':
				return array_sum( $numbers );
				break;
			case 'product':
				return array_product( $numbers );
				break;
			case 'average':
				return array_sum( $numbers ) / count( $numbers );
				break;
			case 'median':
				sort( $numbers, SORT_NUMERIC );
				$position = ( count( $numbers ) + 1 ) / 2 - 1;
				return ( $numbers[ceil( $position )] + $numbers[floor( $position )] ) / 2;
				break;
			case 'variance':
				$average = array_sum($numbers) / count($numbers);
				$space = NULL;
				for($i = 0; $i < count($numbers); $i++)
				{
					$space += pow($numbers[$i], 2);
				}
				$result = ($space / count($numbers) - pow($average, 2));
				return $result;
				break;
			case 'samplevariance':
				$average = array_sum($numbers) / count($numbers);
				$space = NULL;
				for($i = 0; $i < count($numbers); $i++)
				{
					$space += pow(($numbers[$i] - $average), 2);
				}
				$result = ($space / (count($numbers) - 1));
				return $result;
				break;
			case 'samplestandarddeviation':
				$average = array_sum($numbers) / count($numbers);
				$space = NULL;
				for($i = 0; $i < count($numbers); $i++)
				{
					$space += pow(($numbers[$i] - $average), 2);
				}
				$result = sqrt($space / (count($numbers) - 1));
				return $result;
				break;
			case 'standarddeviation':
				$average = array_sum($numbers) / count($numbers);
				$space = NULL;
				for($i = 0; $i < count($numbers); $i++)
				{
					$space += pow($numbers[$i], 2);
				}
				$result = sqrt($space / count($numbers) - pow($average, 2));
				return $result;
				break;
			case 'range':
				return (max($numbers) - min($numbers));
				break;
		}
	}

	/**
	 * @param SMWQueryResult $res
	 *
	 * @return float[]
	 */
	private function getNumbers( SMWQueryResult $res ) {
		$numbers = [];

		while ( $row = $res->getNext() ) {
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
	 * @param float[] $numbers
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
