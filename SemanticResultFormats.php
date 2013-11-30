<?php

/**
 * Main entry point for the SemanticResultFormats extension.
 * http://www.semantic-mediawiki.org/wiki/Semantic_Result_Formats
 *
 * @licence GNU GPL v2 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( version_compare( $GLOBALS['wgVersion'], '1.19c', '<' ) ) {
	throw new Exception( 'This version of Semantic Result Formats requires MediaWiki 1.17 or above; use SRF 1.7.x or SRF 1.6.x for older versions.' );
}

if ( !defined( 'SMW_VERSION' ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

if ( ! defined( 'SMW_VERSION' ) ) {
	throw new Exception( 'You need to have Semantic MediaWiki installed in order to use Semantic Result Formats' );
}

if ( defined( 'SRF_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SRF_VERSION', '1.9 alpha' );

// Initialize the formats later on, so the $srfgFormats setting can be manipulated in LocalSettings.
$GLOBALS['wgExtensionFunctions'][] = 'srffInitFormats';

$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormats'] = __DIR__ . '/SemanticResultFormats.i18n.php';
$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormatsMagic'] = __DIR__ . '/SemanticResultFormats.i18n.magic.php';

$srfgScriptPath = ( $GLOBALS['wgExtensionAssetsPath'] === false ?
		$GLOBALS['wgScriptPath'] . '/extensions' : $GLOBALS['wgExtensionAssetsPath'] ) . '/SemanticResultFormats';

$srfgIP = __DIR__;

// Require the settings file.
require __DIR__ . '/SemanticResultFormats.settings.php';

// Resource definitions
$GLOBALS['wgResourceModules'] = array_merge( $GLOBALS['wgResourceModules'], include( __DIR__ . "/Resources.php" ) );

$GLOBALS['wgExtensionCredits']['semantic'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Result Formats',
	'version' => SRF_VERSION,
	// At least 14 people have contributed formats to this extension, so
	// it would be prohibitive to list them all in the credits. Instead,
	// the current rule is to list anyone who has created, or contributed
	// significantly to, at least three formats, or the overall extension.
	'author' => array(
		'James Hong Kong',
		'Stephan Gambke',
		'[https://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]',
		'Yaron Koren',
		'...'
	),
	'url' => 'http://semantic-mediawiki.org/wiki/Semantic_Result_Formats',
	'descriptionmsg' => 'srf-desc'
);

$formatDir = __DIR__ . '/formats/';

global $wgAutoloadClasses;

$wgAutoloadClasses['SRFExhibit'] = $formatDir . 'Exhibit/SRF_Exhibit.php';
$wgAutoloadClasses['SRFJitGraph'] = $formatDir . 'JitGraph/SRF_JitGraph.php';
$wgAutoloadClasses['SRFFiltered'] = $formatDir . 'Filtered/SRF_Filtered.php';

// Boilerplate
// Uncomment the line below and adopt the class name
// $wgAutoloadClasses['SRFBoilerplate'] = $formatDir . 'boilerplate/SRF_Boilerplate.php';

// Follows naming convention
$wgAutoloadClasses['SRF\DataTables'] = $formatDir . 'datatables/DataTables.php';

$wgAutoloadClasses['SRF\MediaPlayer'] = $formatDir . 'media/MediaPlayer.php';
$wgAutoloadClasses['SRF\EventCalendar']   = $formatDir . 'calendar/EventCalendar.php';
$wgAutoloadClasses['SRFDygraphs']     = $formatDir . 'dygraphs/SRF_Dygraphs.php';
$wgAutoloadClasses['SRFTimeseries']   = $formatDir . 'timeseries/SRF_Timeseries.php';
$wgAutoloadClasses['SRFjqPlot']       = $formatDir . 'jqplot/SRF_jqPlot.php';
$wgAutoloadClasses['SRFjqPlotChart']  = $formatDir . 'jqplot/SRF_jqPlotChart.php';
$wgAutoloadClasses['SRFjqPlotSeries'] = $formatDir . 'jqplot/SRF_jqPlotSeries.php';
$wgAutoloadClasses['SRFPloticusVBar'] = $formatDir . 'ploticus/SRF_PloticusVBar.php';
$wgAutoloadClasses['SRFPageWidget']   = $formatDir . 'widget/SRF_PageWidget.php';
$wgAutoloadClasses['SRFListWidget']   = $formatDir . 'widget/SRF_ListWidget.php';
$wgAutoloadClasses['SRFIncoming']  = $formatDir . 'incoming/SRF_Incoming.php';
$wgAutoloadClasses['SRFSparkline'] = $formatDir . 'sparkline/SRF_Sparkline.php';
$wgAutoloadClasses['SRFD3Chart']   = $formatDir . 'd3/SRF_D3Chart.php';
$wgAutoloadClasses['SRFGraph']     = $formatDir . 'graphviz/SRF_Graph.php';
$wgAutoloadClasses['SRFProcess']   = $formatDir . 'graphviz/SRF_Process.php';
$wgAutoloadClasses['SRFCalendar']  = $formatDir . 'calendar/SRF_Calendar.php';
$wgAutoloadClasses['SRFArray']     = $formatDir . 'array/SRF_Array.php';
$wgAutoloadClasses['SRFHash']      = $formatDir . 'array/SRF_Hash.php';
$wgAutoloadClasses['SRFiCalendar'] = $formatDir . 'icalendar/SRF_iCalendar.php';
$wgAutoloadClasses['SRFGoogleBar'] = $formatDir . 'googlecharts/SRF_GoogleBar.php';
$wgAutoloadClasses['SRFGooglePie'] = $formatDir . 'googlecharts/SRF_GooglePie.php';
$wgAutoloadClasses['SRFOutline']   = $formatDir . 'outline/SRF_Outline.php';
$wgAutoloadClasses['SRFTime']      = $formatDir . 'time/SRF_Time.php';
$wgAutoloadClasses['SRFSlideShow'] = $formatDir . 'slideshow/SRF_SlideShow.php';
$wgAutoloadClasses['SRFSlideShowApi'] = $formatDir . 'slideshow/SRF_SlideShowApi.php';
$wgAutoloadClasses['SRFTree']      = $formatDir . 'tree/SRF_Tree.php';
$wgAutoloadClasses['SRF\Gallery']   = $formatDir . 'gallery/Gallery.php';
$wgAutoloadClasses['SRF\TagCloud']  = $formatDir . 'tagcloud/TagCloud.php';
$wgAutoloadClasses['SRFMath']      = $formatDir . 'math/SRF_Math.php';
$wgAutoloadClasses['SRFTimeline']  = $formatDir . 'timeline/SRF_Timeline.php';
$wgAutoloadClasses['SRFvCard']     = $formatDir . 'vcard/SRF_vCard.php';
$wgAutoloadClasses['SRFValueRank'] = $formatDir . 'valuerank/SRF_ValueRank.php';
$wgAutoloadClasses['SRFBibTeX']    = $formatDir . 'bibtex/SRF_BibTeX.php';
$wgAutoloadClasses['SRF\SRFExcel']     = $formatDir . 'excel/SRF_Excel.php';

unset( $formatDir );

$wgAutoloadClasses['SRFParserFunctions'] = $srfgIP . '/SemanticResultFormats.parser.php';
$wgAutoloadClasses['SRFHooks']           = $srfgIP . '/SemanticResultFormats.hooks.php';
$wgAutoloadClasses['SRFUtils']           = $srfgIP . '/SemanticResultFormats.utils.php';

global $wgHooks;

$wgHooks['AdminLinks'][] = 'SRFHooks::addToAdminLinks';
$wgHooks['ParserFirstCallInit'][] = 'SRFParserFunctions::registerFunctions';
$wgHooks['UnitTestsList'][] = 'SRFHooks::registerUnitTests';

$wgHooks['ResourceLoaderTestModules'][] = 'SRFHooks::registerQUnitTests';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'SRFHooks::onResourceLoaderGetConfigVars';

// register API modules
$GLOBALS['wgAPIModules']['ext.srf.slideshow.show'] = 'SRFSlideShowApi';

// User preference
$GLOBALS['wgHooks']['GetPreferences'][] = 'SRFHooks::onGetPreferences';

/**
 * Autoload the query printer classes and associate them with their formats in the $smwgResultFormats array.
 *
 * @since 1.5.2
 */
function srffInitFormats() {
	global $srfgFormats, $smwgResultFormats, $smwgResultAliases;

	$formatClasses = array(
		// Assign the Boilerplate class to a format identifier
		// 'boilerplate' => 'SRFBoilerplate',
		'timeline' => 'SRFTimeline',
		'eventline' => 'SRFTimeline',
		'vcard' => 'SRFvCard',
		'icalendar' => 'SRFiCalendar',
		'bibtex' => 'SRFBibTeX',
		'calendar' => 'SRFCalendar',
		'eventcalendar' => 'SRF\EventCalendar',
		'outline' => 'SRFOutline',
		'sum' => 'SRFMath',
		'product' => 'SRFMath',
		'average' => 'SRFMath',
		'min' => 'SRFMath',
		'max' => 'SRFMath',
		'median' => 'SRFMath',
		'exhibit' => 'SRFExhibit',
		'googlebar' => 'SRFGoogleBar',
		'googlepie' => 'SRFGooglePie',
		'jitgraph' => 'SRFJitGraph',
		'jqplotchart' => 'SRFjqPlotChart',
		'jqplotseries' => 'SRFjqPlotSeries',
		'graph' => 'SRFGraph',
		'process' => 'SRFProcess',
		'ploticusvbar' => 'SRFPloticusVBar',
		'gallery' => 'SRF\Gallery',
		'tagcloud' => 'SRF\TagCloud',
		'valuerank' => 'SRFValueRank',
		'array' => 'SRFArray',
		'hash' => 'SRFHash',
		'd3chart' => 'SRFD3Chart',
		'tree' => 'SRFTree',
		'ultree' => 'SRFTree',
		'oltree' => 'SRFTree',
		'filtered' => 'SRFFiltered',
		'latest' => 'SRFTime',
		'earliest' => 'SRFTime',
		'slideshow' => 'SRFSlideShow',
		'timeseries' => 'SRFTimeseries',
		'sparkline' => 'SRFSparkline',
		'listwidget' => 'SRFListWidget',
		'pagewidget' => 'SRFPageWidget',
		'dygraphs' => 'SRFDygraphs',
		'incoming' => 'SRFIncoming',
		'media' => 'SRF\MediaPlayer',
		'excel' => 'SRF\SRFExcel',
		'datatables' => 'SRF\DataTables'
	);

	$formatAliases = array(
		'tagcloud'   => array( 'tag cloud' ),
		'datatables'   => array( 'datatable' ),
		'valuerank'  => array( 'value rank' ),
		'd3chart'    => array( 'd3 chart' ),
		'timeseries' => array ( 'time series' ),
		'jqplotchart' => array( 'jqplot chart', 'jqplotpie', 'jqplotbar' ),
		'jqplotseries' => array( 'jqplot series' ),
	);

	foreach ( $srfgFormats as $format ) {
		if ( array_key_exists( $format, $formatClasses ) ) {
			$smwgResultFormats[$format] = $formatClasses[$format];

			if ( isset( $smwgResultAliases ) && array_key_exists( $format, $formatAliases ) ) {
				$smwgResultAliases[$format] = $formatAliases[$format];
			}
		}
		else {
			wfDebug( "There is no result format class associated with the '$format' format." );
		}
	}
}
