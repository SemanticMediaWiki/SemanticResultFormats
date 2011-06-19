<?php

/**
 * Various mathematical functions - sum, product, average, min and max.
 *
 * @file
 * @ingroup SemanticResultFormats
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
		return wfMsg( 'srf_printername_' . $this->mFormat );
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getResult()
	 */
	public function getResult( SMWQueryResult $results, array $params, $outputmode ) {
		$this->readParameters( $params, $outputmode );
		global $wgLang;
		return $wgLang->formatNum( $this->getResultText( $results, SMW_OUTPUT_HTML ) );
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getResultText()
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		$numbers = $this->getNumbers( $res );
		
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
		}
	}
	
	/**
	 * Gets a list of all numbers.
	 * 
	 * @since 1.6
	 * 
	 * @param SMWQueryResult $res
	 * 
	 * @return array
	 */
	protected function getNumbers( SMWQueryResult $res ) {
		$numbers = array();

		while ( $row = $res->getNext() ) {
			foreach( $row as /* SMWResultArray */ $resultArray ) {
				while ( ( $dataValue = efSRFGetNextDV( $resultArray ) ) !== false ) {
					$numbers = array_merge( $numbers, $this->getNumbersForDataValue( $dataValue ) );
				}				
			}
		}

		return $numbers;
	}
	
	/**
	 * Gets a list of all numbers contained in a datavalue.
	 * 
	 * @since 1.6
	 * 
	 * @param SMWDataValue $dataValue
	 * 
	 * @return array
	 */
	protected function getNumbersForDataValue( SMWDataValue $dataValue ) {
		$numbers = array();
		
		if ( $dataValue instanceof SMWNumberValue ) {
			// getDataItem was introduced in SMW 1.6, getValueKey was deprecated in the same version.
			if ( method_exists( $dataValue, 'getDataItem' ) ) {
				$numbers[] = $dataValue->getDataItem()->getNumber();
			} else {
				$numbers[] = $dataValue->getValueKey();
			}
		// Support for SMWNAryValue, which was removed in SMW 1.6.
		} elseif ( $dataValue instanceof SMWNAryValue ) {
			foreach ( $dataValue->getDVs() as $inner_value ) {
				$numbers = array_merge( $numbers, $this->getNumbersForDataValue( $inner_value ) );
			}
		}
		
		return $numbers;
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getParameters()
	 */
	public function getParameters() {
		return array(
			array( 'name' => 'limit', 'type' => 'int', 'description' => wfMsg( 'srf_paramdesc_limit' ) ),
		);
	}

}
