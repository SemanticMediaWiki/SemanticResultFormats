/**
 * File holding the slideshow plugin
 * 
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

(function( $ ){

	$.fn.slideshow = function( options ) {
	  
		var results       = options.data[0];
		var template      = options.data[1];
		var delay         = options.data[2];
		var height        = options.data[3];
		var width         = options.data[4];
		var navButtons    = options.data[5];
		var effect        = options.data[6];
		var printrequests = options.data[7];
	
		var requestedIndex = 0;
		var timeout;
	
		// Build widget
		
		var targetDiv = jQuery('<div class="slideshow-viewport" style="height: ' + height + '; width: ' + width + ';" >');
	
	
		this
		.width( width )
		.append( targetDiv );
		
		if ( navButtons ) {
			var navLeft = jQuery('<div class="slideshow-nav-left" >&lt;</div>');
			var navRight = jQuery('<div class="slideshow-nav-right" >&gt;</div>');
			
			
			navLeft.click(function(){
				switchTo(getPreviousIndex( requestedIndex), false );
			});
			
			navRight.click(function(){
				switchTo(getNextIndex( requestedIndex) );
			});
			
			this
			.append( navLeft )
			.append( navRight );
		}
	
		// start the show
		switchTo( 0, true, 0 );


		function switchTo( index, moveForward, speed ) {
			
			var animateForward, animateBackward;
			
			switch ( effect ) {
				case 'slide left':
					animateForward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'left': (targetDiv.outerWidth() * 1.1) + 'px'})
						.animate({'left': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'left': (-oldWrapper.outerWidth()) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					animateBackward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'left': (-targetDiv.outerWidth() * 1.1) + 'px'})
						.animate({'left': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'left': (oldWrapper.outerWidth()) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					break;
				case 'slide right':
					animateForward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'left': (-newWrapper.outerWidth()) + 'px'})
						.animate({'left': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'left': (targetDiv.outerWidth() * 1.1) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					animateBackward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'left': (newWrapper.outerWidth()) + 'px'})
						.animate({'left': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'left': (-targetDiv.outerWidth() * 1.1) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					break;
				case 'slide up':
					animateForward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'top': (targetDiv.outerHeight() * 1.1) + 'px'})
						.animate({'top': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'top': (-oldWrapper.outerHeight()) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					animateBackward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'top': (-targetDiv.outerHeight() * 1.1) + 'px'})
						.animate({'top': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'top': (oldWrapper.outerHeight()) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					break;
				case 'slide down':
					animateForward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'top': (-newWrapper.outerHeight()) + 'px'})
						.animate({'top': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'top': (targetDiv.outerHeight() * 1.1) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					animateBackward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'top': (newWrapper.outerHeight()) + 'px'})
						.animate({'top': '0px'}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'top': (-targetDiv.outerHeight() * 1.1) + 'px'}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}} );
					}
					
					break;
				case 'fade':
					animateForward = animateBackward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )

						newWrapper.children()
						.css({'opacity': 0})
						.animate({'opacity': 1}, {duration: speed, easing:'linear', queue: true} );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.animate({'opacity': 0}, {duration: speed, easing:'linear', queue: true, complete: function(){
							jQuery(this).parent().detach();
						}
						} );
					}
					
					break;
				case 'hide':
					animateForward = animateBackward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						// insert next result into document
						targetDiv.append( newWrapper );

						// need to set dimensions, might have changed since last cycle
						newWrapper.children()
						.width( targetDiv.width() )
						.height( targetDiv.height() );
						
						// slide out old element, then detach it
						oldWrapper.children()
						.width( targetDiv.width() )
						.height( targetDiv.height() );
						
						newWrapper
						.width( 0 )
						.height( 0 )
						.css({'opacity': 0});
						
						
						if ( oldWrapper.length > 0 ) {
							oldWrapper
							.animate({ 'opacity': 0, 'width': 0, 'height': 0 },
							{ duration: speed, easing:'linear', queue: true, 
								complete: function(){
									jQuery(this).detach();

									newWrapper
									.animate({ 'opacity': 1, 'width': targetDiv.width(), 'height': targetDiv.height()}, {
										duration: speed, easing:'linear', queue: true
									} );
								}
							} );
						} else {
							newWrapper
							.animate({ 'opacity': 1, 'width': targetDiv.width(), 'height': targetDiv.height()}, {
								duration: speed, easing:'linear', queue: true
							} );
						}
					}
					
					break;
				default:
					animateForward = animateBackward = function ( targetDiv, oldWrapper, newWrapper, speed ) {
						
						// need to set dimensions, might have changed since last cycle
						// insert next result into document
						newWrapper
						.width( targetDiv.width() )
						.height( targetDiv.height() )
						.appendTo( targetDiv );

						// slide out old element, then detach it
						oldWrapper.detach();
					}
					
					break;
			}

			// store old index, set new requested index
			var oldIndex = requestedIndex
			requestedIndex = index;
		
			// set speed to default if not given as param
			if ( typeof speed === 'undefined' ) {
				speed = 'slow';
			}
			
			// set speed to default if not given as param
			if ( typeof moveForward === 'undefined' ) {
				moveForward = true;
			}
			
			// requested result item not available and not loading
			if ( results[index].length === 1) {
				// fetch it and switch immediately
				fetchResult( index, moveForward, speed );			
			} else {
				// requested result item available?
				if ( typeof results[index][1] !== "boolean" ) {

					// find next result
					var newWrapper = results[index][1];
					
					// if newWrapper still attached, detach it first
					if ( newWrapper.parent().length > 0) {
						newWrapper.detach();
					}

					// find current result (or sometimes current results)
					var oldWrapper = targetDiv.children();
					
					// set start position for animation and slide in
					if ( moveForward ) {
						animateForward( targetDiv, oldWrapper, newWrapper, speed);
					} else {
						animateBackward( targetDiv, oldWrapper, newWrapper, speed);
					}

					// calculate index of next result item
					var nextIndex = index + 1;
					if ( nextIndex >= results.length ) {
						nextIndex = 0;
					} 

					// preload of next item necessary?
					if ( results[nextIndex].length === 1 ) {
						fetchResult( nextIndex );
					}

					restartTimer();

				} // just wait until loading finished
			}
		}

		function restartTimer(){
			
			// if necessary clear runnning timeout
			if ( typeof timeout !== 'undefined' ) {
				window.clearTimeout( timeout );
				delete timeout;
			}
			
			if ( delay > 0 ) {
				
				// wait some time then switch to next result item
				timeout = window.setTimeout( function(){

					// switch to next item
					switchTo( getNextIndex( requestedIndex ) );

				}, delay);
			}
		}

		function fetchResult( index, moveRight, speed ) {
		
			// mark as loading
			results[index][1] = true;
					
			sajax_request_type = 'POST';
		
			// perform AJAX call
			sajax_do_call( 'SRFSlideShow::handleGetResult', [ results[index][0], template, printrequests ], function( ajaxHeader ){
			
				// create element from returned text
				var element = jQuery( '<div class="slideshow-element">' + ajaxHeader.responseText + '</div>' );
				
				// initialize style parameters
				element
				.css({'position':'relative', 'top':0, 'left':0});
				
				// create wrapper, basically a mask to hide the overflow when animating
				var wrapper = jQuery( '<div>' );
				
				// initialize style parameters and insert element
				wrapper
				.css({'position':'absolute', 'top':0, 'left':0, 'overflow':'hidden'})
				.append(element);
				
				// store the wrapper with the attached element
				results[index][1] = wrapper;

				// is the loaded item the requested one, switch to it immediately
				if ( requestedIndex === index ) {
					switchTo( requestedIndex, moveRight, speed );
				}

			});

		}
		
		function getNextIndex( index ) {
			
			// increase element index
			index++;
			if ( index >= results.length ) {
				return 0;
			} else {
				return index;
			}

		}

		function getPreviousIndex( index ) {
			
			// decrease element index
			if ( index == 0 ) {
				return results.length - 1;
			} else {
				return index-1
			};

		}


	};
})( jQuery );

// initialize all slideshows
for ( id in srf_slideshow ) {
	jQuery('#' + id).slideshow( {
		'id' : id,
		'data' : srf_slideshow[ id ]
	});
}
