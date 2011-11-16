<?php

/**
 * The resource module definitions for the Semantic Result Formats extension.
 *
 * @since 1.7
 *
 * @file SRF_Resources.php
 * @ingroup SemanticResultFormats
 *
 * @licence GNU GPL v3
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

$moduleTemplate = array(
	'localBasePath' => dirname( __FILE__ ) . '/',
	'remoteExtPath' => 'SemanticResultFormats/'
);

$wgResourceModules['ext.srf.jqplot'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/jquery.jqplot.js',
	),
	'styles' => array(
		'jqPlot/jquery.jqplot.css',
	),
);

$wgResourceModules['ext.srf.jqplotbar'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/jqplot.categoryAxisRenderer.js',
		'jqPlot/jqplot.barRenderer.js',
		'jqPlot/jqplot.canvasAxisTickRenderer.js',
		'jqPlot/jqplot.canvasTextRenderer.js',
		'jqPlot/excanvas.js',
	),
	'dependencies' => array(
		'ext.srf.jqplot',
	),
);

$wgResourceModules['ext.srf.jqplotpointlabels'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/jqplot.pointLabels.min.js',
	),
	'dependencies' => array(
		'ext.srf.jqplotbar',
	),
);

$wgResourceModules['ext.srf.jqplotpie'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/jqplot.pieRenderer.js',
		'jqPlot/excanvas.js',
	),
	'dependencies' => array(
		'ext.srf.jqplot',
	),
);

$wgResourceModules['ext.srf.timeline'] = $moduleTemplate + array(
	'scripts' => array(
		'Timeline/SRF_timeline.js',
		'Timeline/SimileTimeline/timeline-api.js'
	),
	'dependencies' => array(
		'mediawiki.legacy.wikibits'
	)
);

$wgResourceModules['ext.srf.d3core'] = $moduleTemplate + array(
	'scripts' => array(
		'D3/d3.js',
	),
	'styles' => array(
		'D3/d3.css',
	),
);

$wgResourceModules['ext.srf.d3treemap'] = $moduleTemplate + array(
	'scripts' => array(
		'D3/d3.layout.min.js',
	),
	'dependencies' => array(
		'ext.srf.d3core',
	),
);	

unset( $moduleTemplate );
