/**
 * JavaSript for SRF Tagcloud module using the TagCanvas plug-in
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @since: 1.8
 *
 * @release: 0.1
 */
(function( $ ) {

/* Error handling *************************************************************/
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }
/* Start javascript ***********************************************************/
	$(document).ready(function() {

		$( ".srf-tagcloud-sphere" ).each(function() {
			var $this = $( this );

			var container = $this.children( "div" ),
				containerID = container.attr( "id" ),
				textFont    = container.attr( "data-font" ),
				canvasID    = container.children( "canvas" ).attr('id'),
				tagsID      = container.children( "div" ).attr('id');

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
/* End javascript *************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready
} )( window.jQuery );