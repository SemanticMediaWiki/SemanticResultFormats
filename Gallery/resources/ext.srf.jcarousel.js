/**
 * JavasSript for SRF Gallery Carousel module
 *
 * @licence: GNU GPL v3 or later
 * @author:  mwjames
 * 
 * @release: 0.1  
 */
 
jQuery(function($) {
 $(window).load(function() {
  // Display carousel only after js is loaded and ready otherwise display=none
  $( '#carousel').show();
   
  // Call the  jcarousel plug-in
  $( '#carousel' ).jcarousel({
      //circular carousel
    	wrap: 'circular',    	
    });
  });
});