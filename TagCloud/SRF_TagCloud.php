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

	public function getResult( $results, $params, $outputmode ) {
		// skip checks, results with 0 entries are normal
		$this->readParameters( $params, $outputmode );
		return $this->getResultText( $results, SMW_OUTPUT_HTML );
	}

	public function getResultText( $results, $outputmode ) {
		
	}
	
}