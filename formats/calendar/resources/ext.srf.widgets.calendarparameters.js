/**
 * SRF JavaScript for srf.calendarparameters widget
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
/* global mw:true, smw:true, mediaWiki:true, semanticMediawiki:true, semanticFormats:true */
( function( $, mw, srf ) {
	'use strict';

	////////////////////////// PRIVATE OBJECTS ////////////////////////

	var html = mw.html;
	var util = new srf.util();

	////////////////////////// FACTORY METHOD ////////////////////////

	$.widget( 'srf.calendarparameters', {

		_init: function() {
			var self = this,
				el = self.element;
			return el;
		},

		/**
		 * Datepicker/date selection portlet input methods
		 *
		 * @since 1.9
		 */
		dateSelection: function( options ) {
			var self = this,
				el = self.element;

			// Date range input elements
			function dateInput( list, name ){
				return html.element( 'fieldset', {}, new html.Raw(
					html.element( 'legend', { }, mw.msg( 'srf-ui-common-label-daterange' ) ) +
					html.element( 'input', { 'type': 'radio', 'name': 'option', 'id': 'from', 'value': 'f' }, '' ) +
					html.element( 'label', { 'for' : 'from'}, 'From' ) +
					html.element( 'input', { 'type': 'radio', 'name': 'option', 'id': 'to', 'value': 't' }, '' ) +
					html.element( 'label', { 'for' : 'to'}, 'to' ) +
					html.element( 'span', { 'class': 'reset-link' }, 'Reset' ) + '<br />' +
					util.html.dropdown( {
						list: list,
						id: 'printouts',
						selectClass: 'printouts',
						browser: name,
						disabled: 'disabled'
					}	) +
					html.element( 'input', { 'id': 'mini-calendar-from', 'size': '8', 'readonly': 'readonly' }, '' ) +
					html.element( 'input', { 'id': 'mini-calendar-to', 'class': 'input-right', 'size': '8', 'readonly': 'readonly' }, '' )
				) );
			}

			self.calendar = $( html.element( 'div', { 'class' : 'datepicker' }, '' ) ).appendTo( el );
			self.input = $( html.element( 'div', { 'class' : 'options' }, new html.Raw( dateInput( options.list, options.browser ) ) ) ).appendTo( el );

			// Datepicker module
			self.calendar.datepicker( {
				inline: true,
				showOtherMonths: true,
				changeMonth: true,
				changeYear: true,
				dateFormat: options.dateFormat ,
				onChangeMonthYear: function( year, month, inst ) {
				// @note Something for later var date = new Date(); container.fullCalendar('gotoDate', year, month, date.getDate());
				},
				onSelect: function( dateText, inst){
					var date = new Date(dateText),
						option = $( 'input:radio[name=option]:checked', self.input ).val();

					// Use the stored index to find the related property
					var fromProperty = options.list[ $( '#mini-calendar-from', self.input ).data( 'property' ) ],
						toProperty = options.list[ $( '#mini-calendar-to', self.input ).data( 'property' ) ];

					if ( option === 'f' && fromProperty !== undefined ){
						$( '#mini-calendar-from', self.input ).val( dateText );  // updates the date
						$( 'input:radio[name=option]', self.input ).prop( 'checked', false ); // uncheck this option
						$( '#printouts', self.input ).prop( 'disabled', true ); // disables the dropdown
						self.calendar.datepicker( "option", { "maxDate": null, "minDate": null } ); // set datepicker min/max values to null
						// Use the onSelect callback
						if ( $.isFunction( options.onSelect ) ){
							options.onSelect( { fromProperty: fromProperty, fromDate: dateText } );
						}
					} else if( option === 't' && toProperty !== undefined  ){
						$( '#mini-calendar-to', self.input ).val( dateText );  // updates the date
						$( 'input:radio[name=option]', self.input ).prop( 'checked', false ); // uncheck this option
						$( '#printouts', self.input ).prop( 'disabled', true ); // disables the dropdown
						self.calendar.datepicker( "option", { "maxDate": null, "minDate": null } ); // set datepicker min/max values to null
						// Use the onSelect callback
						if ( $.isFunction( options.onSelect ) ){
							options.onSelect( { toProperty: toProperty, toDate: dateText } );
						}
					} else if ( option === undefined ) {
						// Use the gotoDate callback since in the current state
						// all actions are related to date navigation only
						if ( $.isFunction( options.gotoDate ) ){
							options.gotoDate( date );
						}
					}
					// @note Something for later var view = container.fullCalendar('getView'); if ( view.name == 'agendaWeek' ){ container.fullCalendar( 'changeView', 'agendaWeek' );} else { container.fullCalendar( 'changeView', 'agendaDay' ); }
				}
			} );

			// Handle events for when the printout dropdown is changed
			// This way we know which printout property belongs to the from or to
			// option and store it to the associated .data() element
			self.input.on( 'change', '#printouts', function( event ){
				var option = $( 'input:radio[name=option]:checked', self.input ).val();
				if ( option === 'f' ){
					$( '#mini-calendar-from', self.input ).data( 'property', $( this ).val() );
				} else if( option === 't' ){
					$( '#mini-calendar-to', self.input ).data( 'property', $( this ).val() );
				}
			} );

			// Handle events for the radio/reset in order to determine which
			// dropdown selection belongs to which from/to property and store those
			// information as .data() so that when the datepicker triggers a onSelect
			// event those data together with the date selected will be sent back to
			// the callback
			self.input.on( 'click', 'input:radio[name=option], .reset-link', function( event ){
				var fromDate = $( '#mini-calendar-from', self.input ).val(),
					toDate = $( '#mini-calendar-to', self.input ).val(),
					option = $( this ).val();

				if ( option === 'f' ){
					self.calendar.datepicker( "setDate", fromDate );
					self.calendar.datepicker( "option", { "maxDate": toDate, "minDate": null } );
					// Reset dropdown value to the stored property for this option
					$( '#printouts', self.input ).prop( 'disabled', false ).val( $( '#mini-calendar-from', self.input ).data( 'property' ) );
				} else if( option === 't' ){
					self.calendar.datepicker( "setDate", toDate );
					self.calendar.datepicker( "option", { "maxDate": null, "minDate": fromDate } );
					$( '#printouts', self.input ).prop( 'disabled', false ).val( $( '#mini-calendar-to', self.input ).data( 'property' ) );
				} else {
					// Reset all values and conditions related to date selection
					$( '#mini-calendar-from', self.input ).val( '' ).data( 'property', '' );
					$( '#mini-calendar-to', self.input ).val( '' ).data( 'property', '' );
					$( '#printouts', self.input ).val( '' ).prop( 'disabled', true );
					$( 'input:radio[name=option]', self.input ).prop( 'checked', false );
					self.calendar.datepicker( "option", { "maxDate": null, "minDate": null } );
					// Use the onReset callback
					if ( $.isFunction( options.onReset ) ){
						options.onReset( event );
					}
				}
			} );
		},

		/**
		 * Limit paramter
		 *
		 * @since 1.9
		 */
		limit: function( options ) {
			var self = this,
				el = self.element;

			function element(){
				return html.element( 'div', { 'class': 'limitparam' }, new html.Raw(
					html.element( 'div', { 'class' : 'parameter-section' }, 'Limit parameter' ) +  // @note mw.msg
					html.element( 'div', { 'class': 'label' }, 'Limit' ) +  // @note mw.msg
					html.element( 'span', { 'class': 'value' }, '' ) +
					html.element( 'span', { 'class': 'count' }, '' ) + '<br/>' +
					html.element( 'div', { 'class': 'slider' }, '' )
				) );
			}

			this.limit = $( element() ).appendTo( el );

			this.limit.find( '.slider' ).slider( {
				range: 'min',
				value: options.limit,
				min: 1,
				max: options.max,
				step: options.step,
				slide: function( event, ui ){
					self._limitParameterUpdate( { limit: self._limitConstrain( ui.value ) } );
				},
				change: function( event, ui ){
					if ( $.isFunction( options.change ) ){
						options.change( event, self._limitConstrain( ui.value ) );
					}
				}
			} );

			// Show initial limit/count
			this._limitParameterUpdate( { limit: options.limit, count: options.count } );
		},

		/**
		 * Limit/count value update
		 *
		 * @since 1.9
		 */
		_limitParameterUpdate: function( options ){
			var self = this,
				el = self.element;

			$( '.value', self.element ).text(  options.limit  );

			if ( options.count ){
				$( '.count', self.element ).text( '[ ' + options.count + ' ]' );
			} else {
				$( '.count', self.element ).text( '' );
			}
		},

		_limitConstrain: function( value ){
			return value > 1 ? value - 1 : value;
		},

		/**
		 * Start (earliest/latest) paramter portlet content
		 *
		 * @since 1.9
		 */
		eventStart: function( options ){
			var self = this,
				_BASE = self.widgetBaseClass + '-minmax',
				el = self.element;

			function element(){
				return html.element( 'div', { 'class': 'minmax' }, new html.Raw(
					html.element( 'div', { 'class' : 'parameter-section' }, 'Start parameter' ) + // @note mw.msg
					html.element( 'input', { 'type': 'radio', 'name': 'minmax', 'id': 'min', 'value': 'earliest'}, '' ) +
					html.element( 'label', { 'for' : 'min'}, 'Earliest' ) +  // @note mw.msg
					html.element( 'input', { 'type': 'radio', 'name': 'minmax', 'id': 'max', 'value': 'latest' }, '' ) +
					html.element( 'label', { 'for' : 'max'}, 'Latest' ) +  // @note mw.msg
					html.element( 'span', { 'class': 'reset-link' }, 'Reset' ) + '<br />' // @note mw.msg
				) );
			}

			// Add the element
			this.minmax = $( element() ).appendTo( el );
			self.minmax = this.minmax;

			// Set options default
			if ( options.type === 'earliest' ){
				$( '#min', self.minmax ).prop( 'checked', true );
			} else if ( options.type === 'latest' ){
				$( '#max', self.minmax ).prop( 'checked', true );
			}

			// Event handling
			self.minmax.on( 'change', '#min, #max' ,function ( event ) {
				if ( $.isFunction( options.change ) ){
					options.change( $( 'input:radio[name=minmax]:checked', self.minmax ).val() );
				}
			} )
			.on( 'click', '.reset-link' ,function ( event ) {
				$( 'input:radio[name=minmax]', self.minmax ).prop( 'checked', false );
				if ( $.isFunction( options.reset ) ){
					options.reset();
				}
			} );
		},


		/**
		 * colorFilter portlet content
		 *
		 * @since 1.9
		 */
		colorFilter: function( options ){
			var self = this,
				_BASE = self.widgetBaseClass + '-filter',
				el = self.element;

			function element( list, name ){
				return html.element( 'div', { 'class': 'filterparam' }, new html.Raw(
					html.element( 'div', { 'class' : 'parameter-section' }, 'Filter parameter' ) + // @note mw.msg
					html.element( 'input', { 'type': 'radio', 'name': 'filterType', 'id': 'legend', 'value': 'legend'}, '' ) +
					html.element( 'label', { 'for' : 'legend'}, 'Legend' ) +  // @note mw.msg
					html.element( 'input', { 'type': 'radio', 'name': 'filterType', 'id': 'filter', 'value': 'filter' }, '' ) +
					html.element( 'label', { 'for' : 'filter'}, 'Filter' ) +  // @note mw.msg
					html.element( 'span', { 'class': 'reset-link' }, 'Reset' ) + '<br />' +  // @note mw.msg
					util.html.dropdown( {
						list: list,
						id: 'filterproperty',
						selectClass: 'filter',
						browser: name,
						disabled: 'disabled'
					} )
				) );
			}

			// Add the element
			this.filterparam = $( element( options.list, options.browser ) ).appendTo( el );

			self.filterparam = this.filterparam;

			// Radio button, dropdown change handling
			self.filterparam.on( 'change', '#filterproperty, #legend, #filter' ,function ( event ) {
				var propertyIndex =  $( '#filterproperty', self.filterparam ).val(),
					filterType = $( 'input:radio[name=filterType]:checked', self.filterparam ).val();
					$( '#filterproperty', self.filterparam ).prop( 'disabled', false );
					if ( $.isFunction( options.onChange ) ){
						options.onChange( event, { propertyIndex: propertyIndex, filterType: filterType } );
					}
			} )
			.on( 'click', '.reset-link' ,function ( event ) {
				// Handle reset option (all values and conditions are set to null)
				if( $( '#filterproperty', self.filterparam ).val() && $( 'input:radio[name=filterType]:checked', self.filterparam ).val() ){
					$( '#filterproperty', self.filterparam ).val( '' ).prop( 'disabled', true );
					$( 'input:radio[name=filterType]', self.filterparam ).prop( 'checked', false );
					if ( $.isFunction( options.onReset ) ){
						options.onReset( event );
					}
				}
			} );
		},

		_setOption: function ( name, value ) {
			switch( name ){
			case 'limit':
				this._limitParameterUpdate( value );
				break;
			case 'colorFilter':
				value.hide ? this.filterparam.hide() : this.filterparam.show();
				break;
			case 'eventStart':
				this.eventStart( value );
				break;
			}
			$.Widget.prototype._setOption.apply( this, arguments );
		},

		/**
		 * Remove objects
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