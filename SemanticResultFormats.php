<?php
/**
 * Initializing file for the diverse query result formats supported in this
 * extension. In order to use them, add the following lines to your
 * LocalSettings.php:
 *
 *  ....
 *
 */
if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

$smwgResultFormats['graph'] = 'SMWGraphResultPrinter';
$smwgResultFormats['googlebar'] = 'SMWGoogleBarResultPrinter';
$smwgResultFormats['googlepie'] = 'SMWGooglePieResultPrinter';

include_once( 'GoogleCharts/GoogleCharts.php' );
include_once( 'GraphViz/Graph.php' );
