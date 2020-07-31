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

function average_function(array $numbers)
{
	// average
	return array_sum($numbers) / count($numbers);
}

function variance_function(array $numbers)
{
	// average
	$average = average_function($numbers);
	// space
	$space = NULL;
	for($i = 0; $i < count($numbers); $i++)
	{
		$space += pow($numbers[$i], 2);
	}
	// result
	return ($space / count($numbers) - pow($average, 2));
}

function samplevariance_function(array $numbers)
{
	// average
	$average = average_function($numbers);
	// space
	$space = NULL;
	for($i = 0; $i < count($numbers); $i++)
	{
		$space += pow(($numbers[$i] - $average), 2);
	}
	// result
	return ($space / (count($numbers) - 1));
}

function standarddeviation_function(array $numbers)
{
	// average
	$average = average_function($numbers);
	// space
	$space = NULL;
	for($i = 0; $i < count($numbers); $i++)
	{
		$space += pow(($numbers[$i] - $average), 2);
	}
	// result
	return sqrt($space / (count($numbers) - 1));
}

function samplestandarddeviation_function(array $numbers)
{
	// average
	$average = average_function($numbers);
	// space
	$space = NULL;
	for($i = 0; $i < count($numbers); $i++)
	{
		$space += pow($numbers[$i], 2);
	}
	// result
	return sqrt($space / count($numbers) - pow($average, 2));
}

function range_function(array $numbers)
{
	return (max($numbers) - min($numbers));
}

function quartillower_inc_function(array $numbers)
{
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
}

function quartilupper_inc_function(array $numbers)
{
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
}

function quartillower_exc_function(array $numbers)
{
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
}

function quartilupper_exc_function(array $numbers)
{
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
}

function interquartilerange_inc_function(array $numbers)
{
	return quartilupper_inc_function($numbers) - quartillower_inc_function($numbers);
}

function interquartilerange_exc_function(array $numbers)
{
	return quartilupper_exc_function($numbers) - quartillower_exc_function($numbers);
}

function mode_function(array $numbers)
{
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
}

function interquartilemean_function(array $numbers)
{
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
}

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
				return variance_function($numbers);
				break;
			case 'samplevariance':
				return samplevariance_function($numbers);
				break;
			case 'samplestandarddeviation':
				return samplestandarddeviation_function($numbers);
				break;
			case 'standarddeviation':
				return standarddeviation_function($numbers);
				break;
			case 'range':
				return range_function($numbers);
				break;
			case 'quartillower':
				return quartillower_inc_function($numbers);
				break;
			case 'quartilupper';
				return quartilupper_inc_function($numbers);
				break;
			case 'quartillower.exc';
				return quartillower_exc_function($numbers);
				break;
			case 'quartilupper.exc';
				return quartilupper_exc_function($numbers);
				break;
			case 'interquartilerange':
				return interquartilerange_inc_function($numbers);
				break;
			case 'interquartilerange.exc';
				return interquartilerange_exc_function($numbers);
				break;
			case 'mode';
				return mode_function($numbers);
				break;
			case 'interquartilemean';
				return interquartilemean_function($numbers);
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
