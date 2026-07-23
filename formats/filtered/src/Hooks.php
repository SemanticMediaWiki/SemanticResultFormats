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

	public static function onOutputPageParserOutput( OutputPage &$outputPage, ParserOutput $parserOutput ) {
		$config = $parserOutput->getExtensionData( 'srf-filtered-config' );
		if ( $config !== null ) {
			$outputPage->setProperty( 'srf-filtered-config', $config );
			$outputPage->addJsConfigVars( 'srfFilteredConfig', $config );
		}
	}

	public static function onMakeGlobalVariablesScript( &$vars, OutputPage $output ) {
		$vars['srfFilteredConfig'] = $output->getProperty( 'srf-filtered-config' );
	}

}
