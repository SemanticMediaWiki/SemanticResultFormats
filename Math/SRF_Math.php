<?php

/**
 * Various mathematical functions - sum, average, min and max.
 *
 * @file
 * @ingroup SemanticResultFormats
 * @author Yaron Koren
 * @author Nathan Yergler
 */

class SRFMath extends SMWResultPrinter {

	public function getName() {
		return wfMsg( 'srf_printername_' . $this->mFormat );
	}

	public function getResult( SMWQueryResult $results, array $params, $outputmode ) {
		$this->readParameters( $params, $outputmode );
		global $wgLang;
		return $wgLang->formatNum( $this->getResultText( $results, SMW_OUTPUT_HTML ) );
	}

	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		// initialize all necessary variables
		$sum = 0;
		$count = 0;
		$min = '';
		$max = '';

		while ( $row = $res->getNext() ) {
			/* SMWResultArray */ $last_col = array_pop( $row );
			
			while ( ( $value = efSRFGetNextDV( $last_col ) ) !== false ) {
				// handle each value only if it's of type Number or NAry
				if ( $value instanceof SMWNumberValue ) {
					// getDataItem was introduced in SMW 1.6, getValueKey was deprecated in the same version.
					if ( method_exists( $value, 'getDataItem' ) ) {
						$num = $value->getDataItem()->getNumber();
					} else {
						$num = $value->getValueKey();
					}
				} elseif ( $value instanceof SMWNAryValue ) {
					$inner_values = $value->getDVs();
					// find the first inner value that's of
					// type Number, and use that; if none
					// are found, ignore this row
					$num = null;
					
					foreach ( $inner_values as $inner_value ) {
						if ( $inner_value instanceof SMWNumberValue ) {
							// getDataItem was introduced in SMW 1.6, getValueKey was deprecated in the same version.
							if ( method_exists( $inner_value, 'getDataItem' ) ) {
								$num = $inner_value->getDataItem()->getNumber();
							} else {
								$num = $inner_value->getValueKey();
							}
							break;
						}
					}
					
					if ( is_null( $num ) ) {
						continue;
					}
						
				} else {
					continue;
				}
				
				$count++;
				
				if ( $this->mFormat == 'sum' || $this->mFormat == 'average' ) {
					$sum += $num;
				} elseif ( $this->mFormat == 'min' ) {
					if ( $min === '' || $num < $min ) {
						$min = $num;
					}
				} elseif ( $this->mFormat == 'max' ) {
					if ( $max === '' || $num > $max ) {
						$max = $num;
					}
				}
			}
		}
		// if there were no results, display a blank
		if ( $count == 0 ) {
			$result = '';
		} elseif ( $this->mFormat == 'sum' ) {
			$result = $sum;
		} elseif ( $this->mFormat == 'average' ) {
			$result = $sum / $count;
		} elseif ( $this->mFormat == 'min' ) {
			$result = $min;
		} elseif ( $this->mFormat == 'max' ) {
			$result = $max;
		} else {
			$result = '';
		}

		return $result;
	}

	public function getParameters() {
		return array(
			array( 'name' => 'limit', 'type' => 'int', 'description' => wfMsg( 'srf_paramdesc_limit' ) ),
		);
	}

}
