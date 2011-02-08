<?php
/**
 * Print query results as a tag cloud.
 * 
 * @since 1.5.3
 * 
 * @file SRF_TagCloud.php
 * @ingroup SemanticResultFormats
 * 
 * @licence GNU GPL v3
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( !defined( 'MEDIAWIKI' ) ) die();

/**
 * Result printer that prints query results as a tag cloud.
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
		return $this->getTagCloud( $this->getTags( $results ) );
	}
	
	protected function getTags( SMWQueryResult $results ) {
		$tags = array();
		
		while ( /* array of SMWResultArray */ $row = $results->getNext() ) { // Objects (pages)
			for ( $i = 0, $n = count( $row ); $i < $n; $i++ ) { // Properties
				while ( ( $obj = $row[$i]->getNextObject() ) !== false ) { // Property values
					if ( $obj->getTypeID() == '_wpg' ) {
						$images[] = $obj->getTitle(); 
					}					
				}					
			}
		}
	}
	
	protected function getTagCloud( array $tags ) {
		
	}
	
}
