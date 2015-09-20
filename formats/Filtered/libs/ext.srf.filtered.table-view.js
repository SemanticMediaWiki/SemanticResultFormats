/**
 * File holding the table-view plugin
 *
 * For this plugin to work, the filtered plugin needs to be available first.
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

(function ( jQuery ) {

	var animationSpeed = 600;
	var viewIsVisible = false;

	var methods = {

		init: function( args ){
			var table = this.find( ".filtered-table > table" );

			if ( table.css( "border-collapse" ) == "collapse" ) {

				var item = table.find( '.filtered-table-item td' ).first();

				table.css( {
					"border-collapse": "separate",
					"border-spacing" : 0,
					"border-top" : "0 none",
					"border-left" : "0 none",

					"border-bottom-width" : item.css( "border-bottom-width" ),
					"border-bottom-style" : item.css( "border-bottom-style" ),
					"border-bottom-color" : item.css( "border-bottom-color" ),

					"border-right-width" : item.css( "border-right-width" ),
					"border-right-style" : item.css( "border-right-style" ),
					"border-right-color" : item.css( "border-right-color" )
				});


				var cells = table.find( 'td, th' );

				cells.css( {
					"border-bottom" : "0 none",
					"border-right" : "0 none"
				});
			}
			return this;
		},

		alert: function(){
			alert('Table View!');
			return this;
		},

		hideItem: function( item ) {

			var tableCells = item.children( 'td' );

			tableCells.each( function()
			{
				var $this = jQuery( this );

				var widths = {
					'border-top-width': $this.css( 'border-top-width' ),
					'border-bottom-width': $this.css( 'border-bottom-width' ),
					'padding-top': $this.css( 'padding-top' ),
					'padding-bottom': $this.css( 'padding-bottom' ),
					'height' : $this.height()
				};

				$this
					.data('widths', widths )
					.animate( {
						'border-top-width': 0,
						'border-bottom-width': 0,
						'padding-top': 0,
						'padding-bottom': 0,
						'height' : 0
					}, viewIsVisible?animationSpeed:0 );
			});
		},

		showItem: function( item ) {

			var tableCells = item.children( 'td' );

			tableCells.each( function() {

				var $this = jQuery( this );
				var widths = $this.data( 'widths' );

				// if we never hid the item, nothing will be stored.
				// take whatever is preset by applicable document styles
				if ( widths === undefined ) {
					widths = {
						'border-top-width': $this.css( 'border-top-width' ),
						'border-bottom-width': $this.css( 'border-bottom-width' ),
						'padding-top': $this.css( 'padding-top' ),
						'padding-bottom': $this.css( 'padding-bottom' ),
						'height' : $this.height()
					};
				}

				// if the view was hidden, the item height will have been null, when the item was hidden
				// set auto height, take height applied by layout engine, then hide the item again
				if ( widths.height === 0 ) {
					$this.height( 'auto' );
					widths.height = $this.height();
					$this.height( 0 );
				}

				$this.animate( widths, viewIsVisible?animationSpeed:0, 'swing', function() { $this.height( 'auto' ); } );

			});
		},

		updateItem: function( params ){

			var view = this.find('.filtered-views').find('.filtered-table');

			var item = view.find('.' + params.item );

			if ( params.visible ) { // show
				methods.showItem( item );
			} else { // hide
				methods.hideItem( item );
			}

			return this;
		},

		updateAllItems: function(){

			var filtered = this;
			var items = this.find('.filtered-views').find('.filtered-table').find( 'tr.filtered-table-item' );

			for ( var i = 0; i < items.length; ++i ) {

				if ( filtered.filtered( 'isVisible', items[i].id ) ) {
					methods.showItem( jQuery( items[i] ) );
				} else {
					methods.hideItem( jQuery( items[i] ) );
				}

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

	var tableView = function( method ) {

		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.filtered.tableView' );
		}


	};

	// attach TableView to all Filtered query printers
	// let them sort out, if TableView is actually applicable to them
	jQuery('.filtered').filtered('attachView', 'table', tableView );

})(jQuery);

