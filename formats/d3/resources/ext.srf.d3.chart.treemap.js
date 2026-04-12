/**
 * JavaScript for SRF D3 chart treemap module supporting d3 v3 and v4+
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
		treemap: function(context) {
			return context.each(function() {
				var width = $(this).width(),
					height = $(this).height(),
					chart = $(this).find('.container'),
					d3ID = chart.attr('id'),
					json = mw.config.get(d3ID);

				// Parse JSON string if necessary
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

				// Color scale
				var color = isV4Plus
					? d3.scaleOrdinal().range(colors)
					: d3.scale.ordinal().range(colors);

				var format = d3.format(",d");

				// Root data object
				var treeData = {
					label: charttitle !== '' ? charttitle : mw.config.get('wgTitle'),
					children: container.data
				};

				// Select or create SVG container
				var svg = d3.select("#" + d3ID).select("svg");
				if (svg.empty()) {
					svg = d3.select("#" + d3ID).append("svg")
						.attr("width", width)
						.attr("height", height);
				} else {
					svg.selectAll("*").remove();
					svg.attr("width", width).attr("height", height);
				}

				if (isV4Plus) {
					// D3 v4+ usage
					var root = d3.hierarchy(treeData)
						.sum(function(d) { return d.value; })
						.sort(function(a, b) { return b.value - a.value; });

					var treemap = d3.treemap()
						.size([width, height])
						.padding(4);

					treemap(root);

					var cell = svg.selectAll("g")
						.data(root.leaves())
						.enter().append("g")
						.attr("class", "cell")
						.attr("transform", function(d) { return "translate(" + d.x0 + "," + d.y0 + ")"; });

					cell.append("title")
						.text(function(d) { return d.data.label + ": " + format(d.value); });

					cell.append("rect")
						.attr("width", function(d) { return d.x1 - d.x0; })
						.attr("height", function(d) { return d.y1 - d.y0; })
						.style("fill", function(d) { return color(d.data.label); });

					cell.append("text")
						.attr("x", function(d) { return (d.x1 - d.x0) / 2; })
						.attr("y", function(d) { return (d.y1 - d.y0) / 2; })
						.attr("dy", ".35em")
						.attr("text-anchor", "middle")
						.text(function(d) {
							if (datalabels === 'value') return d.value;
							return d.data.label;
						});
				} else {
					// D3 v3 or lower usage
					var treemap = d3.layout.treemap()
						.size([width, height])
						.padding(4)
						.value(function(d) { return d.value; });

					var nodes = treemap.nodes(treeData);

					var cell = svg.selectAll("g")
						.data(nodes)
						.enter().append("g")
						.attr("class", "cell")
						.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

					cell.append("title")
						.text(function(d) { return d.label + (d.children ? "" : ": " + format(d.value)); });

					cell.append("rect")
						.attr("width", function(d) { return d.dx; })
						.attr("height", function(d) { return d.dy; })
						.style("fill", function(d) { return d.label ? color(d.label) : color(d.label); });

					cell.append("text")
						.attr("x", function(d) { return d.dx / 2; })
						.attr("y", function(d) { return d.dy / 2; })
						.attr("dy", ".35em")
						.attr("text-anchor", "middle")
						.text(function(d) {
							if (d.children) return null;
							if (datalabels === 'value') return d.value;
							return d.label;
						});
				}
			});
		}
	};

	var srfD3 = new srf.formats.d3();
	var util = new srf.util();

	$(document).ready(function() {
		$('.srf-d3-chart-treemap').each(function() {
			srfD3.treemap($(this));
		});
	});
})(jQuery, semanticFormats);
