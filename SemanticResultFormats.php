<?php

/**
 * @see https://github.com/SemanticMediaWiki/SemanticResultFormats/
 *
 * @defgroup SRF Semantic Result Formats
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the Semantic Result Formats extension. It is not a valid entry point.' );
}

if ( defined( 'SRF_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

SemanticResultFormats::load();

/**
 * @codeCoverageIgnore
 */
class SemanticResultFormats {

	/**
	 * @since 2.5
	 *
	 * @note It is expected that this function is loaded before LocalSettings.php
	 * to ensure that settings and global functions are available by the time
	 * the extension is activated.
	 */
	public static function load() {

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once __DIR__ . '/vendor/autoload.php';
		}

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';

		// In case extension.json is being used, the succeeding steps are
		// expected to be handled by the ExtensionRegistry aka extension.json
		self::initExtension();

		$GLOBALS['wgExtensionFunctions'][] = function() {
			self::onExtensionFunction();
		};
	}

	/**
	 * @since 2.5
	 */
	public static function initExtension() {

		define( 'SRF_VERSION', '2.5.6' );

		// Register the extension
		$GLOBALS['wgExtensionCredits']['semantic'][] = [
			'path' => __FILE__,
			'name' => 'Semantic Result Formats',
			'version' => SRF_VERSION,
			// At least 14 people have contributed formats to this extension, so
			// it would be prohibitive to list them all in the credits. Instead,
			// the current rule is to list anyone who has created, or contributed
			// significantly to, at least three formats, or the overall extension.
			'author' => [
				'James Hong Kong',
				'Stephan Gambke',
				'Jeroen De Dauw',
				'Yaron Koren',
				'...'
			],
			'url' => 'https://www.semantic-mediawiki.org/wiki/Extension:Semantic_Result_Formats',
			'descriptionmsg' => 'srf-desc',
			'license-name'   => 'GPL-2.0-or-later'
		];

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticResultFormats'] = __DIR__ . '/i18n';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormats'] = __DIR__ . '/SemanticResultFormats.i18n.php';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormatsMagic'] = __DIR__ . '/SemanticResultFormats.i18n.magic.php';

		$GLOBALS['srfgIP'] = __DIR__;
		$GLOBALS['wgResourceModules'] = array_merge( $GLOBALS['wgResourceModules'], include( __DIR__ . "/Resources.php" ) );

		self::registerHooks();
	}

	/**
	 * @since 2.5
	 */
	public static function registerHooks() {
		$formatDir = __DIR__ . '/formats/';

		unset( $formatDir );

		$GLOBALS['wgHooks']['ParserFirstCallInit'][] = 'SRFParserFunctions::registerFunctions';
		$GLOBALS['wgHooks']['UnitTestsList'][] = 'SRFHooks::registerUnitTests';

		$GLOBALS['wgHooks']['ResourceLoaderTestModules'][] = 'SRFHooks::registerQUnitTests';
		$GLOBALS['wgHooks']['ResourceLoaderGetConfigVars'][] = 'SRFHooks::onResourceLoaderGetConfigVars';

		// Format hooks
		$GLOBALS['wgHooks']['OutputPageParserOutput'][] = 'SRF\Filtered\Hooks::onOutputPageParserOutput';
		$GLOBALS['wgHooks']['MakeGlobalVariablesScript'][] = 'SRF\Filtered\Hooks::onMakeGlobalVariablesScript';

		// register API modules
		$GLOBALS['wgAPIModules']['ext.srf.slideshow.show'] = 'SRFSlideShowApi';

		// User preference
		$GLOBALS['wgHooks']['GetPreferences'][] = 'SRFHooks::onGetPreferences';
	}

	/**
	 * @since 2.5
	 */
	public static function doCheckRequirements() {

		if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.23', 'lt' ) ) {
			die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/">Semantic Result Formats</a> is only compatible with MediaWiki 1.23 or above. You need to upgrade MediaWiki first.' );
		}

		if ( !defined( 'SMW_VERSION' ) ) {
			die( '<b>Error:</b> <a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/">Semantic Result Formats</a> requires the <a href="https://github.com/SemanticMediaWiki/SemanticMediaWiki/">Semantic MediaWiki</a> extension. Please enable or install the extension first.' );
		}
	}

	/**
	 * @since 2.5
	 */
	public static function onExtensionFunction() {

		// Check requirements after LocalSetting.php has been processed, thid has
		// be done here to ensure SMW is loaded in case
		// wfLoadExtension( 'SemanticMediaWiki' ) is used
		self::doCheckRequirements();

		// Admin Links hook needs to be called in a delayed way so that it
		// will always be called after SMW's Admin Links addition; as of
		// SMW 1.9, SMW delays calling all its hook functions.
		$GLOBALS['wgHooks']['AdminLinks'][] = 'SRFHooks::addToAdminLinks';

		$GLOBALS['srfgScriptPath'] = ( $GLOBALS['wgExtensionAssetsPath'] === false ? $GLOBALS['wgScriptPath'] . '/extensions' : $GLOBALS['wgExtensionAssetsPath'] ) . '/SemanticResultFormats';

		$formatClasses = [
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
			'gallery' => 'SRF\Gallery',
			'tagcloud' => 'SRF\TagCloud',
			'valuerank' => 'SRFValueRank',
			'array' => 'SRFArray',
			'hash' => 'SRFHash',
			'd3chart' => 'SRFD3Chart',
			'tree' => 'SRF\Formats\Tree\TreeResultPrinter',
			'ultree' => 'SRF\Formats\Tree\TreeResultPrinter',
			'oltree' => 'SRF\Formats\Tree\TreeResultPrinter',
			'filtered' => 'SRF\Filtered\Filtered',
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
		];

		$formatAliases = [
			'tagcloud'   => [ 'tag cloud' ],
			'datatables'   => [ 'datatable' ],
			'valuerank'  => [ 'value rank' ],
			'd3chart'    => [ 'd3 chart' ],
			'timeseries' =>  [ 'time series' ],
			'jqplotchart' => [ 'jqplot chart', 'jqplotpie', 'jqplotbar' ],
			'jqplotseries' => [ 'jqplot series' ],
		];

		foreach ( $GLOBALS['srfgFormats'] as $format ) {
			if ( array_key_exists( $format, $formatClasses ) ) {
				$GLOBALS['smwgResultFormats'][$format] = $formatClasses[$format];

				if ( isset( $GLOBALS['smwgResultAliases'] ) && array_key_exists( $format, $formatAliases ) ) {
					$GLOBALS['smwgResultAliases'][$format] = $formatAliases[$format];
				}
			}
		}
	}

	/**
	 * @since 2.5
	 *
	 * @return string|null
	 */
	public static function getVersion() {
		return SRF_VERSION;
	}

}
