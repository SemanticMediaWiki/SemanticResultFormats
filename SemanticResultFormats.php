<?php

/**
 * @see https://github.com/SemanticMediaWiki/SemanticResultFormats/
 *
 * @defgroup SRF Semantic Result Formats
 */

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
	}

	/**
	 * @since 2.5
	 */
	public static function initExtension( $credits = [] ) {

		// See https://phabricator.wikimedia.org/T151136
		define( 'SRF_VERSION', isset( $credits['version'] ) ? $credits['version'] : 'UNKNOWN' );

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
		$GLOBALS['wgHooks']['SMW::GetPreferences'][] = 'SRFHooks::onGetPreferences';

		// Allows last minute changes to the output page, e.g. adding of CSS or JavaScript by extensions
		$GLOBALS['wgHooks']['BeforePageDisplay'][] = 'SRFHooks::onBeforePageDisplay';
	}

	/**
	 * @since 2.5
	 */
	public static function onExtensionFunction() {

		if ( !defined( 'SMW_VERSION' ) ) {

			if ( PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' ) {
				die( "\nThe 'Semantic Result Formats' extension requires 'Semantic MediaWiki' to be installed and enabled.\n" );
			} else {
				die(
					'<b>Error:</b> The <a href="https://github.com/SemanticMediaWiki/SemanticResultFormats/">Semantic Result Formats</a> ' .
					'extension requires <a href="https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> to be ' .
					'installed and enabled.<br />'
				);
			}
		}

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
			'vcard' => 'SRF\vCard\vCardFileExportPrinter',
			'icalendar' => 'SRF\iCalendar\iCalendarFileExportPrinter',
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
