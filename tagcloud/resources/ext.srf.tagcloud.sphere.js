/**
 * JavaSript for SRF tagcloud module using the tagcanvas plug-in
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @since: 1.8
 *
 * @release: 0.2.1
 */
(function( $ ) {

/* Error handling *************************************************************/
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }
/* Start javascript ***********************************************************/
	var sphere = { init: function () {
		$(document).ready(function() {
			$( ".srf-tagcloud-sphere" ).each(function() {
				var $this = $( this );

				var container = $this.find( ".container" ),
					containerID = container.attr( "id" ),
					width       = container.attr( "width" ),
					height      = container.attr( "height" ),
					textFont    = container.attr( "data-font" ),
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
			} ); // end of initilized $this object
		} ); // end $(document).ready
	} } // end sphere init
/* Check browser ( jquery.profile ) *******************************************/
	var p = $.client.profile()

	if ( p.name === 'msie' && p.versionNumber < 9 ) {
		mw.loader.using( 'ext.jquery.tagcanvas.excanvas', sphere.init );
	} else {
		sphere.init();
	}
/* End ************************************************************************/
} )( window.jQuery );