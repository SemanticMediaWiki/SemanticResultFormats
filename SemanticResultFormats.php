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

define( 'SRF_VERSION', '1.5.2 alpha' );

// Require the settings file.
require dirname( __FILE__ ) . '/SRF_Settings.php';

srffInitFormats();

$wgExtensionMessagesFiles['SemanticResultFormats'] = dirname( __FILE__ ) . '/SRF_Messages.php';

// FIXME: hardcoded path
$srfgScriptPath = $wgScriptPath . '/extensions/SemanticResultFormats';
$srfgIP = dirname( __FILE__ );

$wgHooks['AdminLinks'][] = 'srffAddToAdminLinks';
$wgHooks['ParserFirstCallInit'][] = 'SRFParserFunctions::registerFunctions';

$wgAutoloadClasses['SRFParserFunctions'] = $srfgIP . '/SRF_ParserFunctions.php';
// FIXME: Can be removed when new style magic words are used (introduced in r52503)
$wgHooks['LanguageGetMagic'][] = 'SRFParserFunctions::languageGetMagic';

$wgExtensionCredits[defined( 'SEMANTIC_EXTENSION_TYPE' ) ? 'semantic' : 'other'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Result Formats',
	'version' => SRF_VERSION,
	'author' => array(
		'Frank Dengler',
		'[http://steren.fr Steren Giannini]',
		'Sanyam Goyal',
		'Fabian Howahl',
		'Yaron Koren',
		'[http://korrekt.org Markus Krötzsch]',
		'David Loomer',
		'Joel Natividad',
		'[http://simia.net Denny Vrandecic]',
		'Nathan Yergler',
		'Hans-Jörg Happel',
		'Rowan Rodrik van der Molen'
	),
	'url' => 'http://semantic-mediawiki.org/wiki/Help:Semantic_Result_Formats',
	'descriptionmsg' => 'srf-desc'
);

/**
 * Autoload the query printer classes and associate them with their formats in the $smwgResultFormats array.
 * 
 * @since 1.5.2
 */
function srffInitFormats() {
	global $srfgFormats, $smwgResultFormats, $wgAutoloadClasses;
	
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
	
	$formatClasses = array(
		'timeline' => 'SRFTimeline',
		'eventline' => 'SRFTimeline',
		'vcard' => 'SRFvCard',
		'icalendar' => 'SRFiCalendar',
		'bibtex' => 'SRFBibTeX',
		'calendar' => 'SRFCalendar',
		'outline' => 'SRFOutline',
		'sum' => 'SRFMath',
		'average' => 'SRFMath',
		'min' => 'SRFMath',
		'max' => 'SRFMath',
		'exhibit' => 'SRFExhibit',
		'googlebar' => 'SRFGoogleBar',
		'googlepie' => 'SRFGooglePie',
		'jqplotpie' => 'SRFjqPlotPie',
		'jqplotbar' => 'SRFjqPlotBar',
		'graph' => 'SRFGraph',
		'process' => 'SRFProcess',
		'ploticusvbar' => 'SRFPloticusVBar',
		'gallery' => 'SRFGallery',
	);
	
	foreach ( $srfgFormats as $format ) {
		if ( array_key_exists( $format, $formatClasses ) ) {
			$smwgResultFormats[$format] = $formatClasses[$format];
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
	wfLoadExtensionMessages( 'SemanticResultFormats' );
	$srf_docu_label = wfMsg( 'adminlinks_documentation', wfMsg( 'srf-name' ) );
	$smw_docu_row->addItem( AlItem::newFromExternalLink( 'http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats', $srf_docu_label ) );
	
	return true;
}
