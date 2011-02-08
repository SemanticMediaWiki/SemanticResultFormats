<?php

/**
 * Result printer that prints query results as a tag cloud.
 * 
 * @since 1.5.3
 * 
 * @file SRF_TagCloud.php
 * @ingroup SemanticResultFormats
 * 
 * @licence GNU GPL v3
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SRFTagCloud extends SMWResultPrinter {

	public function getName() {
		return wfMsg( 'srf_printername_tagcloud' );
	}

	public function getResult( /* SMWQueryResult */ $results, /* array */ $params, $outputmode ) {
		// skip checks, results with 0 entries are normal
		$this->readParameters( $params, $outputmode );
		return $this->getResultText( $results, SMW_OUTPUT_HTML );
	}

	public function getResultText( /* SMWQueryResult */ $results, $outputmode ) {
		return $this->getTagCloud( $this->getTags( $results, $outputmode ) );
	}
	
	/**
	 * Returns an array with the tags (keys) and the number of times they occur (values).
	 * 
	 * @since 1.5.3
	 * 
	 * @param SMWQueryResult $results
	 * @param $outputmode
	 * 
	 * @return array
	 */
	protected function getTags( SMWQueryResult $results, $outputmode ) {
		$tags = array();
		
		while ( /* array of SMWResultArray */ $row = $results->getNext() ) { // Objects (pages)
			for ( $i = 0, $n = count( $row ); $i < $n; $i++ ) { // Properties
				while ( ( $obj = $row[$i]->getNextObject() ) !== false ) { // Property values
					$value = $obj->getTypeID() == '_wpg' ? $obj->getTitle()->getText() : $obj->getShortText( $outputmode );

					if ( !array_key_exists( $value, $tags ) ) {
						$tags[$value] = 0;
					}
					
					$tags[$value]++;
				}					
			}
		}
		
		return $tags;
	}
	
	/**
	 * Returns the HTML for the tag cloud.
	 * 
	 * @since 1.5.3
	 * 
	 * @param array $tags
	 * 
	 * @return string
	 */
	protected function getTagCloud( array $tags ) {
		return '';
	}
	
}
