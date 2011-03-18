<?php
/**
 * Settings file for the Semantic Result Formats extension.
 * http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats
 * 
 * NOTE: Do not use this file as entry point, use SemanticresultFormats.php instead.
 *
 * @file SRF_Settings.php
 * @ingroup SemanticResultFormats
 * 
 * @author Jeroen De Dauw
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( !defined( 'SRF_VERSION' ) ) {
	require_once dirname( __FILE__ ) . '/SemanticResultFormats.php';
}

# The formats you want to be able to use.
# See the INSTALL file or this url for more info: http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats#Installation
$srfgFormats = array( 'icalendar', 'vcard', 'bibtex', 'calendar', 'eventline', 'timeline', 'outline', 'gallery', 'jqplotbar', 'jqplotpie', 'sum', 'average', 'min', 'max', 'tagcloud' );

# Used for jqplot formats.
$srfgJQPlotIncluded = false;

# Used for Array and Hash formats. 
# Allows value as string or object instances of Title or Article classes or an array
# where index 0 is the page title and 1 is the namespace-index (by default NS_MAIN)
$srfgArraySep = ', ';
$srfgArrayPropSep = '<PROP>';
$srfgArrayManySep = '<MANY>';
$srfgArrayRecordSep = '<RCRD>';
