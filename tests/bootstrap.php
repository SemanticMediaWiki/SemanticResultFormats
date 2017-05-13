<?php

if ( PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ) {
	die( 'Not an entry point' );
}

if ( !is_readable( $path = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The SemanticMediaWiki test autoloader is not available' );
}

print sprintf( "\n%-20s%s\n", "Semantic Result Formats: ", SRF_VERSION );

$autoloader = require $path;
$autoloader->addPsr4( 'SRF\\Tests\\', __DIR__ . '/phpunit' );
