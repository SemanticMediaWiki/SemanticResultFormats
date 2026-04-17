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

return $cfg;
