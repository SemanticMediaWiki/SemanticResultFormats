/**
 * SRF JavaScript for srf.calendarlegend widget
 *
 * @since 1.9
 * @release 0.1
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	/* Private methods and objects */

	/**
	 * Helper objects
	 *
	 * @since 1.9
	 *
	 * @ignore
	 * @private
	 * @static
	 */
	var html = mw.html,
		api = new srf.api.util(),
		tooltip = new smw.util.tooltip();

	/**
	 * $.widget function
	 *
	 * @since 1.9
	 * @type Object
	 */
	$.widget( 'srf.calendarlegend', {
		options:{
			_BASE : 'srf-ui-legendList'
		},

		// Creates the outer frame which depends on the wrapper used to select its position
		// The tooltip will initiate only if the _setOption detects a list{} object
		// and initiates _refreshTooltip which ensures that qtip always holds the list{}
		// content
		_create: function() {
			var self = this,
				el = self.element;

			if ( self.options.position === 'top' ) {
				this.legend = $( html.element( 'div',{ 'class': self.options._BASE } , '') ).prependTo( $( '.' + self.options.wrapper , el ) );
				this.legend.addClass( self.options.theme !== 'fc' ? 'top ui-state-default' : 'top basic' );
			} else if ( self.options.position === 'bottom' ) {
				this.legend = $( html.element( 'div',{ 'class': self.options._BASE } , '') ).appendTo( $( '.' + self.options.wrapper , el ) );
				this.legend.addClass( self.options.theme !== 'fc' ? 'bottom ui-state-default' : 'bottom basic' );
			} else if ( self.options.position === 'pane' ) {
				el.find( '.' + self.options.wrapper ).calendarpane( 'portlet', {
					'class'  : self.options._BASE + ' pane',
					'title'  : 'Legend',
					'fieldset': true
				} );
			}
			self.legend = this.legend;
			this._refreshList( self.options.list );
		},

		// Something has to be done here, the current implementation
		// could be improved but foremost it works therefore I don't bother
		_refreshTooltip: function( list ){
			var self = this,
				el = self.element;

			// Add button
			el.find( '.fc-header-right > .fc-button-today' ).next().calendarbutton( {
				'class': 'tooltip',
				'icon' : 'ui-icon ui-icon-gear',
				'title':  '',
				'theme': this.options.theme,
				'right': false,
				'tooltip': true
			} );

			// Add tooltip instance for legend/filters
			tooltip.add( {
				contextClass: 'srf-ui-legend-tooltip',
				contentClass: 'srf-ui-legend-tooltip-content',
				targetClass : 'srf-calendarbutton-tooltip',
				context: el,
				title: 'Legend',
				type: 'info',
				//event: 'click',
				button: true,
				content: list
			} );

		},

		// Generate the filter/legend content
		_refreshList:function( options ){
			var self = this,
				el = self.element;

			// Returns coloured square element(s)
			function itemSquare( colors, defaultColor ){
				var element = '';
					$.each( api.array.unique( colors ), function( i, value ) {
						element = element + '<span class="srf-ui-legend-square" style="background-color:' +	( value || defaultColor ) + '";></span>';
					} );
				return element;
			}

			// Returns a list of legend/filter elements
			function itemList( list, type, defaultColor ) {
				if ( list !== undefined ) {
					var elements = [];

					$.each( list, function( key, item ) {
						if ( key !== '' ) {
							elements.push(
								( type === 'filter' ? '<input type="checkbox" checked="checked" name="' + key + '"/>' : '' ) +
								itemSquare( item.color, defaultColor ) +  '<span class="srf-ui-legend-label">' + key + '</span>'
							);
						}
					} );
					return '<ul><li class="srf-legend-item">'+ elements.join('</li><li class="srf-legend-item">') + '</li></ul>';
				}
			}

			// Only do a refresh for a non-empty list otherwise hide
			if ( !$.isEmptyObject( options.list ) ) {
				$( '.' + this.options._BASE , this.element ).show();

				if ( this.options.position === 'pane' ){
					this.legend = $( itemList( options.list, options.type, this.options.defaultColor ) ).insertAfter( $( '.' + self.options._BASE + '> fieldset > legend' , el ) );
				} else if ( this.options.position === 'tooltip' ){
					this._refreshTooltip( itemList( options.list, options.type, this.options.defaultColor ) );
					self.legend = $( '.srf-ui-legend-tooltip-content', el );
				} else {
					this.legend = $( itemList( options.list, options.type, this.options.defaultColor ) ).prependTo( $( '.' + self.options._BASE , el ) );
				}

				// Enable a callback to manipulate the event source in accordance
				// with its assigned/unassigned filter
				// returns true/false status
				self.legend.on( 'click', ':checkbox', function( event ){
					var state = $( this ),
						filter = state.attr( 'name' );

					if ( $.isFunction( self.options.onFilter ) ){
						self.options.onFilter( event, state.is( ':checked' ), filter );
					}
				} );
			} else {
				$( '.' + this.options._BASE , this.element ).hide();
			}
		},

		// Remove list items from the main element
		_remove: function(){
			var self = this,
				el = self.element;

			if ( this.options.position === 'pane' ){
				$( '.' + this.options._BASE + ' > fieldset > ul' , this.element ).remove();
			} else if ( this.options.position === 'tooltip' ){
				el.find( '.fc-header-right' ).calendarbutton( 'destroy', { 'class' : 'tooltip' } );
				el.find( '.srf-ui-legend-tooltip' ).remove();
			}	else{
				$( '.' + this.options._BASE + ' > ul' , this.element ).remove();
			}
		},

		// Receives update information from outside
		_setOption: function ( name, value ) {
			if( name === 'list') {
				this._remove( );
				this._refreshList( value );
			}
			$.Widget.prototype._setOption.apply( this, arguments );
		},

		/**
		 * Removes instance objects
		 *
		 * @since 1.9
		 * @var options
		 */
		destroy: function( options ) {
			if ( options['class'] ){
				$( '.' + options['class'] , this.pane ).remove();
			} else{
				$.Widget.prototype.destroy.apply( this );
			}
		}
	} );
} )( jQuery, mediaWiki, semanticFormats );