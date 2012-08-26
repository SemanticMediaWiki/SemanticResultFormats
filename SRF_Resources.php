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
/* Common and non format specific resources
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
 * ListWidget
 ******************************************************************************/
$wgResourceModules['ext.jquery.listwidget'] = $formatModule + array(
	'scripts' => array(
		'listwidget/resources/jquery.listnav.min-2.1.js',
		'listwidget/resources/jquery.listmenu.min-1.1.js',
		'listwidget/resources/jquery.pajinate.js',
	),
);

$wgResourceModules['ext.srf.listwidget'] = $formatModule + array(
	'scripts' => 'listwidget/resources/ext.srf.listwidget.js',
	'styles'  => 'listwidget/resources/ext.srf.listwidget.css',
	'dependencies' => 'ext.jquery.listwidget',
	'position' => 'top',
	'messages' => array(
		'srf-module-nomatch'
	)
);

/******************************************************************************
 * jqPlot
 * @since 1.8
/******************************************************************************/
/*** jQuery plugin specific declarations ***/

$wgResourceModules['ext.jquery.jqplot.core'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jquery.jqplot.min.js',
	'styles' => 'resources/jquery.jqplot/jquery.jqplot.min.css'
);

// excanvas is required only for pre- IE 9 versions
$wgResourceModules['ext.jquery.jqplot.excanvas'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/excanvas.min.js'
);

// JSON data formatting according the the City Index API spec
$wgResourceModules['ext.jquery.jqplot.json'] = $moduleTemplate + array(
	'scripts' => array (
		'resources/jquery.jqplot/jqplot.json2.min.js',
		'resources/jquery.jqplot/jqplot.ciParser.min.js'
	)
);

// Plugin class representing the cursor
$wgResourceModules['ext.jquery.jqplot.cursor'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.cursor.min.js'
);

// Plugin class to render a logarithmic axis
$wgResourceModules['ext.jquery.jqplot.logaxisrenderer'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.logAxisRenderer.min.js'
);

// Plugin class to render a mekko style chart
$wgResourceModules['ext.jquery.jqplot.mekko'] = $moduleTemplate + array(
	'scripts' => array (
		'resources/jquery.jqplot/jqplot.mekkoRenderer.min.js',
		'resources/jquery.jqplot/jqplot.mekkoAxisRenderer.min.js'
	)
);

// Plugin class to render a bar/line style chart
$wgResourceModules['ext.jquery.jqplot.bar'] = $moduleTemplate + array(
	'scripts' => array(
		'resources/jquery.jqplot/jqplot.canvasAxisTickRenderer.min.js',
		'resources/jquery.jqplot/jqplot.canvasTextRenderer.min.js',
		'resources/jquery.jqplot/jqplot.canvasAxisLabelRenderer.min.js',
		'resources/jquery.jqplot/jqplot.categoryAxisRenderer.min.js',
		'resources/jquery.jqplot/jqplot.barRenderer.min.js'
	),
	'dependencies' => 'ext.jquery.jqplot.core',
);

// Plugin class to render a pie style chart
$wgResourceModules['ext.jquery.jqplot.pie'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.pieRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot.core'
);

// Plugin class to render a bubble style chart
$wgResourceModules['ext.jquery.jqplot.bubble'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.bubbleRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot.core'
);

// Plugin class to render a donut style chart
$wgResourceModules['ext.jquery.jqplot.donut'] = $moduleTemplate + array(
	'scripts' =>'resources/jquery.jqplot/jqplot.donutRenderer.min.js',
	'dependencies' => 'ext.jquery.jqplot.pie'
);

$wgResourceModules['ext.jquery.jqplot.pointlabels'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.pointLabels.min.js'
);

$wgResourceModules['ext.jquery.jqplot.highlighter'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.highlighter.min.js'
);

$wgResourceModules['ext.jquery.jqplot.enhancedlegend'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.enhancedLegendRenderer.min.js'
);

// Plugin class to render a trendline
$wgResourceModules['ext.jquery.jqplot.trendline'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.jqplot/jqplot.trendline.min.js'
);

/*** General jqplot/SRF specific declarations ***/

// Plugin class supporting themes
$wgResourceModules['ext.srf.jqplot.themes'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.themes.js',
	'styles'  => 'jqplot/resources/ext.srf.jqlpot.general.css',
	'dependencies' => 'jquery.client'
);

$wgResourceModules['ext.srf.jqplot.cursor'] = $moduleTemplate + array(
	'dependencies' => array (
		'ext.srf.jqplot.bar',
		'ext.jquery.jqplot.cursor',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.enhancedlegend'] = $moduleTemplate + array(
	'dependencies' => array (
		'ext.srf.jqplot.bar',
		'ext.jquery.jqplot.enhancedlegend',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.pointlabels'] = $moduleTemplate + array(
	'dependencies' => array (
		'ext.srf.jqplot.bar',
		'ext.jquery.jqplot.pointlabels',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.highlighter'] = $moduleTemplate + array(
	'dependencies' => array (
		'ext.srf.jqplot.bar',
		'ext.jquery.jqplot.highlighter',
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.trendline'] = $moduleTemplate + array(
	'dependencies' => array (
		'ext.srf.jqplot.bar',
		'ext.jquery.jqplot.trendline',
	),
	'position' => 'top',
);

/*** Chart type specific declarations ***/
$wgResourceModules['ext.srf.jqplot.chart'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.js',
	'dependencies' => 'ext.srf.jqplot.themes'
);

$wgResourceModules['ext.srf.jqplot.bar'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.bar.js',
	'dependencies' => array (
		'ext.jquery.jqplot.bar',
		'ext.srf.jqplot.chart'
	),
	'messages' => array (
		'srf-error-jqplot-stackseries-data-length'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.pie'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.pie.js',
	'dependencies' => array (
		'ext.jquery.jqplot.pie',
		'ext.srf.jqplot.chart'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.bubble'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.bubble.js',
	'dependencies' => array (
		'ext.jquery.jqplot.bubble',
		'ext.srf.jqplot.chart'
	),
	'messages' => array (
		'srf-error-jqplot-bubble-data-length'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.jqplot.donut'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.pie.js',
	'dependencies' => array (
		'ext.jquery.jqplot.donut',
		'ext.srf.jqplot.chart'
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
$wgResourceModules['ext.d3.core'] = $moduleTemplate + array(
	'scripts' => 'resources/d3/d3.v2.min.js'
);

$wgResourceModules['ext.d3.layout.cloud'] = $moduleTemplate + array(
	'scripts' => 'resources/d3/d3.layout.cloud.js',
	'dependencies' => 'ext.d3.core'
);

$wgResourceModules['ext.srf.d3.common'] = $formatModule + array(
	'scripts' => 'd3/resources/ext.srf.d3.common.js',
	'styles'  => 'd3/resources/ext.srf.d3.common.css'
);

$wgResourceModules['ext.srf.d3.chart.treemap'] = $formatModule + array(
	'scripts' => 'd3/resources/chart/ext.srf.d3.chart.treemap.js',
	'styles'  => 'd3/resources/chart/ext.srf.d3.chart.treemap.css',
	'dependencies' => array ( 'ext.d3.core', 'ext.srf.d3.common' ),
	'position'     => 'top',
);

$wgResourceModules['ext.srf.d3.chart.bubble'] = $formatModule + array(
	'scripts' => 'd3/resources/chart/ext.srf.d3.chart.bubble.js',
	'styles'  => 'd3/resources/chart/ext.srf.d3.chart.bubble.css',
	'dependencies' => array ( 'ext.d3.core', 'ext.srf.d3.common' ),
	'position'     => 'top',
);

$wgResourceModules['ext.srf.jqplot.chart.tableview'] = $formatModule + array(
	'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.tableview.js',
	'dependencies' => array(
		'ext.srf.jqplot.chart',
		'jquery.ui.core',
		'jquery.ui.tabs',
		'ext.jquery.jqgrid'
	),
	'messages' => array(
		'srf-chart-tableview-series',
		'srf-chart-tableview-item',
		'srf-chart-tableview-value',
		'srf-chart-tableview-chart-tab',
		'srf-chart-tableview-data-tab'
	),
	'position' => 'top',
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
		'srf-gallery-overlay-count',
		'srf-gallery-image-url-error'
	),
	'position' => 'top',
);

$wgResourceModules['ext.srf.gallery.redirect'] = $formatModule + array(
	'scripts' => 'gallery/resources/ext.srf.gallery.redirect.js',
	'styles'  => 'gallery/resources/ext.srf.gallery.redirect.css',
	'messages' => array(
		'srf-gallery-image-url-error'
	),
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
$wgResourceModules['ext.jquery.tagcanvas.excanvas'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.tagcanvas/excanvas.js'
);

$wgResourceModules['ext.jquery.tagcanvas'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.tagcanvas/jquery.tagcanvas.1.18.min.js'
);

$wgResourceModules['ext.srf.tagcloud.sphere'] = $formatModule + array(
	'scripts' => 'tagcloud/resources/ext.srf.tagcloud.sphere.js',
	'style'   => 'tagcloud/resources/ext.srf.tagcloud.sphere.css',
	'dependencies' => array( 'ext.jquery.tagcanvas', 'jquery.client' ),
	'position'     => 'top',
);

$wgResourceModules['ext.srf.tagcloud.wordcloud'] = $formatModule + array(
	'scripts' => 'tagcloud/resources/ext.srf.tagcloud.wordcloud.js',
	'dependencies' => array (
		'ext.d3.layout.cloud',
		'ext.srf.d3.common'
	),
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

/******************************************************************************
 * Eventcalendar
 ******************************************************************************/
$wgResourceModules['ext.jquery.fullcalendar'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.fullcalendar/fullcalendar.min.js',
	'style' => 'resources/jquery.fullcalendar/fullcalendar.css',
);

$wgResourceModules['ext.jquery.gcal'] = $moduleTemplate + array(
	'scripts' => 'resources/jquery.fullcalendar/gcal.js',
);

$wgResourceModules['ext.srf.eventcalendar'] = $formatModule + array(
	'scripts' => 'calendar/resources/ext.srf.eventcalendar.js',
	'dependencies' => array (
		'jquery.ui.core',
		'jquery.ui.widget',
		'jquery.tipsy',
		'ext.jquery.fullcalendar'
	)
);

$wgResourceModules['ext.srf.eventcalendar.gcal'] = $formatModule + array(
	'dependencies' => array (
		'ext.srf.eventcalendar',
		'ext.jquery.gcal'
	)
);

unset( $formatModule );
unset( $moduleTemplate );
