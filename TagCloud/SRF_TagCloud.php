<?php
/**
 * Print query results as a tag cloud.
 * 
 * @author Jeroen De Dauw
 * 
 * @file SRF_TagCloud.php
 * @ingroup SemanticResultFormats
 */

if ( !defined( 'MEDIAWIKI' ) ) die();

/**
 * Result printer that prints query results as a gallery.
 */
class SRFTagCloud extends SMWResultPrinter {

	public function getName() {
		return wfMsg( 'srf_printername_tagcloud' );
	}

	public function getResult( $results, $params, $outputmode ) {
		// skip checks, results with 0 entries are normal
		$this->readParameters( $params, $outputmode );
		return $this->getResultText( $results, SMW_OUTPUT_HTML );
	}

	public function getResultText( $results, $outputmode ) {
		
	}
	
}