<?php
/**
 * Initializing file for the diverse query result formats supported in this
 * extension. In order to use them, add the following lines to your
 * LocalSettings.php:
 *
 * include_once('path/to/here/SRF_Settings.php');
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

global $IP;
$srfgIP = $IP . '/extensions/SemanticResultFormats';
global $srfgScriptPath;
$srfgScriptPath = $wgScriptPath . '/extensions/SemanticResultFormats';

global $wgExtensionMessagesFiles;
$wgExtensionMessagesFiles['SemanticResultFormats'] = $srfgIP . '/SRF_Messages.php';

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

srfFormat('graph', 'SRFGraph', $srfgIP . '/GraphViz/SRF_Graph.php');
srfFormat('googlebar', 'SRFGoogleBar', $srfgIP . '/GoogleCharts/SRF_GoogleBar.php');
srfFormat('googlepie', 'SRFGooglePie', $srfgIP . '/GoogleCharts/SRF_GooglePie.php');
srfFormat('timeline', 'SRFTimeline', $srfgIP . '/Timeline/SRF_Timeline.php');
srfFormat('eventline', 'SRFTimeline', $srfgIP . '/Timeline/SRF_Timeline.php');
srfFormat('calendar', 'SRFCalendar', $srfgIP . '/Calendar/SRF_Calendar.php');

