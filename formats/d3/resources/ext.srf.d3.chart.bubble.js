/**
 * JavaScript for SRF D3 chart bubble module
 * Supports D3 v3 and v4+ (auto-detects version)
 *
 * @see http://www.semantic-mediawiki.org/wiki/Help:D3chart format
 *
 * @since 1.8
 * @release 0.3
 *
 * @license GPL-2.0-or-later
 * @author mwjames
 */
(function($, srf) {
	'use strict';

	/*global d3:true, mw:true, colorscheme:true*/
	/**
	 * Module for formats extensions
	 * @since 1.8
	 * @type Object
	 */
	srf.formats = srf.formats || {};

	/**
	 * Base constructor for objects representing a d3 instance
	 * @since 1.8
	 * @type Object
	 */
	srf.formats.d3 = function() {};

	srf.formats.d3.prototype = {
		bubble: function(context) {
			return context.each(function() {
				var width = $(this).width(),
					height = $(this).height(),
					chart = $(this).find('.container'),
					d3ID = chart.attr('id'),
					json = mw.config.get(d3ID);

				var container = typeof json === 'string' ? jQuery.parseJSON(json) : json;

				var charttitle = container.parameters.charttitle || '',
					charttext = container.parameters.charttext || '',
					datalabels = container.parameters.datalabels,
					colors;

				if (!container.parameters.colorscheme || typeof colorscheme[container.parameters.colorscheme] === 'undefined') {
					colors = colorscheme[0];
				} else {
					colors = colorscheme[container.parameters.colorscheme];
				}

				// Hide spinner, set dimensions, show chart container
				util.spinner.hide({ context: $(this) });
				$(this).css('width', width).css('height', height);
				chart.show();

				// Add chart title if set
				if (charttitle.length > 0) {
					var titleHTML = '<span class="srf-d3-chart-title">' + charttitle + '</span>';
					$(this).find('#' + d3ID).before(titleHTML);
				}

				// Add chart text if set
				if (charttext.length > 0) {
					var textHTML = '<span class="srf-d3-chart-text">' + charttext + '</span>';
					$(this).find('#' + d3ID).after(textHTML);
				}

				// Adjust height by subtracting title and text heights
				var titleHeight = $(this).find('.srf-d3-chart-title').height() || 0;
				var textHeight = $(this).find('.srf-d3-chart-text').height() || 0;
				height = height - (titleHeight + textHeight);
				if (isNaN(height) || height < 0) height = 0;

				// Detect if using D3 v4+ by checking for d3.pack
				var isV4Plus = typeof d3.pack === "function";

				// Color scale and format function
				var color = isV4Plus
					? d3.scaleOrdinal().range(colors)
					: d3.scale.ordinal().range(colors);

				var format = d3.format(",d");

				// Data root object
				var packlayout = {
					label: charttitle !== '' ? charttitle : mw.config.get('wgTitle'),
					children: container.data
				};

				// Select or create SVG element
				var svg = d3.select("#" + d3ID).select("svg");
				if (svg.empty()) {
					svg = d3.select("#" + d3ID).append("svg")
						.attr("width", width)
						.attr("height", height)
						.attr("class", "pack");
				} else {
					svg.selectAll("*").remove();
					svg.attr("width", width).attr("height", height);
				}

				var vis = svg.append("g").attr("transform", "translate(2,2)");

				if (isV4Plus) {
					// D3 v4+ usage
					var pack = d3.pack()
						.size([width - 4, height - 4])
						.padding(1);

					var root = d3.hierarchy(packlayout)
						.sum(function(d) { return d.value; })
						.sort(function(a, b) { return b.value - a.value; });

					pack(root);

					var node = vis.selectAll("g.node")
						.data(root.descendants())
						.enter().append("g")
						.attr("class", function(d) { return d.children ? "node" : "leaf node"; })
						.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

					node.append("title")
						.text(function(d) { return d.data.label + (d.children ? "" : ": " + format(d.value)); });

					node.append("circle")
						.attr("r", function(d) { return d.r; })
						.style("fill", function(d) { return d.children ? null : color(d.data.label); });

					node.filter(function(d) { return !d.children; })
						.append("text")
						.attr("text-anchor", "middle")
						.attr("dy", ".3em")
						.text(function(d) {
							if (d.children) return null;
							if (datalabels === 'value') return d.value;
							return d.data.label.substring(0, d.r / 3);
						});
				} else {
					// D3 v3 or lower usage
					var pack = d3.layout.pack()
						.size([width - 4, height - 4])
						.value(function(d) { return d.value; });

					var nodes = pack.nodes(packlayout);

					var node = vis.selectAll("g.node")
						.data(nodes)
						.enter().append("g")
						.attr("class", function(d) { return d.children ? "node" : "leaf node"; })
						.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

					node.append("title")
						.text(function(d) { return d.label + (d.children ? "" : ": " + format(d.value)); });

					node.append("circle")
						.attr("r", function(d) { return d.r; })
						.style("fill", function(d) { return d.children ? null : color(d.label); });

					node.filter(function(d) { return !d.children; })
						.append("text")
						.attr("text-anchor", "middle")
						.attr("dy", ".3em")
						.text(function(d) {
							if (d.children) return null;
							if (datalabels === 'value') return d.value;
							return d.label.substring(0, d.r / 3);
						});
				}
			});
		}
	};

	/**
	 * Implementation and representation of the d3 treemap instance
	 * @since 1.8
	 * @type Object
	 */
	var srfD3 = new srf.formats.d3();
	var util = new srf.util();

	$(document).ready(function() {
		$('.srf-d3-chart-bubble').each(function() {
			srfD3.bubble($(this));
		});
	});
})(jQuery, semanticFormats);
