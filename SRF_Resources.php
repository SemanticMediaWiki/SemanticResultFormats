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

/******************************************************************************/
/* jqPlot
/******************************************************************************/
// excanvas is required only for IE versions below 9 while IE 9 includes 
// native support
$wgResourceModules['ext.srf.jqplot.core'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/resources/jquery.jqplot.min.js',
		'jqPlot/resources/excanvas.min.js',
		'jqPlot/resources/jqplot.json2.min.js',		
		'jqPlot/resources/ext.srf.jqplot.themes.js',
	),
	'styles' => array(
		'jqPlot/resources/jquery.jqplot.css',
	),
);

$wgResourceModules['ext.srf.jqplot.bar'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/resources/jqplot.canvasAxisTickRenderer.min.js',
		'jqPlot/resources/jqplot.canvasTextRenderer.min.js',
		'jqPlot/resources/jqplot.canvasAxisLabelRenderer.min.js',
		'jqPlot/resources/jqplot.categoryAxisRenderer.min.js',
		'jqPlot/resources/jqplot.barRenderer.min.js',
		'jqPlot/resources/ext.srf.jqplot.bar.js'	
	),
	'dependencies' => 'ext.srf.jqplot.core',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bar.extended'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/resources/jqplot.pointLabels.min.js',
		'jqPlot/resources/jqplot.highlighter.min.js',	
	),
	'dependencies' => 'ext.srf.jqplot.bar',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bar.trendline'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/resources/jqplot.trendline.min.js',
	),
	'dependencies' => 'ext.srf.jqplot.bar',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.pie'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/resources/jqplot.pieRenderer.min.js',
		'jqPlot/resources/ext.srf.jqplot.pie.js',		
	),
	'dependencies' => 'ext.srf.jqplot.core',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bubble'] = $moduleTemplate + array(
	'scripts' => array(
		'jqPlot/resources/jqplot.bubbleRenderer.min.js',
		'jqPlot/resources/ext.srf.jqplot.bubble.js',		
	),
	'dependencies' 	=> 'ext.srf.jqplot.core', 
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.donut'] = $moduleTemplate + array(
	'scripts' =>'jqPlot/resources/jqplot.donutRenderer.min.js',
	'dependencies' => 'ext.srf.jqplot.pie',
	'position' => 'top',
);

/******************************************************************************/
/*  Timeline
/******************************************************************************/

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

$wgResourceModules['jquery.progressbar'] = $moduleTemplate + array(
	'scripts' => array(
		'JitGraph/jquery.progressbar.js',
	),
);

$wgResourceModules['ext.srf.jit'] = $moduleTemplate + array(
	'scripts' => array(
		'JitGraph/Jit/jit.js',
	),
);
		
$wgResourceModules['ext.srf.jitgraph'] = $moduleTemplate + array(
	'scripts' => array(
		'JitGraph/SRF_JitGraph.js',
	),
	'styles' => array(
		'JitGraph/base.css',
	),
	'dependencies' => array(
		'mediawiki.legacy.wikibits',
		'jquery.progressbar',
		'ext.srf.jit',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jcarousel'] = $moduleTemplate + array(
	'scripts' => array(
		'Gallery/resources/jquery.jcarousel.min.js',
		'Gallery/resources/ext.srf.jcarousel.js',
	),
	'styles' => array(
		'Gallery/resources/ext.srf.jcarousel.css',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.filtered'] = $moduleTemplate + array(
	'scripts' => array(
		'Filtered/libs/ext.srf.filtered.js',
	),
	'styles' => array(
		'Filtered/skins/ext.srf.filtered.css',
	),
);

$wgResourceModules['ext.srf.filtered.list-view'] = $moduleTemplate + array(
	'scripts' => array(
		'Filtered/libs/ext.srf.filtered.list-view.js',
	),
// TODO: Do we need a style file?
//	'styles' => array(
//		'Filtered/skins/ext.srf.filtered.css',
//	),
	'dependencies' => array(
		'ext.srf.filtered'
	),
);

$wgResourceModules['ext.srf.filtered.value-filter'] = $moduleTemplate + array(
	'scripts' => array(
		'Filtered/libs/ext.srf.filtered.value-filter.js',
	),
	'styles' => array(
		'Filtered/skins/ext.srf.filtered.value-filter.css',
	),
	'dependencies' => array(
		'ext.srf.filtered'
	),
);

$wgResourceModules['ext.srf.filtered.distance-filter'] = $moduleTemplate + array(
	'scripts' => array(
		'Filtered/libs/ext.srf.filtered.distance-filter.js',
	),
	'styles' => array(
		'Filtered/skins/ext.srf.filtered.distance-filter.css',
	),
	'dependencies' => array(
		'ext.srf.filtered',
		'jquery.ui.slider'
	),
);

unset( $moduleTemplate );
