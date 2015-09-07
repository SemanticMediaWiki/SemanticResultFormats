/**
 * File holding the table-view plugin
 *
 * For this plugin to work, the filtered plugin needs to be available first.
 *
 * @author Hans-Jürgen Hartl
 * @file
 * @ingroup SemanticResultFormats
 */

(function ($) {

	var animationSpeed = 600;
	var viewIsVisible = false;
	
	var methods = {
	
		init: function( args ){
			return this;
		},

		alert: function(){
			alert('Table View!');
			return this;
		},

		updateItem: function(params){

			var view = this.find('.filtered-views').find('.filtered-table');

			if ( viewIsVisible ) {

				if ( params.visible ) { // show
					view.find('tr#' + params.item +'.filtered-table-item' ).slideDown( animationSpeed, function(){
						jQuery(this).fadeTo(animationSpeed, 1);
					} );
				} else { // hide
					view.find('tr#' + params.item ).fadeTo( animationSpeed, 0, function(){
						jQuery(this).slideUp(animationSpeed);
					}  );
				}
			} else {
				if ( params.visible ) {
					view.find('tr#' + params.item +'.filtered-table-item').css({'display': '', 'opacity': 1}); //show
				} else {
					view.find('tr#' + params.item +'.filtered-table-item').css({'display': 'none', 'opacity': 0}); //hide
				}
			}
			return this;
		},

		updateAllItems: function(){

			var filtered = this;
			var items = this.find('.filtered-views').find('.filtered-table').find('.filtered-table-item');
			
			var visibleItems = jQuery([]);
			var hiddenItems = jQuery([]);

			for ( var i = 0; i < items.length; ++i ) {
				
				if ( filtered.filtered( 'isVisible', items[i].id ) ) {
					visibleItems = visibleItems.add(items[i]);
				} else {
					hiddenItems = hiddenItems.add(items[i]);
				}

			}
			
			if ( viewIsVisible ) {
				
				visibleItems.slideDown( animationSpeed, function(){
						jQuery(this).fadeTo(animationSpeed, 1);
					} );
					
				hiddenItems.fadeTo( animationSpeed, 0, function(){
						jQuery(this).slideUp(animationSpeed);
					}  );
					
			} else {
				visibleItems.css({'display': '', 'opacity': 1}); //show
				hiddenItems.css({'display': 'none', 'opacity': 0}); //hide
			}

		},

		show:  function() {
			jQuery(this).show();
			viewIsVisible = true;
		},

		hide:  function() {
			jQuery(this).hide();
			viewIsVisible = false;
		}

	};

	tableView = function( method ) {

		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.filtered.tableView' );
		}


	};

	// attach ListView to all Filtered query printers
	// let them sort out, if ListView is actually applicable to them
	jQuery('.filtered').filtered('attachView', 'table', tableView );

})(jQuery);

