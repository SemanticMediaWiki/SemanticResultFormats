<?php

declare( strict_types=1 );

namespace SRF\Math;

use SMW\DataValueFactory;
use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\ResultPrinter;
use SMWDataItem;

/**
 * Various mathematical functions - sum, product, average, min, max, median, variance, samplevariance, samplestandarddeviation, standarddeviation, range, quartillower, quartilupper, quartillower.exc, quartilupper.exc, interquartilerange, interquartilerange.exc, mode and interquartilemean
 *
 * @license GPL-3.0-or-later
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Yaron Koren
 * @author Nathan Yergler
 * @author Florian Breitenlacher
 */

class MathFormats {
	public static function maxFunction( array $numbers ) {
		// result
		return max( $numbers );
	}

	public static function minFunction( array $numbers ) {
		// result
		return min( $numbers );
	}

	public static function sumFunction( array $numbers ) {
		// result
		return array_sum( $numbers );
	}

	public static function productFunction( array $numbers ) {
		// result
		return array_product( $numbers );
	}

	public static function averageFunction( array $numbers ) {
		// result
		return array_sum( $numbers ) / count( $numbers );
	}

	public static function medianFunction( array $numbers ) {
		sort( $numbers, SORT_NUMERIC );
		// get position
		$position = ( count( $numbers ) + 1 ) / 2 - 1;
		// result
		return ( $numbers[ceil( $position )] + $numbers[floor( $position )] ) / 2;
	}

	public static function varianceFunction( array $numbers ) {
		// average
		$average = self::averageFunction( $numbers );
		// space
		$space = null;
		for ( $i = 0; $i < count( $numbers ); $i++ ) {
			$space += pow( $numbers[$i], 2 );
		}
		// result
		return ( $space / count( $numbers ) - pow( $average, 2 ) );
	}

	public static function samplevarianceFunction( array $numbers ) {
		// average
		$average = self::averageFunction( $numbers );
		// space
		$space = null;
		for ( $i = 0; $i < count( $numbers ); $i++ ) {
			$space += pow( ( $numbers[$i] - $average ), 2 );
		}
		// result
		return ( $space / ( count( $numbers ) - 1 ) );
	}

	public static function standarddeviationFunction( array $numbers ) {
		// result: square root of the population variance
		return sqrt( self::varianceFunction( $numbers ) );
	}

	public static function samplestandarddeviationFunction( array $numbers ) {
		// result: square root of the sample variance
		return sqrt( self::samplevarianceFunction( $numbers ) );
	}

	public static function rangeFunction( array $numbers ) {
		// result
		return ( max( $numbers ) - min( $numbers ) );
	}

	public static function quartillowerIncFunction( array $numbers ) {
		sort( $numbers, SORT_NUMERIC );
		// get position (inclusive method: 0-based position (n - 1) * 0.25)
		$position = ( count( $numbers ) - 1 ) * 0.25;
		$lower = (int)floor( $position );
		$upper = (int)ceil( $position );
		// result: interpolate between the neighbouring values by the
		// fractional part of the position
		return $numbers[$lower] + ( $numbers[$upper] - $numbers[$lower] ) * ( $position - $lower );
	}

	public static function quartilupperIncFunction( array $numbers ) {
		sort( $numbers, SORT_NUMERIC );
		// get position (inclusive method: 0-based position (n - 1) * 0.75)
		$position = ( count( $numbers ) - 1 ) * 0.75;
		$lower = (int)floor( $position );
		$upper = (int)ceil( $position );
		// result: interpolate between the neighbouring values by the
		// fractional part of the position
		return $numbers[$lower] + ( $numbers[$upper] - $numbers[$lower] ) * ( $position - $lower );
	}

	public static function quartillowerExcFunction( array $numbers ) {
		sort( $numbers, SORT_NUMERIC );
		// get position (exclusive method: 1-based rank (n + 1) * 0.25)
		$position = ( count( $numbers ) + 1 ) * 0.25 - 1;
		$lower = (int)floor( $position );
		$upper = (int)ceil( $position );
		// result: interpolate between the neighbouring values by the
		// fractional part of the position
		return $numbers[$lower] + ( $numbers[$upper] - $numbers[$lower] ) * ( $position - $lower );
	}

	public static function quartilupperExcFunction( array $numbers ) {
		sort( $numbers, SORT_NUMERIC );
		// get position (exclusive method: 1-based rank (n + 1) * 0.75)
		$position = ( count( $numbers ) + 1 ) * 0.75 - 1;
		$lower = (int)floor( $position );
		$upper = (int)ceil( $position );
		// result: interpolate between the neighbouring values by the
		// fractional part of the position
		return $numbers[$lower] + ( $numbers[$upper] - $numbers[$lower] ) * ( $position - $lower );
	}

	public static function interquartilerangeIncFunction( array $numbers ) {
		// result
		return self::quartilupperIncFunction( $numbers ) - self::quartillowerIncFunction( $numbers );
	}

	public static function interquartilerangeExcFunction( array $numbers ) {
		// result
		return self::quartilupperExcFunction( $numbers ) - self::quartillowerExcFunction( $numbers );
	}

	public static function modeFunction( array $numbers ) {
		// count occurrences per value (string keys, so decimal values work)
		$counts = [];
		foreach ( $numbers as $number ) {
			$key = strval( $number );
			$counts[$key] = ( $counts[$key] ?? 0 ) + 1;
		}
		// values sharing the highest occurrence count
		$modes = array_keys( $counts, max( $counts ), true );
		// no result unless exactly one value is the most frequent
		if ( count( $modes ) !== 1 ) {
			return null;
		}
		// result: the most frequent value (array keys are int or string,
		// decimal values arrive as numeric strings)
		$mode = $modes[0];
		return is_int( $mode ) ? $mode : (float)$mode;
	}

	public static function interquartilemeanFunction( array $numbers ) {
		// sort numbers
		sort( $numbers, SORT_NUMERIC );
		// check if size of numbers is divisible by 4
		if ( count( $numbers ) % 4 == 0 ) {
			// split array into 4 groups (2D array)
			$array_split = ( array_chunk( $numbers, count( $numbers ) / 4 ) );
			// creating store_string
			$store_string = null;
			for ( $i = 0; $i < count( $array_split[1] ); $i++ ) {
				$store_string += $array_split[1][$i];
			}
			for ( $i = 0; $i < count( $array_split[2] ); $i++ ) {
				$store_string += $array_split[2][$i];
			}
			// result
			return $store_string / ( count( $array_split[1] ) + count( $array_split[2] ) );
		} else {
			// get position of split
			$position = count( $numbers ) / 4;
			// remove values out of split
			for ( $i = 0; $i < floor( $position ); $i++ ) {
				unset( $numbers[$i] );
				array_pop( $numbers );
			}
			// reset array keys
			$store_array = array_merge( $numbers );
			// add values
			$store_values = null;
			for ( $i = 1; $i < count( $store_array ) - 1; $i++ ) {
				$store_values += $store_array[$i];
			}
			// result
			return ( $store_values + ( ( ceil( $position ) - $position ) * ( $store_array[0] + $store_array[count( $store_array ) - 1] ) ) ) / ( $position * 2 );
		}
	}
}

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
