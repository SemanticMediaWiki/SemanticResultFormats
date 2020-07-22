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
			case 'quartillower.inkl':
				sort($numbers, SORT_NUMERIC);
				$Q1_position = ((sizeof($numbers) - 1) * 0.25);
				if(is_float($Q1_position) == TRUE)
				{
					$Q1_position_y = floor($Q1_position);
					$Q1_position_x = ceil($Q1_position);
					return ($numbers[$Q1_position_y] + ($numbers[$Q1_position_x] - $numbers[$Q1_position_y]) * 0.25);
				}
				else
				{
					return $numbers[$Q1_position];
				}
				break;
			case 'median.inkl';
				sort($numbers, SORT_NUMERIC);
				$Q2_position = ((sizeof($numbers) - 1) * 0.5);
				if(is_float($Q2_position) == TRUE)
				{
					$Q2_position_y = floor($Q2_position);
					$Q2_position_x = ceil($Q2_position);
					return (($numbers[$Q2_position_x] + $numbers[$Q2_position_y]) / 2);
				}
				else
				{
					return $numbers[$Q2_position];
				}
				break;
			case 'quartilupper.inkl';
				sort($numbers, SORT_NUMERIC);
				$Q3_position = ((sizeof($numbers) - 1) * 0.75);
				if(is_float($Q3_position) == TRUE)
				{
					$Q3_position_y = floor($Q3_position);
					$Q3_position_x = ceil($Q3_position);
					return ($numbers[$Q3_position_y] + ($numbers[$Q3_position_x] - $numbers[$Q3_position_y]) * 0.75);
				}
				else
				{
					return $numbers[$Q3_position];
				}
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
