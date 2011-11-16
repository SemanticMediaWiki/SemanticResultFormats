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
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( !defined( 'SRF_VERSION' ) ) {
	require_once dirname( __FILE__ ) . '/SemanticResultFormats.php';
}

# The formats you want to be able to use.
# See the INSTALL file or this url for more info: http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats#Installation
$srfgFormats = array(
	'icalendar',
	'vcard',
	'bibtex',
	'calendar',
	'eventline',
	'timeline',
	'outline',
	'gallery',
	'jqplotbar',
	'jqplotpie',
	'sum',
	'average',
	'min',
	'max',
	'median',
	'product',
	'tagcloud',
	'valuerank',
	'array',
);

if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) ) {
	$srfgFormats[] = 'D3Line';
	$srfgFormats[] = 'D3Bar';
	$srfgFormats[] = 'D3Treemap';
}

# load hash format only if HashTables extension is initialised, otherwise 'Array' format is enough
if( isset( $wgHashTables ) ) {
	$srfgFormats[] = 'hash';
}

# Used for jqplot formats.
$srfgJQPlotIncluded = false;

# Used for Array and Hash formats. 
# Allows value as string or object instances of Title or Article classes or an array
# where index 0 is the page title and 1 is the namespace-index (by default NS_MAIN)
# also allows defining optional template-arguments by index 'args' as array where a
# key represents an argument name and a keys associated value an argument value.
$srfgArraySep = ', ';
$srfgArrayPropSep = '<PROP>';
$srfgArrayManySep = '<MANY>';
$srfgArrayRecordSep = '<RCRD>';
$srfgArrayHeaderSep = ' ';

/**
 * used if Array|Hash result format isn't used inline and the standard config values
 * defined in LocalSettings.php can't be used because they are page references which
 * can only be evaluated in inline queries
 * 
 * @var Array
 */
$srfgArraySepTextualFallbacks = array (
	'sep'       => $srfgArraySep,
	'propsep'   => $srfgArrayPropSep,
	'manysep'   => $srfgArrayManySep,
	'recordsep' => $srfgArrayRecordSep,
	'headersep' => $srfgArrayHeaderSep
);
$srfgArraySep = array( 'bla', NS_TEMPLATE );
