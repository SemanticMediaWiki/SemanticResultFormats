<?php
/**
 * Initializing file for the Semantic Result Formats extension.
 *
 * @file
 * @ingroup SemanticResultFormats
 */
if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

define('SRF_VERSION', '1.4.2');

$srfgScriptPath = $wgScriptPath . '/extensions/SemanticResultFormats';
$srfgIP = $IP . '/extensions/SemanticResultFormats';
$wgExtensionMessagesFiles['SemanticResultFormats'] = $srfgIP . '/SRF_Messages.php';
$wgExtensionFunctions[] = 'srffSetup';

$srfgFormats = array('icalendar', 'vcard', 'calendar', 'eventline', 'timeline', 'sum', 'average', 'min', 'max');

function srffSetup() {
	global $srfgFormats, $wgExtensionCredits;

	foreach($srfgFormats as $fn) srffInitFormat($fn);
	$formats_list = implode(', ', $srfgFormats);
	$wgExtensionCredits['other'][]= array(
		'name' => 'Semantic Result Formats',
		'version' => SRF_VERSION,
		'author' => "[http://simia.net Denny&nbsp;Vrandecic], Frank Dengler, Yaron Koren, Nathan Yergler, Joel Natividad, Fabian Howahl, [http://korrekt.org Markus Krötzsch]",
		'url' => 'http://semantic-mediawiki.org/wiki/Help:Semantic_Result_Formats',
		'description' => 'Additional formats for Semantic MediaWiki inline queries. Available formats: ' . $formats_list
	);
}

function srffInitFormat( $format ) {
	global $smwgResultFormats, $wgAutoloadClasses, $srfgIP;

	$class = '';
	$file = '';
	switch ($format) {
		case 'timeline': case 'eventline':
			$class = 'SRFTimeline';
			$file = $srfgIP . '/Timeline/SRF_Timeline.php';
		break;
		case 'vcard':
			$class = 'SRFvCard';
			$file = $srfgIP . '/vCard/SRF_vCard.php';
		break;
		case 'icalendar':
			$class = 'SRFiCalendar';
			$file = $srfgIP . '/iCalendar/SRF_iCalendar.php';
		break;
		case 'calendar':
			$class = 'SRFCalendar';
			$file = $srfgIP . '/Calendar/SRF_Calendar.php';
		breaK;
		case  'sum': case 'average': case 'min': case 'max':
			$class = 'SRFMath';
			$file = $srfgIP . '/Math/SRF_Math.php';
		break;
		case 'exhibit':
			$class = 'SRFExhibit';
			$file = $srfgIP . '/Exhibit/SRF_Exhibit.php';
		break;
		case 'googlebar':
			$class = 'SRFGoogleBar';
			$file = $srfgIP . '/GoogleCharts/SRF_GoogleBar.php';
		break;
		case 'googlepie':
			$class = 'SRFGooglePie';
			$file = $srfgIP . '/GoogleCharts/SRF_GooglePie.php';
		break;
		case 'ploticus':
			$class = 'SRFPloticus';
			$file = $srfgIP . '/Ploticus/SRF_Ploticus.php';
		break;
		case 'graph':
			$class = 'SRFGraph';
			$file = $srfgIP . '/GraphViz/SRF_Graph.php';
		break;
	}
	if (($class) && ($file)) {
		$smwgResultFormats[$format] = $class;
		$wgAutoloadClasses[$class] = $file;
	}
}

