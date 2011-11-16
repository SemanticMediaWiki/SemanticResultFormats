<?php

/**
 * Main entry point for the SemanticResultFormats extension.
 * http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats
 * 
 * @file SemanticResultFormats.php
 * @ingroup SemanticResultFormats
 * 
 * @author Jeroen De Dauw
 */

/**
 * This documentation group collects source code files belonging to SemanticResultFormats.
 * 
 * @defgroup SemanticResultFormats SemanticResultFormats
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( version_compare( $wgVersion, '1.16', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Result Formats requires MediaWiki 1.16 or above; use SRF 1.6.x for older versions.' );
}

// Show a warning if Semantic MediaWiki is not loaded.
if ( ! defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> You need to have <a href="http://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> installed in order to use <a href="http://www.mediawiki.org/wiki/Extension:Semantic Result Formats">Semantic Result Formats</a>.<br />' );
}

if ( version_compare( SMW_VERSION, '1.6.2 alpha', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Result Formats requires Semantic MediaWiki 1.7 or above; use Semantic Result Formats 1.6.1 for older versions of SMW.' );
}

define( 'SRF_VERSION', '1.7 alpha' );

// Require the settings file.
require dirname( __FILE__ ) . '/SRF_Settings.php';

if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) ) {
	require dirname( __FILE__ ) . '/SRF_Resources.php';
}

// Initialize the formats later on, so the $srfgFormats setting can be manipulated in LocalSettings.
$wgExtensionFunctions[] = 'srffInitFormats';

$wgExtensionMessagesFiles['SemanticResultFormats'] = dirname( __FILE__ ) . '/SRF_Messages.php';

// FIXME: hardcoded path
$srfgScriptPath = $wgScriptPath . '/extensions/SemanticResultFormats';
$srfgIP = dirname( __FILE__ );

$wgHooks['AdminLinks'][] = 'srffAddToAdminLinks';
$wgHooks['ParserFirstCallInit'][] = 'SRFParserFunctions::registerFunctions';

$wgAutoloadClasses['SRFParserFunctions'] = $srfgIP . '/SRF_ParserFunctions.php';

// FIXME: Can be removed when new style magic words are used (introduced in r52503)
$wgHooks['LanguageGetMagic'][] = 'SRFParserFunctions::languageGetMagic';

$wgExtensionCredits['semantic'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Result Formats',
	'version' => SRF_VERSION,
	'author' => array(
		'[http://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]',
		'Yaron Koren',
		'Sanyam Goyal',
		'others'
	),
	'url' => 'http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats',
	'descriptionmsg' => 'srf-desc'
);

/**
 * Autoload the query printer classes and associate them with their formats in the $smwgResultFormats array.
 * 
 * @since 1.5.2
 */
function srffInitFormats() {
	global $srfgFormats, $smwgResultFormats, $smwgResultAliases, $wgAutoloadClasses;
	
	$formatDir = dirname( __FILE__ ) . '/';
	
	$wgAutoloadClasses['SRFTimeline'] = $formatDir . 'Timeline/SRF_Timeline.php';
	$wgAutoloadClasses['SRFvCard'] = $formatDir . 'vCard/SRF_vCard.php';
	$wgAutoloadClasses['SRFiCalendar'] = $formatDir . 'iCalendar/SRF_iCalendar.php';
	$wgAutoloadClasses['SRFBibTeX'] = $formatDir . 'BibTeX/SRF_BibTeX.php';
	$wgAutoloadClasses['SRFCalendar'] = $formatDir . 'Calendar/SRF_Calendar.php';
	$wgAutoloadClasses['SRFOutline'] = $formatDir . 'Outline/SRF_Outline.php';
	$wgAutoloadClasses['SRFMath'] = $formatDir . 'Math/SRF_Math.php';
	$wgAutoloadClasses['SRFExhibit'] = $formatDir . 'Exhibit/SRF_Exhibit.php';
	$wgAutoloadClasses['SRFGoogleBar'] = $formatDir . 'GoogleCharts/SRF_GoogleBar.php';
	$wgAutoloadClasses['SRFGooglePie'] = $formatDir . 'GoogleCharts/SRF_GooglePie.php';
	$wgAutoloadClasses['SRFjqPlotPie'] = $formatDir . 'jqPlot/SRF_jqPlotPie.php';
	$wgAutoloadClasses['SRFjqPlotBar'] = $formatDir . 'jqPlot/SRF_jqPlotBar.php';
	$wgAutoloadClasses['SRFGraph'] = $formatDir . 'GraphViz/SRF_Graph.php';
	$wgAutoloadClasses['SRFProcess'] = $formatDir . 'GraphViz/SRF_Process.php';
	$wgAutoloadClasses['SRFPloticusVBar'] = $formatDir . 'Ploticus/SRF_PloticusVBar.php';
	$wgAutoloadClasses['SRFGallery'] = $formatDir . 'Gallery/SRF_Gallery.php';
	$wgAutoloadClasses['SRFTagCloud'] = $formatDir . 'TagCloud/SRF_TagCloud.php';
	$wgAutoloadClasses['SRFArray'] = $formatDir . 'Array/SRF_Array.php';
	$wgAutoloadClasses['SRFHash'] = $formatDir . 'Array/SRF_Array.php';
	$wgAutoloadClasses['SRFValueRank'] = $formatDir . 'ValueRank/SRF_ValueRank.php';
	$wgAutoloadClasses['SRFD3Line'] = $formatDir . 'D3/SRF_D3Line.php';
	$wgAutoloadClasses['SRFD3Bar'] = $formatDir . 'D3/SRF_D3Bar.php';
	$wgAutoloadClasses['SRFD3Treemap'] = $formatDir . 'D3/SRF_D3Treemap.php';        
	
	$formatClasses = array(
		'timeline' => 'SRFTimeline',
		'eventline' => 'SRFTimeline',
		'vcard' => 'SRFvCard',
		'icalendar' => 'SRFiCalendar',
		'bibtex' => 'SRFBibTeX',
		'calendar' => 'SRFCalendar',
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
		'jqplotpie' => 'SRFjqPlotPie',
		'jqplotbar' => 'SRFjqPlotBar',
		'graph' => 'SRFGraph',
		'process' => 'SRFProcess',
		'ploticusvbar' => 'SRFPloticusVBar',
		'gallery' => 'SRFGallery',
		'tagcloud' => 'SRFTagCloud',
		'valuerank' => 'SRFValueRank',
		'array' => 'SRFArray',
		'hash' => 'SRFHash',
		'D3Line' => 'SRFD3Line',
		'D3Bar' => 'SRFD3Bar',
		'D3Treemap' => 'SRFD3Treemap',
	);

	$formatAliases = array(
		'tagcloud' => array( 'tag cloud' ),
		'valuerank' => array( 'value rank' )
	);
	
	foreach ( $srfgFormats as $format ) {
		if ( array_key_exists( $format, $formatClasses ) ) {
			$smwgResultFormats[$format] = $formatClasses[$format];
			
			// Register the resource loader modules for when they are supported.
			if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && method_exists( $formatClasses[$format], 'registerResourceModules' ) ) {
				call_user_func( array( $formatClasses[$format], 'registerResourceModules' ) );
			}
			
			if ( isset( $smwgResultAliases ) && array_key_exists( $format, $formatAliases ) ) {
				$smwgResultAliases[$format] = $formatAliases[$format];
			}
		}
		else {
			wfDebug( "There is not result format class associated with the format '$format'." );
		}
	}		
}

/**
 * Adds a link to Admin Links page.
 */
function srffAddToAdminLinks( &$admin_links_tree ) {
	$displaying_data_section = $admin_links_tree->getSection( wfMsg( 'smw_adminlinks_displayingdata' ) );
	
	// Escape is SMW hasn't added links.
	if ( is_null( $displaying_data_section ) ) {
		return true;
	}
		
	$smw_docu_row = $displaying_data_section->getRow( 'smw' );
	$srf_docu_label = wfMsg( 'adminlinks_documentation', wfMsg( 'srf-name' ) );
	$smw_docu_row->addItem( AlItem::newFromExternalLink( 'http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats', $srf_docu_label ) );
	
	return true;
}
