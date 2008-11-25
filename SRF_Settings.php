<?php
/**
 * Initializing file for the diverse query result formats supported in this
 * extension. In order to use them, add the following lines to your
 * LocalSettings.php:
 *
 * include_once('path/to/here/SRF_Settings.php');
 *
 * If nothing else is added, no format will be included. In order to include a
 * format, add the following line to your local settings after the line above:
 * 
 *  srfInit( array('formatname', 'formatname') );
 * 
 * With formatname being one of the following values:
 * 
 *  calendar, eventline, googlebar, googlepie, graph, timeline
 * 
 * You can also just use the following command to include all formats:
 * 
 *  srfInit('all');
 * 
 * but this is not recommended.
 */
if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

define('SRF_VERSION', '1.4.0');

global $srfgScriptPath, $srfgIP;
$srfgScriptPath = $wgScriptPath . '/extensions/SemanticResultFormats';
$srfgIP = $IP . '/extensions/SemanticResultFormats';
global $wgExtensionMessagesFiles;
$wgExtensionMessagesFiles['SemanticResultFormats'] = $srfgIP . '/SRF_Messages.php';

function srfInit( $formatName ) {
	if ($formatName == 'all') {
		srfInit(array('calendar', 'eventline', 'googlebar', 'googlepie', 'graph', 'timeline'));
	} elseif (is_array($formatName)) {
		foreach($formatName as $fn) srfInit($fn);
		
		$formats = implode(', ', $formatName);
		global $wgExtensionCredits;
		$wgExtensionCredits['other'][]= array(
			'name' => 'Semantic Result Formats',
			'version' => SRF_VERSION,
			'author' => "[http://simia.net Denny&nbsp;Vrandecic], Frank Dengler and Yaron Koren",
			'url' => 'http://www.semantic-mediawiki.org/wiki/Help:Semantic_Result_Formats',
			'description' => 'Additional formats for Semantic MediaWiki inline queries. Available formats: ' . $formats
		);
	} else {
		global $smwgResultFormats, $wgAutoloadClasses, $srfgIP;

		$class = '';
		$file = '';
		if ($formatName == 'graph') { $class = 'SRFGraph'; $file = $srfgIP . '/GraphViz/SRF_Graph.php'; }
		if ($formatName == 'googlebar') { $class = 'SRFGoogleBar'; $file = $srfgIP . '/GoogleCharts/SRF_GoogleBar.php'; }
		if ($formatName == 'googlepie') { $class = 'SRFGooglePie'; $file = $srfgIP . '/GoogleCharts/SRF_GooglePie.php'; }
		if ($formatName == 'timeline') { $class = 'SRFTimeline'; $file = $srfgIP . '/Timeline/SRF_Timeline.php'; }
		if ($formatName == 'eventline') { $class = 'SRFTimeline'; $file = $srfgIP . '/Timeline/SRF_Timeline.php'; }
		if ($formatName == 'calendar') { $class = 'SRFCalendar'; $file = $srfgIP . '/Calendar/SRF_Calendar.php'; }
		if (($class != '') && ($file)) {
			$smwgResultFormats[$formatName] = $class;
			$wgAutoloadClasses[$class] = $file;
		}
	}
}

