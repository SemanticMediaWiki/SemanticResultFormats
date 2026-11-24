<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 2/24/17
 * Time: 11:00 PM
 */

namespace SRF\Filtered;

use OutputPage;
use ParserOutput;

class Hooks {

	/**
	 * Only needed for ParserOutputs cached before the configuration moved to
	 * per-instance JS config vars (see Filtered::addConfigToOutput).
	 */
	public static function onOutputPageParserOutput( OutputPage &$outputPage, ParserOutput $parserOutput ) {
		$config = $parserOutput->getExtensionData( 'srf-filtered-config' );
		if ( $config !== null ) {
			$outputPage->addJsConfigVars( 'srfFilteredConfig', $config );
		}
	}

}
