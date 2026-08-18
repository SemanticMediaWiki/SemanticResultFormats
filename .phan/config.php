<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['baseline_path'] = __DIR__ . '/baseline.php';

// Analyse extension source code; vendor + node_modules are excluded by default
$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'formats',
		'src',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'vendor/',
	]
);

// Make optional dependency extensions visible to Phan's type-checker: those
// activated in the Makefile / ci.yml matrix. mediawiki-phan-config only adds
// MW core and MW vendor to directory_list by default, never extensions/.
// Without this, calls into an optional extension's classes either surface as
// PhanUndeclaredClass* noise, or (for unqualified class references resolved
// relative to the current namespace) are silently skipped by Phan instead of
// being checked against the dependency's actual API.
$IP = getenv( 'MW_INSTALL_PATH' ) !== false
	? str_replace( '\\', '/', getenv( 'MW_INSTALL_PATH' ) )
	: '../..';

$dependencyExtensions = [
	'Arrays',
];

foreach ( $dependencyExtensions as $ext ) {
	$cfg['directory_list'][] = $IP . '/extensions/' . $ext;
	$cfg['exclude_analysis_directory_list'][] = $IP . '/extensions/' . $ext;
}

return $cfg;
