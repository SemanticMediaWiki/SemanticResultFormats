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
		$outputPage->setProperty( 'srf-filtered-config', $parserOutput->getExtensionData( 'srf-filtered-config' ) );
		return true;
	}

	public static function onMakeGlobalVariablesScript( &$vars, OutputPage $output ) {
		$vars['srfFilteredConfig'] = $output->getProperty( 'srf-filtered-config' );
		return true;
	}

}