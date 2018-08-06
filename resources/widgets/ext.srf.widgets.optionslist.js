/**
 * SRF JavaScript srf.optionslist widget
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

	/**
	 * Helper method
	 *
	 * @type object
	 */
	var html = mw.html;

	/**
	 * $.widget factory method
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 */
	$.widget( 'srf.optionslist', {

		/**
		 * Internal method that runs once during initialization
		 *
		 * @private
		 * @return {object}
		 */
		_init: function() {
			var self = this,
				el = self.element;
			return el;
		},

		/**
		 * Create checkbox elements from an array
		 *
		 * @return object
		 */
		checklist: function( options ) {
			var self = this,
				el = self.element;

			// Returns a list of elements
			function checkList( list, checkListClass ) {
				if ( list !== undefined ) {
					var elements = [];
					$.each( list, function( key, item ) {
						if ( key !== '' ) {
							key = $.type( item ) === 'object' ? item.key : key;
							item = $.type( item ) === 'object' ? item.label : item;
							elements.push(
								html.element( 'input', {
									'type': 'checkbox',
									'checked': 'checked',
									'id': item,
									'name': item,
									'value': key
								}, item )
							);
						}
					} );
					return '<ul><li class="' + checkListClass + '-item">'+ elements.join('</li><li class="' + checkListClass + '-item">') + '</li></ul>';
				}

			}

			this.checkList = $( checkList( options.list, options['class'] ) ).appendTo( el );

			// Create element and the bind click event
			self.checkList = this.checkList
			.on( 'click', ':checkbox', function( event ){
				var that = $( this );
				if ( $.isFunction( options.click ) ){
					options.click( event, {
						checked: that.is( ':checked' ),
						value: that.attr( 'value' ),
						name: that.attr( 'name' )
					} )
				}
			} );

			return options.show || options.show === undefined ?  self.checkList.show() : self.checkList.hide();
		},

		/**
		 * Create select elements from an array
		 *
		 * @since  1.9
		 *
		 * @param {array} options
		 *
		 * @return object
		 */
		selectlist: function( options ) {
			var self = this,
				el = self.element;

			// Returns a list of elements
			function selectList( list ) {
				if ( list !== undefined ) {
					var dropdown = '';
					$.each( list, function( key, item ) {
						key = $.type( item ) === 'object' ? item.key : key;
						item = $.type( item ) === 'object' ? item.label : item;
						dropdown = dropdown + html.element( 'option', {
							'value': key,
							'selected': options.selectedAll
						},item );
					} );

					return html.element( 'select', {
						'id': options['class'],
						'class': options['class'],
						'multiple': options.multiple || false,
						'size': options.multiple ? ( list.length > 5 ? 5 : list.length ) : 1
						}, new html.Raw( ( options.null ? html.element( 'option', { }, '' ) : '' ) + dropdown )
					);
				}
			}

			// Create element and bind the click event
			this.selectList = $( selectList( options.list ) ).appendTo( el );
			self.selectList = this.selectList
			.on( 'change', function( event ){
				var that = $( this );
				if ( $.isFunction( options.change ) ){
					options.change( event, {
						selected: that.is( ':selected' ),
						value: that.val()
					} );
				}
			} );

			return options.show || options.show === undefined ?  self.selectList.show() : self.selectList.hide();
		},

		/**
		 * Remove objects
		 *
		 * @since 1.9
		 */
		destroy: function() {
			$.Widget.prototype.destroy.apply( this );
		}
	} );
} )( jQuery, mediaWiki, semanticFormats );