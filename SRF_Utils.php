<?php

/**
 * Static utility class.
 * 
 * @since 1.7
 * 
 * @file SRF_Utils.php
 * @ingroup SemanticResultFormats
 * 
 * @licence GNU GPL v3
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class SRFUtils {
	
	public static function getDistributionResults( SMWQueryResult $result, $outputmode, $linker ) {
		$values = array();
		
		while ( /* array of SMWResultArray */ $row = $result->getNext() ) { // Objects (pages)
			for ( $i = 0, $n = count( $row ); $i < $n; $i++ ) { // SMWResultArray for a sinlge property 
				while ( ( /* SMWDataValue */ $dataValue = $row[$i]->getNextDataValue() ) !== false ) { // Data values
					
					// Get the HTML for the tag content. Pages are linked, other stuff is just plaintext.
					if ( $dataValue->getTypeID() == '_wpg' ) {
						$value = $dataValue->getTitle()->getText();
					}
					else {
						$value = $dataValue->getShortText( $outputmode, $linker );
					}

					if ( !array_key_exists( $value, $values ) ) {
						$values[$value] = 0;
					}
					
					$values[$value]++;
				}
			}
		}
		
		return $values;
	}
	
}
