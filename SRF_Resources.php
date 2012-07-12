<?php

/**
 * The resource module definitions for the Semantic Result Formats extension.
 *
 * @since 1.7
 *
 * @file SRF_Resources.php
 * @ingroup SemanticResultFormats
 *
 * @licence GNU GPL v2 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

$moduleTemplate = array(
	'localBasePath' => dirname( __FILE__ ) . '/',
	'remoteExtPath' => 'SemanticResultFormats/'
);
/******************************************************************************/
/* Common resources
/******************************************************************************/
$wgResourceModules['ext.jquery.easing'] = $moduleTemplate + array(
	'scripts' => 'resources/easing/jquery.easing-1.3.pack.js'
);

$wgResourceModules['ext.jquery.fancybox'] = $moduleTemplate + array(
	'scripts' => 'resources/fancybox/jquery.fancybox-1.3.4.pack.js',
	'styles'  => 'resources/fancybox/jquery.fancybox-1.3.4.css',
	'dependencies' => 'ext.jquery.easing',
);

/******************************************************************************
 * jqPlot
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

/******************************************************************************
 *  Timeline
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

/******************************************************************************
 * Gallery
/******************************************************************************/
$wgResourceModules['ext.jquery.jcarousel'] = $moduleTemplate + array(
	'scripts' => 'Gallery/resources/jquery.jcarousel.min.js',
);

$wgResourceModules['ext.jquery.responsiveslides'] = $moduleTemplate + array(
	'scripts' => 'Gallery/resources/jquery.responsiveslides.1.32.min.js',
);

$wgResourceModules['ext.srf.gallery.carousel'] = $moduleTemplate + array(
	'scripts' => 'Gallery/resources/ext.srf.jcarousel.js',
	'styles'  => 'Gallery/resources/ext.srf.jcarousel.css',
	'dependencies' => 'ext.jquery.jcarousel',
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.slideshow'] = $moduleTemplate + array(
	'scripts' => 'Gallery/resources/ext.srf.gallery.slideshow.js',
	'styles'  => 'Gallery/resources/ext.srf.gallery.slideshow.css',
	'dependencies' => 'ext.jquery.responsiveslides',
	'messages' => array(
		'srf-gallery-navigation-previous',
		'srf-gallery-navigation-next'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.fancybox'] = $moduleTemplate + array(
	'scripts' => 'Gallery/resources/ext.srf.gallery.fancybox.js',
	'styles'  => 'Gallery/resources/ext.srf.gallery.fancybox.css',
	'dependencies' => 'ext.jquery.fancybox',
	'messages' => array(
		'srf-gallery-overlay-count'
	),
	'position' => 'top',
);

/******************************************************************************
 * Filtered
/******************************************************************************/
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

/******************************************************************************
 * Slideshow
 ******************************************************************************/
$wgResourceModules['ext.srf.slideshow'] = $moduleTemplate + array(
	'scripts' => array(
		'SlideShow/ext.srf.slideshow.js',
	),
	'styles' => array(
		'SlideShow/ext.srf.slideshow.css',
	),
	'dependencies' => array(
		'mediawiki.legacy.ajax',
	),
);

/******************************************************************************
 * Tag cloud
 ******************************************************************************/
// excanvas is only needed for pre-9.0 Internet Explorer compatibility
$wgResourceModules['ext.jquery.tagcanvas.excanvas'] = $moduleTemplate + array(
	'scripts' => 'TagCloud/resources/excanvas.js'
);

$wgResourceModules['ext.jquery.tagcanvas'] = $moduleTemplate + array(
	'scripts' => 'TagCloud/resources/jquery.tagcanvas.1.18.min.js'
);

$wgResourceModules['ext.srf.tagcloud.canvas'] = $moduleTemplate + array(
	'scripts' => 'TagCloud/resources/ext.srf.tagcloud.canvas.js',
	'style'   => 'TagCloud/resources/ext.srf.tagcloud.canvas.css',
	'dependencies' => array( 'ext.jquery.tagcanvas', 'jquery.client' ),
	'position'     => 'top',
);
unset( $moduleTemplate );
