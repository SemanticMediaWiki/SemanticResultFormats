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

$formatModule = array(
	'localBasePath' => dirname( __FILE__ ) . '/formats/',
	'remoteExtPath' => 'SemanticResultFormats/formats/'
);

/******************************************************************************/
/* Common resources
/******************************************************************************/
$wgResourceModules['ext.jquery.easing'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.easing/jquery.easing-1.3.pack.js'
);

$wgResourceModules['ext.jquery.fancybox'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.fancybox/jquery.fancybox-1.3.4.pack.js',
	'styles'  => 'resources/jquery.fancybox/jquery.fancybox-1.3.4.css',
	'dependencies' => 'ext.jquery.easing',
);

$wgResourceModules['ext.jquery.jqgrid'] = $moduleTemplate + array(
	'scripts' => array(
		'resources/jquery.jqgrid/jquery.jqGrid.4.4.0min.js',
		'resources/jquery.jqgrid/grid.locale-en.js' 
	),
	'styles' => 'resources/jquery.jqgrid/ui.jqgrid.css',
	'dependencies' => 'jquery.ui.core'
);

$wgResourceModules['ext.jquery.flot'] = $moduleTemplate + array(
	'scripts' => array( 
		'resources/jquery.flot/jquery.flot.js',
		'resources/jquery.flot/jquery.flot.selection.js'
	)
);

$wgResourceModules['ext.jquery.sparkline'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.sparkline/jquery.sparkline.min.js'
);

/******************************************************************************
 * Sparkline
/******************************************************************************/
$wgResourceModules['ext.srf.sparkline'] = $formatModule + array(
	'scripts' => 'sparkline/resources/ext.srf.sparkline.js',
	'dependencies' => 'ext.jquery.sparkline',
	'position' => 'top',
);

/******************************************************************************
 * jqPlot
/******************************************************************************/
$wgResourceModules['ext.jquery.jqplot'] = $formatModule + array(
	'scripts' => 'jqplot/resources/jquery.jqplot.min.js',
	'styles' => 'jqplot/resources/jquery.jqplot.css'
);

// excanvas is required only for pre- IE 9 versions
$wgResourceModules['ext.jquery.jqplot.excanvas'] = $formatModule + array(
	'scripts' => 'jqplot/resources/excanvas.min.js'
);

$wgResourceModules['ext.jquery.jqplot.json'] = $formatModule + array(
	'scripts' => 'jqplot/resources/jqplot.json2.min.js'
);

$wgResourceModules['ext.jquery.jqplot.bar'] = $formatModule + array(
	'scripts' => array(
		'jqplot/resources/jqplot.canvasAxisTickRenderer.min.js',
		'jqplot/resources/jqplot.canvasTextRenderer.min.js',
		'jqplot/resources/jqplot.canvasAxisLabelRenderer.min.js',
		'jqplot/resources/jqplot.categoryAxisRenderer.min.js',
		'jqplot/resources/jqplot.barRenderer.min.js',
	),
	'dependencies' => 'ext.jquery.jqplot',
);

$wgResourceModules['ext.jquery.jqplot.pie'] = $formatModule + array(
	'scripts' => 'jqplot/resources/jqplot.pieRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot'
);

$wgResourceModules['ext.jquery.jqplot.bubble'] = $formatModule + array(
	'scripts' => 'jqplot/resources/jqplot.bubbleRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot'
);

$wgResourceModules['ext.jquery.jqplot.donut'] = $formatModule + array(
	'scripts' =>'jqplot/resources/jqplot.donutRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot.pie'
);

$wgResourceModules['ext.srf.jqplot.themes'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.themes.js',
	'dependencies' =>	'jquery.client'
);

$wgResourceModules['ext.srf.jqplot.bar'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.bar.js',
	'dependencies' => array (
		'ext.jquery.jqplot.bar',
		'ext.srf.jqplot.themes',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bar.extended'] = $formatModule + array(
	'scripts' => array(
		'jqplot/resources/jqplot.pointLabels.min.js',
		'jqplot/resources/jqplot.highlighter.min.js',
	),
	'dependencies' => 'ext.srf.jqplot.bar',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bar.trendline'] = $formatModule + array(
	'scripts' => 'jqplot/resources/jqplot.trendline.min.js',
	'dependencies' => 'ext.srf.jqplot.bar',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.pie'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.pie.js',
	'dependencies' => array (
		'ext.jquery.jqplot.pie',
		'ext.srf.jqplot.themes'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bubble'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.bubble.js',
	'dependencies' => array (
		'ext.jquery.jqplot.bubble',
		'ext.srf.jqplot.themes'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.donut'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.pie.js',
	'dependencies' => array (
		'ext.jquery.jqplot.donut',
		'ext.srf.jqplot.themes'
	),
	'position' => 'top',
);

/******************************************************************************
 * Timeline
/******************************************************************************/
$wgResourceModules['ext.smile.timeline'] = $formatModule + array(
	'scripts' => 'timeline/resources/SimileTimeline/timeline-api.js'
);

$wgResourceModules['ext.srf.timeline'] = $formatModule + array(
	'scripts' => 'timeline/resources/ext.srf.timeline.js',
	'dependencies' => array(
		'ext.smile.timeline',
		'mediawiki.legacy.wikibits'
	)
);

/******************************************************************************
 * D3
 ******************************************************************************/
$wgResourceModules['ext.srf.d3.core'] = $formatModule + array(
	'scripts' => 'd3/resources/d3.v2.min.js'
);

$wgResourceModules['ext.srf.d3.common'] = $formatModule + array(
	'scripts' => 'd3/resources/ext.srf.d3.common.js',
	'styles'  => 'd3/resources/ext.srf.d3.common.css'
);

$wgResourceModules['ext.srf.d3.chart.treemap'] = $formatModule + array(
	'scripts' => 'D3/resources/chart/ext.srf.d3.chart.treemap.js',
	'styles'  => 'D3/resources/chart/ext.srf.d3.chart.treemap.css',
	'dependencies' => array ( 'ext.srf.d3.core', 'ext.srf.d3.common' ),
	'position'     => 'top',
);

$wgResourceModules['ext.srf.d3.chart.bubble'] = $formatModule + array(
	'scripts' => 'd3/resources/chart/ext.srf.d3.chart.bubble.js',
	'styles'  => 'd3/resources/chart/ext.srf.d3.chart.bubble.css',
	'dependencies' => array ( 'ext.srf.d3.core', 'ext.srf.d3.common' ),
	'position'     => 'top',
);

/******************************************************************************
 * JitGraph
 ******************************************************************************/
$wgResourceModules['jquery.progressbar'] = $formatModule + array(
	'scripts' => array(
		'JitGraph/jquery.progressbar.js',
	),
);

$wgResourceModules['ext.srf.jit'] = $formatModule + array(
	'scripts' => array(
		'JitGraph/Jit/jit.js',
	),
);
		
$wgResourceModules['ext.srf.jitgraph'] = $formatModule + array(
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
$wgResourceModules['ext.jquery.jcarousel'] = $formatModule + array(
	'scripts' => 'gallery/resources/jquery.jcarousel.min.js',
);

$wgResourceModules['ext.jquery.responsiveslides'] = $formatModule + array(
	'scripts' => 'gallery/resources/jquery.responsiveslides.1.32.min.js',
);

$wgResourceModules['ext.srf.gallery.carousel'] = $formatModule + array(
	'styles'  => 'gallery/resources/ext.srf.gallery.carousel.css',
	'scripts' => 'gallery/resources/ext.srf.gallery.carousel.js',
	'dependencies' => 'ext.jquery.jcarousel',
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.slideshow'] = $formatModule + array(
	'scripts' => 'gallery/resources/ext.srf.gallery.slideshow.js',
	'styles'  => 'gallery/resources/ext.srf.gallery.slideshow.css',
	'dependencies' => 'ext.jquery.responsiveslides',
	'messages' => array(
		'srf-gallery-navigation-previous',
		'srf-gallery-navigation-next'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.overlay'] = $formatModule + array(
	'scripts' => 'gallery/resources/ext.srf.gallery.overlay.js',
	'styles'  => 'gallery/resources/ext.srf.gallery.overlay.css',
	'dependencies' => 'ext.jquery.fancybox',
	'messages' => array(
		'srf-gallery-overlay-count'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.redirect'] = $formatModule + array(
	'scripts' => 'gallery/resources/ext.srf.gallery.redirect.js',
	'styles'  => 'gallery/resources/ext.srf.gallery.redirect.css',
	'position' => 'top',
);

/******************************************************************************
 * Filtered
/******************************************************************************/
$wgResourceModules['ext.srf.filtered'] = $formatModule + array(
	'scripts' => array(
		'Filtered/libs/ext.srf.filtered.js',
	),
	'styles' => array(
		'Filtered/skins/ext.srf.filtered.css',
	),
);

$wgResourceModules['ext.srf.filtered.list-view'] = $formatModule + array(
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

$wgResourceModules['ext.srf.filtered.value-filter'] = $formatModule + array(
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

$wgResourceModules['ext.srf.filtered.distance-filter'] = $formatModule + array(
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
$wgResourceModules['ext.srf.slideshow'] = $formatModule + array(
	'scripts' => 'slideshow/resources/ext.srf.slideshow.js',
	'styles'  => 'slideshow/resources/ext.srf.slideshow.css',
	'dependencies' =>'mediawiki.legacy.ajax'
);

/******************************************************************************
 * Tag cloud
 ******************************************************************************/
// excanvas is only needed for pre-9.0 Internet Explorer compatibility
$wgResourceModules['ext.jquery.tagcanvas.excanvas'] = $formatModule + array(
	'scripts' => 'tagcloud/resources/excanvas.js'
);

$wgResourceModules['ext.jquery.tagcanvas'] = $formatModule + array(
	'scripts' => 'tagcloud/resources/jquery.tagcanvas.1.18.min.js'
);

$wgResourceModules['ext.srf.tagcloud.sphere'] = $formatModule + array(
	'scripts' => 'tagcloud/resources/ext.srf.tagcloud.sphere.js',
	'style'   => 'tagcloud/resources/ext.srf.tagcloud.sphere.css',
	'dependencies' => array( 'ext.jquery.tagcanvas', 'jquery.client' ),
	'position'     => 'top',
);

/******************************************************************************
 * Flot
 ******************************************************************************/
$wgResourceModules['ext.srf.flot.core'] = $formatModule + array(
	'styles'  => 'flot/resources/ext.srf.flot.core.css',
);

$wgResourceModules['ext.srf.flot.timeseries'] = $formatModule + array(
	'scripts' => 'flot/resources/ext.srf.flot.timeseries.js',
	'dependencies' => array ( 'ext.jquery.flot', 'ext.jquery.jqgrid', 'ext.srf.flot.core' ),
	'messages' => array(
		'srf-timeseries-zoom-out-of-range'
	),
	'position' => 'top'
);

unset( $formatModule );
unset( $moduleTemplate );
