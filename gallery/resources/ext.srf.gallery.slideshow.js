/**
 * JavaSript for SRF gallery slides module
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

		$( ".srf-gallery-slideshow" ).each(function() {
			var $this = $( this );
			var maxHeight = 0;
			var gallery   = $this.find( 'ul' );
			var galleryId = "#" + gallery.attr( "id" );
			var previous  = $this.prev( 'p' ).children( 'br' );

			// The gallery parser comes with a preceding empty <p> element
			// this is a work-around to avoid
			if ( previous.length == 1 ) {
				previous.hide();
			}

			// Make elements visible / hide
			$this.find( '.srf-processing' ).hide();
			gallery.show();

			// Loop over all the gallery items
			gallery.find( 'li' ).each( function () {

				// Determine height because text elements can change the max height
				if($(this).height() > maxHeight ) {
					maxHeight = $( this ).height();
				}
			} );

			// Set the max height, so all elements are equally positioned
			gallery.height( maxHeight );

			if( !gallery.responsiveSlides({
				pauseControls: gallery.attr( 'data-nav-control' ) === 'auto',
				prevText: mw.msg( 'srf-gallery-navigation-previous' ),
				nextText: mw.msg( 'srf-gallery-navigation-next' ),
				auto:  gallery.attr( 'data-nav-control' ) === 'auto',
				pause: gallery.attr( 'data-nav-control' ) === 'auto',
				pager: gallery.attr( 'data-nav-control' ) === 'pager',
				nav:   gallery.attr( 'data-nav-control' ) === 'nav'
			} ) ) {
				// something went wrong, hide the canvas container
				$this.find( galleryId ).hide();
			}
/* End javascript *************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready
} )( window.jQuery );