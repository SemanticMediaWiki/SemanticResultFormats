<?php

if ( PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !defined( 'SMW_PHPUNIT_AUTOLOADER_FILE' ) || !is_readable( SMW_PHPUNIT_AUTOLOADER_FILE ) ) {
	die( "\nThe Semantic MediaWiki test autoloader is not available" );
}

$width = 25;

if ( !defined( 'SMW_PHPUNIT_FIRST_COLUMN_WIDTH' ) ) {
	define( 'SMW_PHPUNIT_FIRST_COLUMN_WIDTH', $width );
}

print sprintf( "\n%-{$width}s%s\n", "Semantic Result Formats: ", SRF_VERSION );

$autoLoader = require SMW_PHPUNIT_AUTOLOADER_FILE;
$autoloader->addPsr4( 'SRF\\Tests\\', __DIR__ . '/phpunit' );
$autoloader->addPsr4( 'SMW\\Test\\', __DIR__ . '/../../SemanticMediaWiki/tests/phpunit' );
$autoloader->addPsr4( 'SMW\\Tests\\', __DIR__ . '/../../SemanticMediaWiki/tests/phpunit' );

$autoloader->addClassMap( [
	'SRF\Tests\ResultPrinterReflector'             => __DIR__ . '/phpunit/ResultPrinterReflector.php',
] );
