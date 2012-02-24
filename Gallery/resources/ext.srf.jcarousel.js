/**
 * JavasSript for SRF Gallery Carousel module
 *
 * @licence: GNU GPL v3 or later
 * @author:  mwjames
 * 
 * @release: 0.1.3  
 */

(function( $ ) {

	$( document ).ready( function() {
		
		// Bind individual elements containing class jcarousel as the plug-in 
		// requires different id's  
		$(".jcarousel").each(function() {
			var $this = $( this );
			
			// Display carousel only after js is loaded and is ready otherwise display=none
			$this.show();
	
			// Call the  jcarousel plug-in
			$this.jcarousel( {			
				scroll:  parseInt( $this.attr( 'scroll' ), 10 ), // Number of items to be scrolled
				visible: parseInt( $this.attr( 'visible' ), 10 ), // calculated and set visible elements
				wrap: $this.attr( 'wrap' ), // Options are "first", "last", "both" or "circular" 
				vertical: $this.attr( 'vertical' ) === 'true', // Whether the carousel appears in horizontal or vertical orientation
				rtl: $this.attr( 'rtl' ) === 'true' // Directionality 
			} );
		} );

	} );

})( window.jQuery );