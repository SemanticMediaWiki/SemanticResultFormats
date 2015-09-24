<?php

/**
 * Main entry point for the Semantic Result Formats (SRF) extension.
 * https://www.semantic-mediawiki.org/wiki/Semantic_Result_Formats
 *
 * @licence GNU GPL v2 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( defined( 'SRF_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SRF_VERSION', '2.3' );

if ( version_compare( $GLOBALS['wgVersion'], '1.19c', '<' ) ) {
	throw new Exception( 'This version of Semantic Result Formats requires MediaWiki 1.19 or above; use SRF 1.7.x or SRF 1.6.x for older versions.' );
}

if ( !defined( 'SMW_VERSION' ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

if ( ! defined( 'SMW_VERSION' ) ) {
	throw new Exception( 'You need to have Semantic MediaWiki installed in order to use Semantic Result Formats' );
}

$GLOBALS['wgMessagesDirs']['SemanticResultFormats'] = __DIR__ . '/i18n';
$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormats'] = __DIR__ . '/SemanticResultFormats.i18n.php';
$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormatsMagic'] = __DIR__ . '/SemanticResultFormats.i18n.magic.php';

$GLOBALS['srfgIP'] = __DIR__;

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
	'url' => 'https://semantic-mediawiki.org/wiki/Semantic_Result_Formats',
	'descriptionmsg' => 'srf-desc',
	'license-name'   => 'GPL-2.0+'
);

$formatDir = __DIR__ . '/formats/';

global $wgAutoloadClasses;

unset( $formatDir );

global $wgHooks;

// Admin Links hook needs to be called in a delayed way so that it
// will always be called after SMW's Admin Links addition; as of
// SMW 1.9, SMW delays calling all its hook functions.
$wgExtensionFunctions[] = function() {
	$GLOBALS['wgHooks']['AdminLinks'][] = 'SRFHooks::addToAdminLinks';
};

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
$GLOBALS['wgExtensionFunctions'][] = function() {
	global $srfgFormats, $smwgResultFormats, $smwgResultAliases;

	$GLOBALS['srfgScriptPath'] = ( $GLOBALS['wgExtensionAssetsPath'] === false ?
			$GLOBALS['wgScriptPath'] . '/extensions' : $GLOBALS['wgExtensionAssetsPath'] ) . '/SemanticResultFormats';

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
};
