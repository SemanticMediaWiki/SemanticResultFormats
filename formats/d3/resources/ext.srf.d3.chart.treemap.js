/**
 * JavaScript for SRF D3 chart treemap module supporting d3 v3 and v4+
 *
 * @param $
 * @param srf
 * @see http://www.semantic-mediawiki.org/wiki/Help:D3chart format
 *
 * @since 1.8
 * @release 0.3
 *
 * @license GPL-2.0-or-later
 * @author mwjames
 */
( function ( $, srf ) {
	'use strict';

	/* global d3:true, mw:true, colorscheme:true */

	/**
	 * Module for formats extensions
	 *
	 * @since 1.8
	 * @type Object
	 */
	srf.formats = srf.formats || {};

	/**
	 * Base constructor for objects representing a d3 instance
	 *
	 * @since 1.8
	 * @type Object
	 */
	srf.formats.d3 = function () {};

	srf.formats.d3.prototype = {
		treemap: function ( context ) {
			return context.each( function () {
				let width = $( this ).width(),
					height = $( this ).height(),
					chart = $( this ).find( '.container' ),
					d3ID = chart.attr( 'id' ),
					json = mw.config.get( d3ID );

				// Parse JSON string if necessary
				const container = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

				let charttitle = container.parameters.charttitle || '',
					charttext = container.parameters.charttext || '',
					datalabels = container.parameters.datalabels,
					colors;

				if ( !container.parameters.colorscheme || typeof colorscheme[ container.parameters.colorscheme ] === 'undefined' ) {
					colors = colorscheme[ 0 ];
				} else {
					colors = colorscheme[ container.parameters.colorscheme ];
				}

				// Hide spinner, set dimensions, show chart container
				util.spinner.hide( { context: $( this ) } );
				$( this ).css( 'width', width ).css( 'height', height );
				chart.show();

				// Add chart title if set
				if ( charttitle.length > 0 ) {
					const titleHTML = '<span class="srf-d3-chart-title">' + charttitle + '</span>';
					$( this ).find( '#' + d3ID ).before( titleHTML );
				}

				// Add chart text if set
				if ( charttext.length > 0 ) {
					const textHTML = '<span class="srf-d3-chart-text">' + charttext + '</span>';
					$( this ).find( '#' + d3ID ).after( textHTML );
				}

				// Adjust height by subtracting title and text heights
				const titleHeight = $( this ).find( '.srf-d3-chart-title' ).height() || 0;
				const textHeight = $( this ).find( '.srf-d3-chart-text' ).height() || 0;
				height = height - ( titleHeight + textHeight );
				if ( isNaN( height ) || height < 0 ) {
					height = 0;
				}

				// Detect if using D3 v4+ by checking for d3.pack
				const isV4Plus = typeof d3.pack === 'function';

				// Color scale
				const color = isV4Plus ?
					d3.scaleOrdinal().range( colors ) :
					d3.scale.ordinal().range( colors );

				const format = d3.format( ',d' );

				// Root data object
				const treeData = {
					label: charttitle !== '' ? charttitle : mw.config.get( 'wgTitle' ),
					children: container.data
				};

				// Select or create SVG container
				let svg = d3.select( '#' + d3ID ).select( 'svg' );
				if ( svg.empty() ) {
					svg = d3.select( '#' + d3ID ).append( 'svg' )
						.attr( 'width', width )
						.attr( 'height', height );
				} else {
					svg.selectAll( '*' ).remove();
					svg.attr( 'width', width ).attr( 'height', height );
				}

				if ( isV4Plus ) {
					// D3 v4+ usage
					const root = d3.hierarchy( treeData )
						.sum( ( d ) => d.value )
						.sort( ( a, b ) => b.value - a.value );

					const treemap = d3.treemap()
						.size( [ width, height ] )
						.padding( 4 );

					treemap( root );

					const cell = svg.selectAll( 'g' )
						.data( root.leaves() )
						.enter().append( 'g' )
						.attr( 'class', 'cell' )
						.attr( 'transform', ( d ) => 'translate(' + d.x0 + ',' + d.y0 + ')' );

					cell.append( 'title' )
						.text( ( d ) => d.data.label + ': ' + format( d.value ) );

					cell.append( 'rect' )
						.attr( 'width', ( d ) => d.x1 - d.x0 )
						.attr( 'height', ( d ) => d.y1 - d.y0 )
						.style( 'fill', ( d ) => color( d.data.label ) );

					cell.append( 'text' )
						.attr( 'x', ( d ) => ( d.x1 - d.x0 ) / 2 )
						.attr( 'y', ( d ) => ( d.y1 - d.y0 ) / 2 )
						.attr( 'dy', '.35em' )
						.attr( 'text-anchor', 'middle' )
						.text( ( d ) => {
							if ( datalabels === 'value' ) {
								return d.value;
							}
							return d.data.label;
						} );
				} else {
					// D3 v3 or lower usage
					const treemap = d3.layout.treemap()
						.size( [ width, height ] )
						.padding( 4 )
						.value( ( d ) => d.value );

					const nodes = treemap.nodes( treeData );

					const cell = svg.selectAll( 'g' )
						.data( nodes )
						.enter().append( 'g' )
						.attr( 'class', 'cell' )
						.attr( 'transform', ( d ) => 'translate(' + d.x + ',' + d.y + ')' );

					cell.append( 'title' )
						.text( ( d ) => d.label + ( d.children ? '' : ': ' + format( d.value ) ) );

					cell.append( 'rect' )
						.attr( 'width', ( d ) => d.dx )
						.attr( 'height', ( d ) => d.dy )
						.style( 'fill', ( d ) => d.label ? color( d.label ) : color( d.label ) );

					cell.append( 'text' )
						.attr( 'x', ( d ) => d.dx / 2 )
						.attr( 'y', ( d ) => d.dy / 2 )
						.attr( 'dy', '.35em' )
						.attr( 'text-anchor', 'middle' )
						.text( ( d ) => {
							if ( d.children ) {
								return null;
							}
							if ( datalabels === 'value' ) {
								return d.value;
							}
							return d.label;
						} );
				}
			} );
		}
	};

	const srfD3 = new srf.formats.d3();
	const util = new srf.util();

	$( document ).ready( () => {
		$( '.srf-d3-chart-treemap' ).each( function () {
			srfD3.treemap( $( this ) );
		} );
	} );
}( jQuery, semanticFormats ) );
