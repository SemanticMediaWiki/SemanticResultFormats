/**
 * JavaScript for SRF Gallery jcarousel module
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

	$( document ).ready( function() {

		// Hide processing image
		$( ".srf-gallery-carousel" ).each(function() {
			$( this ).find( '.srf-processing' ).hide();
		} );

		// Bind individual elements containing class jcarousel as the plug-in
		// requires different id's
		$( '.jcarousel' ).each( function() {
			var $this = $( this );

			// Display carousel after jquery is loaded
			$this.show();

			// Call the  jcarousel plug-in
			$this.jcarousel( {
				scroll:  parseInt( $this.attr( 'data-scroll' ), 10 ), // Number of items to be scrolled
				visible: parseInt( $this.attr( 'data-visible' ), 10 ), // calculated and set visible elements
				wrap: $this.attr( 'data-wrap' ), // Options are "first", "last", "both" or "circular"
				vertical: $this.attr( 'data-vertical' ) === 'true', // Whether the carousel appears in horizontal or vertical orientation
				rtl: $this.attr( 'data-rtl' ) === 'true' // Directionality
			} );
/* End javascript *************************************************************/
		} );
	} );
})( window.jQuery );