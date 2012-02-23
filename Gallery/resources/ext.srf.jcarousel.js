/**
 * JavasSript for SRF Gallery Carousel module
 *
 * @licence: GNU GPL v3 or later
 * @author:  mwjames
 * 
 * @release: 0.1  
 */

(function( $ ) {

	$( document ).ready( function() {

		var v_visible = parseInt($( '#carousel' ).attr( 'visible' ) ), 
		v_scroll = parseInt($( '#carousel' ).attr( 'scroll' ) ),
		v_directionality = $( '#carousel' ).attr( 'directionality' ),
		v_orientation = $( '#carousel' ).attr( 'orientation' ),
		v_wrap = $( '#carousel' ).attr( 'wrap' ),
		v_vertical = v_orientation === 'vertical',
		v_rtl = v_directionality === 'rtl';
		
		// Display carousel only after js is loaded and ready otherwise display=none
		$( '#carousel').show();
   
		  // Call the  jcarousel plug-in
		$( '#carousel' ).jcarousel( {
			scroll: v_scroll, // Number of items to be scrolled
			visible: v_visible, // calculated and set visible elements
    		wrap: 'circular', // Whether to wrap at the first/last item  (Options are "first", "last", "both" or "circular")
    		vertical: v_vertical, // Whether the carousel appears in horizontal or vertical orientation 
    		rtl: v_rtl // Directionality
    	} );

	} );

})( window.jQuery );