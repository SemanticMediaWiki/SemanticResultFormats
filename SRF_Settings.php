<?php
/**
 * Initializing file for the diverse query result formats supported in this
 * extension. In order to use them, add the following lines to your
 * LocalSettings.php:
 *
 * include_once('path/to/here/SemanticResultFormats.php');
 *
 * If nothing else is added, all the formats will be included. If you want to
 * include only certain formats, you first need to set up an array with all the
 * formats that should be included, e.g. like this:
 * 
 * global $srfgFormats;
 * $srfgFormats = array('graph', 'googlebar');
 * 
 * A list of all available formats can be found at the end of this file.
 */
if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

function srfFormat($formatName, $formatClassName, $formatFileName) {
	// if the array $srfgFormats does not exist, then include every format
	// (as by default). If the array exists, check if the current format shall
	// be included or not.
	global $srfgFormats;
	$include = !isset($srfgFormats);
	if (!$include) $include = in_array($formatName, $srfgFormats);
	if (!$include) return; 
	global $smwgResultFormats, $wgAutoloadClasses;
	$smwgResultFormats[$formatName] = $formatClassName;
	$wgAutoloadClasses[$formatClassName] = $formatFileName;
}

global $IP;
srfFormat('graph', 'SRFGraph', $IP . '/extensions/SemanticResultFormats/GraphViz/SRF_Graph.php');
srfFormat('googlebar', 'SRFGoogleBar', $IP . '/extensions/SemanticResultFormats/GoogleCharts/SRF_GoogleBar.php');
srfFormat('googlepie', 'SRFGooglePie', $IP . '/extensions/SemanticResultFormats/GoogleCharts/SRF_GooglePie.php');
srfFormat('timeline', 'SRFTimeline', $IP . '/extensions/SemanticResultFormats/Timeline/SRF_Timeline.php');
srfFormat('eventline', 'SRFTimeline', $IP . '/extensions/SemanticResultFormats/Timeline/SRF_Timeline.php');

