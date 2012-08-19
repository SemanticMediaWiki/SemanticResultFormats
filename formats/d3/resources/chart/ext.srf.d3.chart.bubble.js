/**
 * JavaSript for SRF D3 chart bubble module using d3 v2
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * jshint compliance checked
 *
 * @release: 0.2
 */
( function( $ ) {
	"use strict";

	/*global d3:true, mw:true, colorscheme:true*/

	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	$.fn.d3ChartBubble = function() {
		var	width  = this.width(),
			height = this.height(),
			chart  = this.find( ".container" ),
			d3ID   = chart.attr( "id" ),
			json   = mw.config.get( d3ID );

		// Parse json string and convert it back
		var container = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

		var charttitle  = container.parameters.charttitle,
			charttext   = container.parameters.charttext,
			datalabels  = container.parameters.datalabels,
			colors      = container.parameters.colorscheme === null ? colorscheme[0] : colorscheme[container.parameters.colorscheme][9];

		// Release the graph
		this.find( '.srf-processing' ).hide();
		this.css( 'width', width ).css( 'height', height);
		chart.show();

		// Add chart title
		if ( charttitle.length > 0 ) {
			charttitle = '<span class="srf-d3-chart-title">' + charttitle + '</span>';
			this.find( '#' + d3ID ).before( charttitle );
		}

		// Add bottom chart text
		if ( charttext.length > 0 ) {
			charttext  = '<span class="srf-d3-chart-text">' + charttext  + '</span>';
			this.find( '#' + d3ID ).after( charttext );
		}

		// Calculate height
		height = height - ( this.find( '.srf-d3-chart-title' ).height() + this.find( '.srf-d3-chart-text' ).height() );

		// Create an ordinal color array and set formatting
		var color = d3.scale.ordinal().range( colors ),
			format  = d3.format( ",d" );

		// Data array definition
		var packlayout = [];
		packlayout.push( {
			label:  charttitle !== '' ? container.parameters.charttitle :  mw.config.get ( 'wgTitle' ),
			children: container.data
		} );

		var pack = d3.layout.pack()
			.size([width - 4, height - 4])
			.value( function( d ) { return d.value; } );

		var vis = d3.select( "#" + d3ID ).append( "svg" )
			.attr( "width", width )
			.attr( "height", height )
			.attr( "class", "pack" )
			.append( "g" )
			.attr( "transform", "translate(2, 2)" );

		var node = vis.data(packlayout).selectAll("g.node")
			.data( pack.nodes )
			.enter().append("g")
			.attr( "class", function( d ) { return d.children ? "node" : "leaf node"; } )
			.attr( "transform", function( d ) { return "translate(" + d.x + "," + d.y + ")"; } );

		node.append("title")
			.text(function( d ) { return d.label + ( d.children ? "" : ": " + format( d.value ) ); } );

		node.append( "circle" )
			.attr( "r" , function( d ) { return d.r; } )
			.style( "fill" , function( d ) { return d.children ? null : color( d.label ); } );

		node.filter(function( d ) { return !d.children; } ).append("text")
			.attr( "text-anchor", "middle" )
			.attr( "dy", ".3em" )
			.text( function( d ) { return d.children ? null : datalabels === 'value' ? d.value : d.label.substring( 0, d.r / 3 ); } );
	};

	// DOM is ready
	$(document).ready(function() {
		$( ".srf-d3-chart-bubble" ).each(function() {
			$( this ).d3ChartBubble();
		} );
	} );
} )( window.jQuery );