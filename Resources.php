<?php

/**
 * The resource module definitions for the Semantic Result Formats extension.
 *
 * @since 1.7
 *
 * @licence GNU GPL v2 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author mwjames
 */

$moduleTemplate = [
	'localBasePath' => __DIR__ ,
	'remoteExtPath' => 'SemanticResultFormats'
];

$formatModule = [
	'localBasePath' => __DIR__ . '/formats',
	'remoteExtPath' => 'SemanticResultFormats/formats'
];

$calendarMessages = [ 'messages' => [
		'january', 'february', 'march', 'april', 'may_long', 'june', 'july', 'august',
		'september', 'october', 'november', 'december',
		'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
		'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday',
		'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat',
		'srf-ui-eventcalendar-label-today', 'srf-ui-eventcalendar-label-month',
		'srf-ui-eventcalendar-label-week', 'srf-ui-eventcalendar-label-day',
		'srf-ui-eventcalendar-label-listmonth', 'srf-ui-eventcalendar-label-listweek',
		'srf-ui-eventcalendar-label-listday',
		'srf-ui-eventcalendar-label-allday', 'srf-ui-eventcalendar-format-time',
		'srf-ui-eventcalendar-format-time-agenda', 'srf-ui-eventcalendar-format-axis',
		'srf-ui-eventcalendar-format-title-month', 'srf-ui-eventcalendar-format-title-week',
		'srf-ui-eventcalendar-format-title-day', 'srf-ui-eventcalendar-format-column-month',
		'srf-ui-eventcalendar-format-column-week', 'srf-ui-eventcalendar-format-column-day',
		'srf-ui-tooltip-title-legend', 'srf-ui-tooltip-title-filter',
		'srf-ui-common-label-refresh', 'srf-ui-eventcalendar-label-update-success',
		'srf-ui-eventcalendar-label-update-error', 'srf-ui-common-label-parameters',
		'srf-ui-common-label-paneview', 'srf-ui-common-label-daterange',
		'srf-ui-eventcalendar-click-popup',
	]
];

return [
	//SRF common and non printer specific resources
	'ext.jquery.easing' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.easing.js'
	],

	// Fancybox
	'ext.jquery.fancybox' => $moduleTemplate + [
		'scripts' => 'resources/jquery/fancybox/jquery.fancybox-1.3.4.pack.js',
		'styles'  => 'resources/jquery/fancybox/jquery.fancybox-1.3.4.css',
		'dependencies' => [
			'ext.jquery.easing',
			'ext.jquery.migration.browser'
		]
	],

	// Multiselect
	'ext.jquery.multiselect' => $moduleTemplate + [
		'scripts' => 'resources/jquery/multiselect/jquery.multiselect.js',
		'styles'  => 'resources/jquery/multiselect/jquery.multiselect.css',
		'dependencies' => [
			'jquery.ui.core',
			'jquery.ui.widget'
		]
	],

	// Multiselect filter
	'ext.jquery.multiselect.filter' => $moduleTemplate + [
		'scripts' => 'resources/jquery/multiselect/jquery.multiselect.filter.js',
		'styles'  => 'resources/jquery/multiselect/jquery.multiselect.filter.css',
		'dependencies' => 'ext.jquery.multiselect'
	],

	// blockUI plugin
	'ext.jquery.blockUI' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.blockUI.js'
	],

	// jqgrid
	'ext.jquery.jqgrid' => $moduleTemplate + [
		'scripts' => [
			'resources/jquery/jqgrid/jquery.jqGrid.js',
			'resources/jquery/jqgrid/grid.locale-en.js'
		],
		'styles' => 'resources/jquery/jqgrid/ui.jqgrid.css',
		'dependencies' => [
			'jquery.ui.core',
			'ext.jquery.migration.browser'
		]
	],

	// Flot
	'ext.jquery.flot' => $moduleTemplate + [
		'scripts' => [
			'resources/jquery/flot/jquery.flot.js',
			'resources/jquery/flot/jquery.flot.selection.js'
		]
	],

	// Provide migration tool to avoid jqplot to fail with
	// $.browser is undefined jquery.jqplot.js:398 caused
	// by jQuery 1.9+
	// @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/issues/356
	'ext.jquery.migration.browser' => $moduleTemplate + [
		'scripts' => [
			'resources/jquery/ext.jquery.migration.browser.js'
		]
	],

	// SRF specific printer independent utility resources
	'ext.srf' => $moduleTemplate + [
		'scripts' => 'resources/ext.srf.js',
		'styles'  => [
			'resources/ext.srf.css',

			// Someone broke the CSS loading (Suspect bug 46401) in 1.22
			// until this is fixed force styles to be loaded at the very start
			// to avoid display clutter
			'formats/calendar/resources/ext.srf.formats.eventcalendar.css',
		],
		'dependencies' => 'ext.smw.api',
		'position' => 'top',
		'group' => 'ext.srf'
	],

	'ext.srf.styles' => $moduleTemplate + [
		'styles'  => [
			'resources/ext.srf.css',
		],
		'position' => 'top',
		'group' => 'ext.srf'
	],

	// SMW/SRF query/result api module
	'ext.srf.api' => $moduleTemplate + [
		'scripts' => [
			'resources/ext.srf.api.results.js',
			'resources/ext.srf.api.query.js',
		],
		'position' => 'top',
		'dependencies' => 'ext.srf',
		'group' => 'ext.srf'
	],

	// Collects utility methods that are shared among different printers
	'ext.srf.util' => $moduleTemplate + [
		'scripts' => [
			'resources/ext.srf.util.js',
			'resources/ext.srf.util.html.js',
		],
		'dependencies' =>  [
			'ext.srf',
			'ext.jquery.jStorage',
			'ext.jquery.blockUI',
			'jquery.client',
			'mediawiki.Title',
		],
		'group' => 'ext.srf'
	],

	// SRF widgets
	'ext.srf.widgets' => $moduleTemplate + [
		'scripts' => [
			'resources/widgets/ext.srf.widgets.panel.js',
			'resources/widgets/ext.srf.widgets.parameters.js',
			'resources/widgets/ext.srf.widgets.optionslist.js'
		],
		'dependencies' => [
			'ext.srf',
			'jquery.ui.core',
			'jquery.ui.widget',
			'jquery.ui.button',
			'jquery.ui.slider',
			'ext.jquery.multiselect'
		],
		'messages' => [
			'srf-ui-widgets-label-parameter-limit',
		],
		'group' => 'ext.srf'
	],

	'ext.srf.util.grid' => $moduleTemplate + [
		'scripts' => 'resources/ext.srf.util.grid.js',
		'styles'  => 'resources/ext.srf.util.grid.css',
		'dependencies' => [
			'jquery.ui.tabs',
			'ext.srf.util',
			'ext.jquery.jqgrid',
		],
		'messages' => [
			'ask',
			'srf-ui-gridview-label-series',
			'srf-ui-gridview-label-item',
			'srf-ui-gridview-label-value',
			'srf-ui-gridview-label-chart-tab',
			'srf-ui-gridview-label-data-tab',
			'srf-ui-gridview-label-info-tab'
		],
		'position' => 'top',
		'group' => 'ext.srf'
	],

	// Sparkline
	'ext.jquery.sparkline' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.sparkline.js',
		'dependencies' => [
			'ext.jquery.migration.browser'
		]
	],

	'ext.srf.sparkline' => $formatModule + [
		'scripts' => 'sparkline/resources/ext.srf.sparkline.js',
		'dependencies' => [
			'ext.srf.util',
			'ext.jquery.sparkline'
		],
		'group' => 'ext.srf',
		'position' => 'top',
	],

	// Dygraphs
	'ext.dygraphs.combined' => $moduleTemplate + [
		'scripts' => 'resources/jquery/dygraphs/dygraph-combined.js'
	],
	'ext.srf.dygraphs' => $formatModule + [
		'scripts' => [
			'dygraphs/resources/ext.srf.dygraphs.js',
			'../resources/jquery/dygraphs/dygraph-combined.js'
		],
		'styles' => 'dygraphs/resources/ext.srf.dygraphs.css',
		'dependencies' => [
			'jquery.client',
			'jquery.async',
			'ext.srf.util',
			'ext.smw.tooltip',
			'ext.dygraphs.combined',
		],
		'messages' =>  [
			'srf-ui-common-label-datasource',
			'srf-ui-common-label-request-object',
			'srf-ui-common-label-ajax-error',
			'srf-ui-common-label-help-section',
			'srf-ui-tooltip-title-scope'
		],
		'position' => 'top',
	],

	// Listnav
	'ext.jquery.listnav' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.listnav.js'
	],

	// Listmenu
	'ext.jquery.listmenu' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.listmenu.js'
	],

	// pajinate
	'ext.jquery.pajinate' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.pajinate.js'
	],

	// Listwidget
	'ext.srf.listwidget' => $formatModule + [
		'scripts' => 'widget/resources/ext.srf.listwidget.js',
		'styles'  => 'widget/resources/ext.srf.listwidget.css',
		'dependencies' => 'ext.srf.util',
		'messages' => [
			'srf-module-nomatch'
		]
	],
	'ext.srf.listwidget.alphabet' => $formatModule + [
		'dependencies' =>  [
			'ext.srf.listwidget',
			'ext.jquery.listnav'
		],
		'position' => 'top'
	],
	'ext.srf.listwidget.menu' => $formatModule + [
		'dependencies' =>  [
			'ext.srf.listwidget',
			'ext.jquery.listmenu'
		],
		'position' => 'top'
	],
	'ext.srf.listwidget.pagination' => $formatModule + [
		'dependencies' =>  [
			'ext.srf.listwidget',
			'ext.jquery.pajinate'
		],
		'position' => 'top'
	],

	// Dynamiccarousel
	'ext.jquery.dynamiccarousel' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.dynamiccarousel.js',
		'dependencies' => [
			'ext.jquery.migration.browser'
		]
	],

	// Pagewidget
	'ext.srf.pagewidget.carousel' => $formatModule + [
		'scripts' => 'widget/resources/ext.srf.pagewidget.carousel.js',
		'styles' => 'widget/resources/ext.srf.pagewidget.carousel.css',
		'dependencies' => [
			'ext.jquery.dynamiccarousel',
			'ext.srf.util'
		],
		'messages' => [
			'srf-ui-navigation-prev',
			'srf-ui-navigation-next',
			'srf-ui-common-label-source',
		],
		'position' => 'top',
	],

	// jqPlot
	// jQuery plugin specific declarations
	'ext.jquery.jqplot.core' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jquery.jqplot.js',
		'styles' => 'resources/jquery/jqplot/jquery.jqplot.css',
		'dependencies' => [
			'ext.jquery.migration.browser'
		]
	],

	// excanvas is required only for pre- IE 9 versions
	'ext.jquery.jqplot.excanvas' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/excanvas.js'
	],

	// JSON data formatting according the the City Index API spec
	'ext.jquery.jqplot.json' => $moduleTemplate + [
		'scripts' =>  [
			'resources/jquery/jqplot/jqplot.json2.js',
			'resources/jquery/jqplot/jqplot.ciParser.js'
		]
	],

	// Plugin class representing the cursor
	'ext.jquery.jqplot.cursor' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.cursor.js'
	],

	// Plugin class to render a logarithmic axis
	'ext.jquery.jqplot.logaxisrenderer' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.logAxisRenderer.js'
	],

	// Plugin class to render a mekko style chart
	'ext.jquery.jqplot.mekko' => $moduleTemplate + [
		'scripts' =>  [
			'resources/jquery/jqplot/jqplot.mekkoRenderer.js',
			'resources/jquery/jqplot/jqplot.mekkoAxisRenderer.js'
		]
	],

	// Plugin class to render a bar/line style chart
	'ext.jquery.jqplot.bar' => $moduleTemplate + [
		'scripts' => [
			'resources/jquery/jqplot/jqplot.canvasAxisTickRenderer.js',
			'resources/jquery/jqplot/jqplot.canvasTextRenderer.js',
			'resources/jquery/jqplot/jqplot.canvasAxisLabelRenderer.js',
			'resources/jquery/jqplot/jqplot.categoryAxisRenderer.js',
			'resources/jquery/jqplot/jqplot.barRenderer.js'
		],
		'dependencies' => 'ext.jquery.jqplot.core',
	],

	// Plugin class to render a pie style chart
	'ext.jquery.jqplot.pie' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.pieRenderer.js',
		'dependencies' => 'ext.jquery.jqplot.core'
	],

	// Plugin class to render a bubble style chart
	'ext.jquery.jqplot.bubble' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.bubbleRenderer.js',
		'dependencies' => 'ext.jquery.jqplot.core'
	],

	// Plugin class to render a donut style chart
	 'ext.jquery.jqplot.donut' => $moduleTemplate + [
		'scripts' =>'resources/jquery/jqplot/jqplot.donutRenderer.js',
		'dependencies' => 'ext.jquery.jqplot.pie'
	],

	'ext.jquery.jqplot.pointlabels' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.pointLabels.js',
		'dependencies' => 'ext.jquery.jqplot.core'
	],

	'ext.jquery.jqplot.highlighter' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.highlighter.js',
		'dependencies' => 'ext.jquery.jqplot.core'
	],

	'ext.jquery.jqplot.enhancedlegend' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.enhancedLegendRenderer.js',
		'dependencies' => 'ext.jquery.jqplot.core'
	],

	// Plugin class to render a trendline
	'ext.jquery.jqplot.trendline' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jqplot/jqplot.trendline.js'
	],

	// General jqplot/SRF specific declarations
	// Plugin class supporting themes
	'ext.srf.jqplot.themes' => $formatModule + [
		'scripts' => 'jqplot/resources/ext.srf.jqplot.themes.js',
		'dependencies' => 'jquery.client'
	],

	//
	'ext.srf.jqplot.cursor' => $moduleTemplate + [
		'dependencies' =>  [
			'ext.srf.jqplot.bar',
			'ext.jquery.jqplot.cursor',
		],
		'position' => 'top',
	],

	//
	'ext.srf.jqplot.enhancedlegend' => $moduleTemplate + [
		'dependencies' =>  [
			'ext.srf.jqplot.bar',
			'ext.jquery.jqplot.enhancedlegend',
		],
		'position' => 'top',
	],

	//
	'ext.srf.jqplot.pointlabels' => $moduleTemplate + [
		'dependencies' =>  [
			'ext.srf.jqplot.bar',
			'ext.jquery.jqplot.pointlabels',
		],
		'position' => 'top',
	],

	//
	'ext.srf.jqplot.highlighter' => $moduleTemplate + [
		'dependencies' =>  [
			'ext.srf.jqplot.bar',
			'ext.jquery.jqplot.highlighter',
		],
		'position' => 'top',
	],

	//
	'ext.srf.jqplot.trendline' => $moduleTemplate + [
		'dependencies' =>  [
			'ext.srf.jqplot.bar',
			'ext.jquery.jqplot.trendline',
		],
		'position' => 'top',
	],

	// Chart specific declarations
	'ext.srf.jqplot.chart' => $formatModule + [
		'scripts' => [
			'jqplot/resources/ext.srf.jqplot.chart.bar.js',
			'jqplot/resources/ext.srf.jqplot.chart.pie.js',
			'jqplot/resources/ext.srf.jqplot.chart.js',
		],
		'styles'  => 'jqplot/resources/ext.srf.jqlpot.chart.css',
		'dependencies' => [
			'ext.jquery.jqplot.core',
			'jquery.async',
			'ext.srf.util',
			'ext.srf.jqplot.themes'
		]
	],

	//
	'ext.srf.jqplot.bar' => $formatModule + [
		'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.bar.js',
		'dependencies' =>  [
			'ext.jquery.jqplot.bar',
			'ext.srf.jqplot.chart'
		],
		'messages' =>  [
			'srf-error-jqplot-stackseries-data-length'
		],
		'position' => 'top',
	],

	//
	'ext.srf.jqplot.pie' => $formatModule + [
		'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.pie.js',
		'dependencies' =>  [
			'ext.jquery.jqplot.pie',
			'ext.srf.jqplot.chart'
		],
		'position' => 'top',
	],

	//
	'ext.srf.jqplot.bubble' => $formatModule + [
		'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.bubble.js',
		'dependencies' =>  [
			'ext.jquery.jqplot.bubble',
			'ext.srf.jqplot.chart'
		],
		'messages' =>  [
			'srf-error-jqplot-bubble-data-length'
		],
		'position' => 'top',
	],

	//
	'ext.srf.jqplot.donut' => $formatModule + [
		'scripts' => 'jqplot/resources/ext.srf.jqplot.chart.pie.js',
		'dependencies' =>  [
			'ext.jquery.jqplot.donut',
			'ext.srf.jqplot.chart'
		],
		'position' => 'top',
	],

	// Timeline
	// Copied from timeline-api.js
	'ext.smile.timeline.core' => $formatModule + [
		'scripts' => [
			'timeline/resources/SimileTimeline/scripts/timeline.js',
			'timeline/resources/SimileTimeline/scripts/util/platform.js',
			'timeline/resources/SimileTimeline/scripts/util/debug.js',
			'timeline/resources/SimileTimeline/scripts/util/xmlhttp.js',
			'timeline/resources/SimileTimeline/scripts/util/dom.js',
			'timeline/resources/SimileTimeline/scripts/util/graphics.js',
			'timeline/resources/SimileTimeline/scripts/util/date-time.js',
			'timeline/resources/SimileTimeline/scripts/util/data-structure.js',
			'timeline/resources/SimileTimeline/scripts/units.js',
			'timeline/resources/SimileTimeline/scripts/themes.js',
			'timeline/resources/SimileTimeline/scripts/ethers.js',
			'timeline/resources/SimileTimeline/scripts/ether-painters.js',
			'timeline/resources/SimileTimeline/scripts/labellers.js',
			'timeline/resources/SimileTimeline/scripts/sources.js',
			'timeline/resources/SimileTimeline/scripts/layouts.js',
			'timeline/resources/SimileTimeline/scripts/painters.js',
			'timeline/resources/SimileTimeline/scripts/decorators.js',
			'timeline/resources/SimileTimeline/scripts/labellers.js',

			// Keep this with in the same load sequence
			'timeline/resources/SimileTimeline/scripts/l10n/en/labellers.js',
			'timeline/resources/SimileTimeline/scripts/l10n/en/timeline.js'
		],
		'styles'  => [
			'timeline/resources/SimileTimeline/styles/timeline.css',
			'timeline/resources/SimileTimeline/styles/ethers.css',
			'timeline/resources/SimileTimeline/styles/events.css',
		],
		'targets' => [ 'mobile', 'desktop' ]
	],

	'ext.smile.timeline' => $formatModule + [
		'scripts' => [
			'timeline/resources/SimileTimeline/timeline-api.js',
		],
		'targets' => [ 'mobile', 'desktop' ]
	],

	'ext.srf.timeline' => $formatModule + [
		'scripts' => 'timeline/resources/ext.srf.timeline.js',
		'dependencies' => [
			'ext.smile.timeline',
			'mediawiki.legacy.wikibits'
		],
		'position' => 'top',
		'targets' => [ 'mobile', 'desktop' ]
	],

	// D3
	'ext.d3.core' => $moduleTemplate + [
		'scripts' => 'resources/jquery/d3/d3.v3.js'
	],

	//
	'ext.srf.d3.common' => $formatModule + [
		'scripts' => 'd3/resources/ext.srf.d3.common.js',
		'styles'  => 'd3/resources/ext.srf.d3.common.css',
		'dependencies' => 'ext.srf.util'
	],

	// Wordcloud
	'ext.d3.wordcloud' => $moduleTemplate + [
		'scripts' => 'resources/jquery/d3/d3.layout.cloud.js',
		'dependencies' =>  [
			'ext.d3.core',
			'ext.srf.d3.common'
		]
	],

	//
	'ext.srf.d3.chart.treemap' => $formatModule + [
		'scripts' => 'd3/resources/ext.srf.d3.chart.treemap.js',
		'styles'  => 'd3/resources/ext.srf.d3.chart.treemap.css',
		'dependencies' =>  [ 'ext.d3.core', 'ext.srf.d3.common' ],
		'position'     => 'top',
	],

	//
	'ext.srf.d3.chart.bubble' => $formatModule + [
		'scripts' => 'd3/resources/ext.srf.d3.chart.bubble.js',
		'styles'  => 'd3/resources/ext.srf.d3.chart.bubble.css',
		'dependencies' =>  [ 'ext.d3.core', 'ext.srf.d3.common' ],
		'position'     => 'top',
	],


	// JitGraph
	'ext.srf.jquery.progressbar' => $formatModule + [
		'scripts' => [
			'JitGraph/jquery.progressbar.js',
		],
	],
	'ext.srf.jit' => $formatModule + [
		'scripts' => [
			'JitGraph/Jit/jit.js',
		],
	],
	'ext.srf.jitgraph' => $formatModule + [
		'scripts' => [
			'JitGraph/SRF_JitGraph.js',
		],
		'styles' => [
			'JitGraph/base.css',
		],
		'dependencies' => [
			'mediawiki.legacy.wikibits',
			'ext.srf.jquery.progressbar',
			'ext.srf.jit',
		],
		'position' => 'top',
	],

	// Gallery

	// jcarousel
	'ext.jquery.jcarousel' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.jcarousel.js',
		'dependencies' => 'ext.jquery.migration.browser'
	],

	// responsiveslides
	'ext.jquery.responsiveslides' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.responsiveslides.js',
	],

	// Gallery base class
	'ext.srf.formats.gallery' => $formatModule + [
		'scripts' => 'gallery/resources/ext.srf.formats.gallery.js',
		'dependencies' => 'ext.srf.util'
	],

	//
	'ext.srf.gallery.carousel' => $formatModule + [
		'styles'  => 'gallery/resources/ext.srf.gallery.carousel.css',
		'scripts' => 'gallery/resources/ext.srf.gallery.carousel.js',
		'dependencies' => [
			'ext.srf.formats.gallery',
			'ext.jquery.jcarousel'
		],
		'position' => 'top',
	],

	//
	'ext.srf.gallery.slideshow' => $formatModule + [
		'scripts' => 'gallery/resources/ext.srf.gallery.slideshow.js',
		'styles'  => 'gallery/resources/ext.srf.gallery.slideshow.css',
		'dependencies' => [
			'ext.srf.formats.gallery',
			'ext.jquery.responsiveslides'
		],
		'messages' => [
			'srf-gallery-navigation-previous',
			'srf-gallery-navigation-next'
		],
		'position' => 'top',
	],

	//
	'ext.srf.gallery.overlay' => $formatModule + [
		'scripts' => 'gallery/resources/ext.srf.gallery.overlay.js',
		'styles'  => 'gallery/resources/ext.srf.gallery.overlay.css',
		'dependencies' => [
			'ext.srf.formats.gallery',
			'ext.jquery.fancybox'
		],
		'messages' => [
			'srf-gallery-overlay-count',
			'srf-gallery-image-url-error'
		],
		'position' => 'top',
	],

	//
	'ext.srf.gallery.redirect' => $formatModule + [
		'scripts' => 'gallery/resources/ext.srf.gallery.redirect.js',
		'styles'  => 'gallery/resources/ext.srf.gallery.redirect.css',
		'dependencies' => 'ext.srf.formats.gallery',
		'messages' => [
			'srf-gallery-image-url-error'
		],
		'position' => 'top',
	],

	// fullCalendar
	'ext.jquery.fullcalendar' => $moduleTemplate + [
		'scripts' => [
			'resources/jquery/fullcalendar/moment.js',
			'resources/jquery/fullcalendar/fullcalendar.js'
		],
		'styles' => 'resources/jquery/fullcalendar/fullcalendar.css',
	// If you have MW 1.20+ the definitions below will work but not for earlier
	// MW installations
	//	'styles' => array(
	//		'resources/jquery/fullcalendar/fullcalendar.css' => array( 'media' => 'screen' ),
	//		'resources/jquery/fullcalendar/fullcalendar.print.css' => array( 'media' => 'print' ),
	//	)
	],

	//
	'ext.jquery.gcal' => $moduleTemplate + [
		'scripts' => 'resources/jquery/fullcalendar/gcal.js',
	],

	// Eventcalendar widgets
	'ext.srf.widgets.eventcalendar' => $formatModule + [
		'scripts' => [
			'calendar/resources/ext.srf.widgets.calendarpane.js',
			'calendar/resources/ext.srf.widgets.calendarbutton.js',
			'calendar/resources/ext.srf.widgets.calendarparameters.js',
			'calendar/resources/ext.srf.widgets.calendarlegend.js',
		],
		'dependencies' =>  [
			'jquery.ui.core',
			'jquery.ui.widget',
			'jquery.ui.datepicker',
			'jquery.ui.slider',
			'ext.smw.tooltip',
			'ext.srf.util',
			'ext.srf.api',
		]
	],

	// Eventcalendar hooks
	'ext.srf.hooks.eventcalendar' => $formatModule + [
		'scripts' => 'calendar/resources/ext.srf.hooks.eventcalendar.js',
		'dependencies' => 'ext.srf'
	],

	// Eventcalendar module
	'ext.srf.eventcalendar' => $formatModule + $calendarMessages + [
		'scripts' => 'calendar/resources/ext.srf.formats.eventcalendar.js',
		'styles' => 'calendar/resources/ext.srf.formats.eventcalendar.css',
		'dependencies' =>  [
			'ext.srf.widgets.eventcalendar',
			'ext.srf.hooks.eventcalendar',
			'ext.jquery.fullcalendar',
		],
	],

	// Filtered
	'ext.srf.filtered' => $formatModule + [
			'scripts' => [
				'filtered/resources/js/ext.srf.filtered.js',
			],
			'styles' => [
				'filtered/resources/css/ext.srf.filtered.less',
			],
			'messages' => [
				'srf-filtered-value-filter-placeholder',
				'srf-filtered-value-filter-and',
				'srf-filtered-value-filter-or',
			],
			'dependencies' => [
				'ext.srf',
			],
			'position' => 'top',
		],

	'ext.srf.filtered.calendar-view.messages' => $formatModule + $calendarMessages,

	'ext.srf.filtered.calendar-view' => $formatModule +  array(
			'styles' => array(
				'filtered/resources/css/ext.srf.filtered.calendar-view.less',
			),
			'dependencies' => array(
				'ext.srf.filtered.calendar-view.messages',
				'ext.jquery.fullcalendar'
			),
		),

	'ext.srf.filtered.map-view.leaflet' => $formatModule + [
			'scripts' => [
				'filtered/resources/js/ext.srf.filtered.leaflet.js',
			],
			'styles' => [
				'filtered/resources/css/ext.srf.filtered.leaflet.css',
			],
		],

	'ext.srf.filtered.map-view' => $formatModule + [
			'styles' => [
				'filtered/resources/css/ext.srf.filtered.map-view.less',
			],
		],

	'ext.srf.filtered.value-filter' => $formatModule + [
			'styles' => [ 'filtered/resources/css/ext.srf.filtered.value-filter.less' ],
		],

	'ext.srf.filtered.value-filter.select' => $formatModule + [
			'scripts' => [ 'filtered/resources/js/ext.srf.filtered.select.js' ],
			'styles' => [ 'filtered/resources/css/ext.srf.filtered.select.css' ],
		],

	'ext.srf.filtered.slider' => $formatModule + [
			'scripts' => [ 'filtered/resources/js/ext.srf.filtered.slider.js' ],
			'styles' => [ 'filtered/resources/css/ext.srf.filtered.slider.css' ],
		],

	'ext.srf.filtered.distance-filter' => $formatModule + [
			'styles' => [ 'filtered/resources/css/ext.srf.filtered.distance-filter.less' ],
			'dependencies' => [ 'ext.srf.filtered.slider' ],
		],

	'ext.srf.filtered.number-filter' => $formatModule + [
			'styles' => [ 'filtered/resources/css/ext.srf.filtered.number-filter.less' ],
			'dependencies' => [ 'ext.srf.filtered.slider' ],
		],

	// Slideshow
	'ext.srf.slideshow' => $formatModule + [
		'scripts' => 'slideshow/resources/ext.srf.slideshow.js',
		'styles'  => 'slideshow/resources/ext.srf.slideshow.css',
		'dependencies' =>'mediawiki.util'
	],

	// Tagcanvas module

	'ext.jquery.tagcanvas' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jquery.tagcanvas.js'
	],

	// SRF Tag cloud module
	'ext.srf.formats.tagcloud' => $formatModule + [
		'scripts' => 'tagcloud/resources/ext.srf.formats.tagcloud.js',
		'styles'  => 'tagcloud/resources/ext.srf.formats.tagcloud.css',
		'dependencies' => 'ext.srf.util'
	],

	// Timeseries
	'ext.srf.flot.core' => $formatModule + [
		'styles'  => 'timeseries/resources/ext.srf.flot.core.css',
	],

	'ext.srf.timeseries.flot' => $formatModule + [
		'scripts' => 'timeseries/resources/ext.srf.timeseries.flot.js',
		'dependencies' => [
			'jquery.async',
			'ext.jquery.flot',
			'ext.srf.util',
			'ext.srf.flot.core'
		],
		'position' => 'top'
	],

	// Register the jplayer js
	'ext.jquery.jplayer' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jplayer/jquery.jplayer.js',
	],

	// Register the jplayer skin
	'ext.jquery.jplayer.skin.blue.monday' => $moduleTemplate + [
		'styles' => 'resources/jquery/jplayer/skin/blue.monday/jplayer.blue.monday.css',
	],

	// Register the jplayer skin
	'ext.jquery.jplayer.skin.morning.light' => $moduleTemplate + [
		'styles' => 'resources/jquery/jplayer/skin/morning.light/jplayer.morning.light.css',
	],

	// Register the jplayer playlist js
	'ext.jquery.jplayer.playlist' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jplayer/add-on/jplayer.playlist.min.js',
		'dependencies' => 'ext.jquery.jplayer',
	],

	// Register the jplayer inspector js
	'ext.jquery.jplayer.inspector' => $moduleTemplate + [
		'scripts' => 'resources/jquery/jplayer/add-on/jquery.jplayer.inspector.js',
		'dependencies' => 'ext.jquery.jplayer',
	],

	// SRF jplayer template specifications
	'ext.srf.template.jplayer' => $formatModule + [
		'scripts' => 'media/resources/ext.srf.template.jplayer.js',
		'messages' => [
			'srf-ui-mediaplayer-label-previous',
			'srf-ui-mediaplayer-label-play',
			'srf-ui-mediaplayer-label-pause',
			'srf-ui-mediaplayer-label-next',
			'srf-ui-mediaplayer-label-stop',
			'srf-ui-mediaplayer-label-mute',
			'srf-ui-mediaplayer-label-unmute',
			'srf-ui-mediaplayer-label-volume-max',
			'srf-ui-mediaplayer-label-shuffle',
			'srf-ui-mediaplayer-label-shuffle-off',
			'srf-ui-mediaplayer-label-repeat',
			'srf-ui-mediaplayer-label-repeat-off',
			'srf-ui-mediaplayer-label-full-screen',
			'srf-ui-mediaplayer-label-restore-screen',
		],
		'dependencies' => 'ext.srf'
	],

	// SRF implementation
	'ext.srf.formats.media' => $formatModule + [
		'scripts' => 'media/resources/ext.srf.formats.media.js',
		'styles'  => 'media/resources/ext.srf.formats.media.css',
		'dependencies' => [
			'ext.srf',
			'ext.srf.template.jplayer',
			'ext.jquery.jplayer.playlist'
		],
		'group' => 'ext.srf'
	],

	// jQuery DataTables
	'jquery.dataTables' => $moduleTemplate + [
		'scripts' => 'resources/jquery/datatables/jquery.dataTables.js',
		'position' => 'top'
	],

	// DataTables extras
	'jquery.dataTables.extras' => $moduleTemplate + [
		'scripts'  => 'resources/jquery/datatables/jquery.dataTables.extras.js',
	],

	// DataTables implementation
	'ext.srf.datatables' => $formatModule + [
		'scripts' => 'datatables/resources/ext.srf.formats.datatables.js',
		'styles'  => 'datatables/resources/ext.srf.formats.datatables.css',
		'dependencies' => [
			'jquery.dataTables',
			'jquery.dataTables.extras',
			'jquery.ui.core',
			'jquery.ui.widget',
			'jquery.ui.button',
			'ext.smw.dataItem',
			'ext.smw.api',
			'ext.srf.api',
			'ext.srf.util',
			'ext.srf.widgets'
		],
		'messages' => [
			'srf-ui-datatables-label-conditions',
			'srf-ui-datatables-label-parameters',
			'srf-ui-datatables-label-filters',
			'srf-ui-datatables-label-information',
			'srf-ui-datatables-panel-disclaimer',
			'srf-ui-datatables-label-update-success',
			'srf-ui-datatables-label-update-error',
			'srf-ui-datatables-label-sEmptyTable',
			'srf-ui-datatables-label-sInfo',
			'srf-ui-datatables-label-sInfoEmpty',
			'srf-ui-datatables-label-sInfoFiltered',
			'srf-ui-datatables-label-sInfoPostFix',
			'srf-ui-datatables-label-sInfoThousands',
			'srf-ui-datatables-label-sLengthMenu',
			'srf-ui-datatables-label-sLoadingRecords',
			'srf-ui-datatables-label-sProcessing',
			'srf-ui-datatables-label-sSearch',
			'srf-ui-datatables-label-sZeroRecords',
			'srf-ui-datatables-label-oPaginate-sFirst',
			'srf-ui-datatables-label-oPaginate-sLast',
			'srf-ui-datatables-label-oPaginate-sNext',
			'srf-ui-datatables-label-oPaginate-sPrevious',
			'srf-ui-datatables-label-oAria-sSortAscending',
			'srf-ui-datatables-label-oAria-sSortDescending',
			'srf-ui-datatables-label-multiselect-column-header',
			'srf-ui-datatables-label-multiselect-column-noneselectedtext',
			'srf-ui-datatables-label-multiselect-column-selectedtext',
			'srf-ui-datatables-label-placeholder-column-search',
			'srf-ui-datatables-label-content-cache',
			'srf-ui-datatables-label-content-server'
		]
	],

	// DataTables bootstrap
	'ext.srf.datatables.bootstrap' => $moduleTemplate + [
		'scripts' => 'resources/jquery/datatables/jquery.dataTables.bootstrap.js',
		'styles'  => 'resources/jquery/datatables/jquery.dataTables.bootstrap.css',
		'dependencies' => 'ext.srf.datatables'
	],

	// DataTables basic
	'ext.srf.datatables.basic' => $moduleTemplate + [
		'styles'  => [
			'resources/jquery/datatables/jquery.dataTables.css',
			'resources/jquery/datatables/jquery.dataTables.images.css'
		],
		'dependencies' => 'ext.srf.datatables'
	],

	// Boilerplate example registration
	/*
		// Simple implementation
		'ext.srf.boilerplate.simple' => $formatModule + array(
			'scripts' => 'boilerplate/resources/ext.srf.boilerplate.simple.js',
			'styles'  => 'boilerplate/resources/ext.srf.boilerplate.css',
			'messages' => array(
				'srf-boilerplate-message'
			),
		);

		// Using the semanticFormats namespace class implementation
		'ext.srf.boilerplate.namespace' => $formatModule + array(
			'scripts' => 'boilerplate/resources/ext.srf.boilerplate.namespace.js',
			'styles'  => 'boilerplate/resources/ext.srf.boilerplate.css',
			'dependencies' => array (
				'ext.srf.util'
			),
			'messages' => array(
				'srf-boilerplate-message'
			),
		);
	*/
];
