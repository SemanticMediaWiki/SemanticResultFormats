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

define('SRF_VERSION', '1.4.4');

$srfgScriptPath = $wgScriptPath . '/extensions/SemanticResultFormats';
$srfgIP = $IP . '/extensions/SemanticResultFormats';
$wgExtensionMessagesFiles['SemanticResultFormats'] = $srfgIP . '/SRF_Messages.php';
$wgExtensionFunctions[] = 'srffSetup';

$wgAutoloadClasses['SRFParserFunctions'] = $srfgIP . '/SRF_ParserFunctions.php';

$wgHooks['LanguageGetMagic'][] = 'SRFParserFunctions::languageGetMagic';
$wgExtensionFunctions[] = 'srffRegisterFunctions';

$srfgFormats = array('icalendar', 'vcard', 'bibtex', 'calendar', 'eventline', 'timeline', 'sum', 'average', 'min', 'max');

function srffSetup() {
	global $srfgFormats, $wgExtensionCredits;

	foreach($srfgFormats as $fn) srffInitFormat($fn);
	$formats_list = implode(', ', $srfgFormats);
	$wgExtensionCredits['other'][]= array(
		'path' => __FILE__,
		'name' => 'Semantic Result Formats',
		'version' => SRF_VERSION,
		'author' => "Frank Dengler, [http://steren.fr Steren Giannini], Fabian Howahl, Yaron Koren, [http://korrekt.org Markus Krötzsch], David Loomer, Joel Natividad, [http://simia.net Denny&nbsp;Vrandecic], Nathan Yergler",
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
		case 'bibtex':
			$class = 'SRFBibTeX';
			$file = $srfgIP . '/BibTeX/SRF_BibTeX.php';
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

function srffRegisterFunctions ( ) {
	global $wgHooks, $wgParser;
	if( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
		$wgHooks['ParserFirstCallInit'][] = 'SRFParserFunctions::registerFunctions';
	} else {
		if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
			$wgParser->_unstub();
		}
		SRFParserFunctions::registerFunctions( $wgParser );
	}

}
