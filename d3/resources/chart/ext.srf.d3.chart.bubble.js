/**
 * JavaSript for SRF D3 chart pack module using d3 v2
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

		$( ".srf-d3-chart-bubble" ).each(function() {

			var $this = $( this ),
				d3ID   = $this.find( ".bubble" ).attr( "id" ),
				json   = mw.config.get( d3ID ),
				width  = $this.width(),
				height = $this.height();

			// Parse json string and convert it back into objects
			typeof json == 'string' ? data = JSON.parse( json ) : data = json;

			// Release the graph
			$this.show();

			var children = data['data'],
				charttitle = data['parameters'].charttitle,
				charttext  = data['parameters'].charttext,
				datalabels = data['parameters'].datalabels,
				colors     = data['parameters'].colorscheme == null ? colorscheme[0] : colorscheme[data['parameters'].colorscheme][9];

			if ( charttitle.length > 0 ) {
				charttitle = '<span class="srf-d3-chart-title">' + charttitle + '</span>';
				$this.find( '#' + d3ID ).before( charttitle );
			}

			if ( charttext.length > 0 ) {
				charttext  = '<span class="srf-d3-chart-text">' + charttext  + '</span>';
				$this.find( '#' + d3ID ).after( charttext );
			}

			// Calculate height
			height = height - ( $this.find( '.srf-d3-chart-title' ).height() + $this.find( '.srf-d3-chart-text' ).height() );

/* D3 object declaration ******************************************************/

			// Create a function of an ordinal array
			var color = d3.scale.ordinal().range( colors ),
				format = d3.format(",d");

			var packlayout = [];
			packlayout.push( {
				label:  charttitle !== '' ? data['parameters'].charttitle :  mw.config.get ( 'wgTitle' ),
				children:  children
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

			node.filter(function( d ) { return !d.children; }).append("text")
				.attr( "text-anchor", "middle" )
				.attr( "dy", ".3em" )
				.text( function( d ) { return d.children ? null : datalabels == 'value' ? d.value : d.label.substring(0, d.r / 3); } );
/* D3 *************************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready
} )( window.jQuery );