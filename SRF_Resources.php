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
	'scripts' => 'resources/jquery.easing/jquery.easing-1.3.pack.js'
);

$wgResourceModules['ext.jquery.fancybox'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.fancybox/jquery.fancybox-1.3.4.pack.js',
	'styles'  => 'resources/jquery.fancybox/jquery.fancybox-1.3.4.css',
	'dependencies' => 'ext.jquery.easing',
);

/******************************************************************************
 * jqPlot
/******************************************************************************/
$wgResourceModules['ext.jquery.jqplot'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/jquery.jqplot.min.js',
	'styles' => 'jqplot/resources/jquery.jqplot.css'
);

// excanvas is required only for pre- IE 9 versions
$wgResourceModules['ext.jquery.jqplot.excanvas'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/excanvas.min.js'
);

$wgResourceModules['ext.jquery.jqplot.json'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/jqplot.json2.min.js'
);

$wgResourceModules['ext.jquery.jqplot.bar'] = $moduleTemplate + array(
	'scripts' => array(
		'jqplot/resources/jqplot.canvasAxisTickRenderer.min.js',
		'jqplot/resources/jqplot.canvasTextRenderer.min.js',
		'jqplot/resources/jqplot.canvasAxisLabelRenderer.min.js',
		'jqplot/resources/jqplot.categoryAxisRenderer.min.js',
		'jqplot/resources/jqplot.barRenderer.min.js',
	),
	'dependencies' => 'ext.jquery.jqplot',
);

$wgResourceModules['ext.jquery.jqplot.pie'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/jqplot.pieRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot'
);

$wgResourceModules['ext.jquery.jqplot.bubble'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/jqplot.bubbleRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot'
);

$wgResourceModules['ext.jquery.jqplot.donut'] = $moduleTemplate + array(
	'scripts' =>'jqplot/resources/jqplot.donutRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot.pie'
);

$wgResourceModules['ext.srf.jqplot.themes'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.themes.js',
	'dependencies' =>	'jquery.client'
);

$wgResourceModules['ext.srf.jqplot.bar'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.bar.js',
	'dependencies' => array (
		'ext.jquery.jqplot.bar',
		'ext.srf.jqplot.themes',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bar.extended'] = $moduleTemplate + array(
	'scripts' => array(
		'jqplot/resources/jqplot.pointLabels.min.js',
		'jqplot/resources/jqplot.highlighter.min.js',
	),
	'dependencies' => 'ext.srf.jqplot.bar',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bar.trendline'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/jqplot.trendline.min.js',
	'dependencies' => 'ext.srf.jqplot.bar',
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.pie'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.pie.js',
	'dependencies' => array (
		'ext.jquery.jqplot.pie',
		'ext.srf.jqplot.themes'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bubble'] = $moduleTemplate + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.bubble.js',
	'dependencies' => array (
		'ext.jquery.jqplot.bubble',
		'ext.srf.jqplot.themes'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.donut'] = $moduleTemplate + array(
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
$wgResourceModules['ext.smile.timeline'] = $moduleTemplate + array(
	'scripts' => 'timeline/resources/SimileTimeline/timeline-api.js'
);

$wgResourceModules['ext.srf.timeline'] = $moduleTemplate + array(
	'scripts' => 'timeline/resources/ext.srf.timeline.js',
	'dependencies' => array(
		'ext.smile.timeline',
		'mediawiki.legacy.wikibits'
	)
);

/******************************************************************************
 * D3
/******************************************************************************/
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
	'scripts' => 'gallery/resources/jquery.jcarousel.min.js',
);

$wgResourceModules['ext.jquery.responsiveslides'] = $moduleTemplate + array(
	'scripts' => 'gallery/resources/jquery.responsiveslides.1.32.min.js',
);

$wgResourceModules['ext.srf.gallery.carousel'] = $moduleTemplate + array(
	'styles'  => 'gallery/resources/ext.srf.gallery.carousel.css',
	'scripts' => 'gallery/resources/ext.srf.gallery.carousel.js',
	'dependencies' => 'ext.jquery.jcarousel',
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.slideshow'] = $moduleTemplate + array(
	'scripts' => 'gallery/resources/ext.srf.gallery.slideshow.js',
	'styles'  => 'gallery/resources/ext.srf.gallery.slideshow.css',
	'dependencies' => 'ext.jquery.responsiveslides',
	'messages' => array(
		'srf-gallery-navigation-previous',
		'srf-gallery-navigation-next'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.overlay'] = $moduleTemplate + array(
	'scripts' => 'gallery/resources/ext.srf.gallery.overlay.js',
	'styles'  => 'gallery/resources/ext.srf.gallery.overlay.css',
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
	'scripts' => 'slideshow/resources/ext.srf.slideshow.js',
	'styles'  => 'slideshow/resources/ext.srf.slideshow.css',
	'dependencies' =>'mediawiki.legacy.ajax'
);

/******************************************************************************
 * Tag cloud
 ******************************************************************************/
// excanvas is only needed for pre-9.0 Internet Explorer compatibility
$wgResourceModules['ext.jquery.tagcanvas.excanvas'] = $moduleTemplate + array(
	'scripts' => 'tagcloud/resources/excanvas.js'
);

$wgResourceModules['ext.jquery.tagcanvas'] = $moduleTemplate + array(
	'scripts' => 'tagcloud/resources/jquery.tagcanvas.1.18.min.js'
);

$wgResourceModules['ext.srf.tagcloud.sphere'] = $moduleTemplate + array(
	'scripts' => 'tagcloud/resources/ext.srf.tagcloud.sphere.js',
	'style'   => 'tagcloud/resources/ext.srf.tagcloud.sphere.css',
	'dependencies' => array( 'ext.jquery.tagcanvas', 'jquery.client' ),
	'position'     => 'top',
);
unset( $moduleTemplate );
