/**
 * JavaSript for SRF D3 chart treemap module using d3 v2
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @release: 0.2
 */
(function( $ ) {

/* Error handling *************************************************************/
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }
/* Start javascript ***********************************************************/

	$(document).ready(function() {

		$( ".srf-d3-chart-treemap" ).each(function() {

			var $this = $( this ),
				chart  = $this.find( ".container" ),
				d3ID   = chart.attr( "id" ),
				json   = mw.config.get( d3ID );

			// Parse json string and convert it back into objects
			typeof json == 'string' ? container = jQuery.parseJSON( json ) : container = json;

			var data      = container['data'],
				width       = container['parameters'].width,
				height      = container['parameters'].height,
				charttitle  = container['parameters'].charttitle,
				charttext   = container['parameters'].charttext,
				datalabels  = container['parameters'].datalabels,
				colors      = container['parameters'].colorscheme == null ? colorscheme[0] : colorscheme[container['parameters'].colorscheme][9];

			// Release the graph
			$this.find( ".srf-processing" ).hide();
			$this.css( 'width', width ).css( 'height', height);
			chart.show();

			// Add chart title
			if ( charttitle.length > 0 ) {
				charttitle = '<span class="srf-d3-chart-title">' + charttitle + '</span>';
				$this.find( '#' + d3ID ).before( charttitle );
			}

			// Add bottom chart text
			if ( charttext.length > 0 ) {
				charttext  = '<span class="srf-d3-chart-text">' + charttext  + '</span>';
				$this.find( '#' + d3ID ).after( charttext );
			}

			// Calculate height
			height = height - ( $this.find( '.srf-d3-chart-title' ).height() + $this.find( '.srf-d3-chart-text' ).height() );

/* D3 object declaration ******************************************************/

			// Create an ordinal color array and set formatting
			var color = d3.scale.ordinal().range( colors ),
				format  = d3.format(",d");

			// Data array definition
			var treeArray = [];
			treeArray.push( {
				label:  charttitle !== '' ? container['parameters'].charttitle : mw.config.get ( 'wgTitle' ),
				children:  data
			} );

			var treemap = d3.layout.treemap()
				.padding( 4 )
				.size([ width , height ])
				.value( function( d ) { return d.value; } );

			var svg = d3.select( "#" + d3ID ).append("svg")
				.attr( "width", width )
				.attr( "height", height )
				.append( "g" )
				.attr( "transform", "translate(-.5,-.5)" );

			var cell = svg.data( treeArray ).selectAll( "g" )
				.data( treemap )
				.enter().append( "g" )
				.attr( "class", "cell" )
				.attr( "transform", function( d ) { return "translate(" + d.x + "," + d.y + ")"; } );

			cell.append( "title" )
				.text( function( d ) { return d.data.label + ( d.children ? "" : ": " + format( d.data.value ) ); } );

			cell.append( "rect" )
				.attr( "width", function( d ) { return d.dx; } )
				.attr( "height", function( d ) { return d.dy; } )
				.style( "fill", function( d ) { return d.label ? color( d.data.label ) :  color( d.data.label ); } );

			cell.append( "text" )
				.attr( "x", function( d ) { return d.dx / 2; } )
				.attr( "y", function( d ) { return d.dy / 2; } )
				.attr( "dy", ".35em" )
				.attr( "text-anchor", "middle" )
				.text( function( d ) { return d.children ? null : datalabels == 'value' ? d.data.value : d.data.label ; } );
/* D3 *************************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready
} )( window.jQuery );