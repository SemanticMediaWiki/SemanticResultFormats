/**
 * JavaScript for SRF tagcloud module using the tagcanvas plug-in
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @since: 1.8
 * @release: 0.3
 */
( function( $, mw ) {
	"use strict";

	var sphere = {
		init: function () {
			$(document).ready(function() {
				if( $.isFunction( $.fn.eachAsync ) ){
						$( '.srf-tagcloud-sphere' ).eachAsync( {
						delay: 100,
						bulk: 0,
						loop: function(){
							sphere.output( $( this ) );
						}
					} );
				} else {
					$( '.srf-tagcloud-sphere' ).each( function() {
						sphere.output( $( this ) );
					} );
				}
			} );
		},
		output: function ( $this ) {
			var container = $this.find( ".container" ),
				containerID = container.attr( "id" ),
				width       = container.data( "width" ),
				height      = container.data( "height" ),
				textFont    = container.data( "font" ),
				tagsID      = container.children( "div" ).attr('id');

			// Hide and re-assign elements
			$this.find( '.srf-processing' ).hide();
			$this.css( { 'width': width, 'height': height } );

			// Add canvas object
			var canvasID = containerID + '-canvas';
			$this.find( '#' + containerID ).append( '<canvas></canvas>' );
			$this.find( 'canvas' ).attr( 'id', canvasID ).attr( 'width', width ).attr( 'height', height );

			if( !$this.find( '#' + canvasID ).tagcanvas( {
				textColour: null,
				outlineColour: '#FF9D43',
				textFont: textFont,
				reverse: true,
				weight: true,
				shadow: '#ccf',
				shadowBlur: 3,
				depth: 0.3,
				maxSpeed: 0.04
			}, tagsID ) ) {
				// something went wrong, hide the canvas container
				$this.find( '#' + containerID ).hide();
			}
		}
	};

	var p = $.client.profile();

	if ( p.name === 'msie' && p.versionNumber < 9 ) {
		mw.loader.using( 'ext.jquery.tagcanvas.excanvas', sphere.init );
	} else {
		sphere.init();
	}
} )( window.jQuery );