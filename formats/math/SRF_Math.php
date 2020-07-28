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
				// create space to store values
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
				// create space to store values
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
				// create space to store values
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
				// create space to store values
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
			case 'quartillower':
				sort($numbers, SORT_NUMERIC);
				// get position
				$Q1_position = ((sizeof($numbers) - 1) * 0.25);
				// check if position is between two numbers
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
			case 'quartilupper';
				sort($numbers, SORT_NUMERIC);
				// get position
				$Q3_position = ((sizeof($numbers) - 1) * 0.75);
				// check if position is between two numbers
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
			case 'quartillower.exc';
				sort($numbers, SORT_NUMERIC);
				// get position
				$Q1_position = ((sizeof($numbers) + 1) * 0.25);
				// check if position is between two numbers
				if(is_float($Q1_position) == TRUE)
				{
					$Q1_position_y = floor($Q1_position)-1;
					$Q1_position_x = ceil($Q1_position)-1;
					return ($numbers[$Q1_position_y] + ($numbers[$Q1_position_x] - $numbers[$Q1_position_y]) * 0.75);
				}
				else
				{
					return $numbers[$Q1_position];
				}
				break;
			case 'quartilupper.exc';
				sort($numbers, SORT_NUMERIC);
				// get position
				$Q3_position = ((sizeof($numbers) + 1) * 0.75);
				// check if position is between two numbers
				if(is_float($Q3_position) == TRUE)
				{
					$Q3_position_y = floor($Q3_position)-1;
					$Q3_position_x = ceil($Q3_position)-1;
					return ($numbers[$Q3_position_y] + ($numbers[$Q3_position_x] - $numbers[$Q3_position_y]) * 0.25);
				}
				else
				{
					return $numbers[$Q3_position];
				}
				break;
			case 'interquartilerange':
				sort($numbers, SORT_NUMERIC);
				// get position of Q1
				$Q1_position = ((sizeof($numbers) - 1) * 0.25);
				// check if position is between two numbers
				if(is_float($Q1_position) == TRUE)
				{
					$Q1_position_y = floor($Q1_position);
					$Q1_position_x = ceil($Q1_position);
					$Q1 = ($numbers[$Q1_position_y] + ($numbers[$Q1_position_x] - $numbers[$Q1_position_y]) * 0.25);
				}
				else
				{
					$Q1 = $numbers[$Q1_position];
				}
				// get position of Q3
				$Q3_position = ((sizeof($numbers) - 1) * 0.75);
				// check if position is between two numbers
				if(is_float($Q3_position) == TRUE)
				{
					$Q3_position_y = floor($Q3_position);
					$Q3_position_x = ceil($Q3_position);
					$Q3 = ($numbers[$Q3_position_y] + ($numbers[$Q3_position_x] - $numbers[$Q3_position_y]) * 0.75);
				}
				else
				{
					$Q3 = $numbers[$Q3_position];
				}
				return ($Q3 - $Q1);
				break;
			case 'interquartilerange.exc';
				sort($numbers, SORT_NUMERIC);
				// get position of Q1
				$Q1_position = ((sizeof($numbers) + 1) * 0.25);
				// check if position is between two numbers
				if(is_float($Q1_position) == TRUE)
				{
					$Q1_position_y = floor($Q1_position)-1;
					$Q1_position_x = ceil($Q1_position)-1;
					$Q1 = ($numbers[$Q1_position_y] + ($numbers[$Q1_position_x] - $numbers[$Q1_position_y]) * 0.75);
				}
				else
				{
					$Q1 = $numbers[$Q1_position];
				}
				// get position of Q3
				$Q3_position = ((sizeof($numbers) + 1) * 0.75);
				// check if position is between two numbers
				if(is_float($Q3_position) == TRUE)
				{
					$Q3_position_y = floor($Q3_position)-1;
					$Q3_position_x = ceil($Q3_position)-1;
					$Q3 = ($numbers[$Q3_position_y] + ($numbers[$Q3_position_x] - $numbers[$Q3_position_y]) * 0.25);
				}
				else
				{
					$Q3 = $numbers[$Q3_position];
				}
				return ($Q3 - $Q1);
				break;
			case 'mode';
				// array temp
				$array_temp = array();
				// convert array
				for($i = 0; $i < sizeof($numbers); $i++)
				{
					$converted_value = strval($numbers[$i]);
					$array_temp += [$i => $converted_value];
				}
				$array_counted_values = array_count_values($array_temp);
				// max
				$max = max($array_counted_values);
				// count
				$count = NULL;
				// filter
				for($i = 0; $i < sizeof($array_counted_values); $i++)
				{
					if ($array_counted_values[array_keys($array_counted_values)[$i]] == $max)
					{
						$count += 1;
					}
				}
				// check if there are more than one max
				if($count == 1)
				{
					return $max;
				}
				break;
			case 'interquartilemean';
				// sort numbers
				sort($numbers,SORT_NUMERIC);
					// check if size of numbers is divisble by 4
					if(sizeof($numbers)%4 == 0)
					{
						// split array into 4 groups (2D array)
						$array_split = (array_chunk($numbers, sizeof($numbers)/4));
						// creating store_string
						$store_string = NULL;
						for($i = 0; $i < sizeof($array_split[1]); $i++)
						{
							$store_string += $array_split[1][$i];
						}
						for($i = 0; $i < sizeof($array_split[2]); $i++)
						{
							$store_string += $array_split[2][$i];
						}
						return $store_string/(sizeof($array_split[1])+sizeof($array_split[2]));
					}
					else
					{
						// get positon of split
						$position = sizeof($numbers)/4;
						// remove values out of split
						for($i = 0; $i < floor($position); $i++)
						{
							unset($numbers[$i]);
							array_pop($numbers);
						}
						// reset array keys
						$store_array = array_merge($numbers);
						// add values
						$store_values = NULL;
						for($i = 1; $i < sizeof($store_array)-1; $i++)
						{
							$store_values += $store_array[$i];
						}
						return ($store_values + ((ceil($position) - $position) * ($store_array[0] + $store_array[sizeof($store_array)-1]))) / ($position*2);
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
