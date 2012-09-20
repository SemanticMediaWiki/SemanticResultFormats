/**
 * JavaScript for SRF ListWidget module
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @since: 1.8
 *
 * jshint checked
 *
 * @release: 0.3
 */
( function( $ ) {
	"use strict";

	/*global mw:true*/

	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	$.fn.srfListwidget = function() {
		var widgetID = this.find( '.container' ).attr( 'id' ),
			listType   = this.data( 'listtype' ),
			widget     = this.data( 'widget' ),
			pageitems  = this.data( 'pageitems' ),
			noMatch    = mw.msg( 'srf-module-nomatch' );

		// Update list with ID and class
		this.find( listType ).attr( { 'id': widgetID, 'class' : widget + '-container' } );

		// Navigation class
		var navClass = widget === 'pagination' ? '.container' : '.' + widget + '-container';

		// Navigation element
		var navigation  = '<div id="' + widgetID + '-nav" class="srf-listwidget-navigation"></div>';
		this.find( navClass ).before( navigation );

		if ( widget === 'pagination' ) {
			// Pagination widget
			this.pajinate( {
				items_per_page : pageitems,
				item_container_id : '.pagination-container',
				nav_panel_id : '#' + widgetID + '-nav'
			} );

		} else if ( widget === 'menu' ) {
			// Alphabet menu navigation
			this.find( listType ).listmenu( {
					includeAll: false,
					includeOther: true,
					showCounts: false,
					cols: { count:3, gutter:35 },
					noMatchText: noMatch
			} );
		} else {
			// Alphabet list navigation
			this.find( listType ).listnav( {
				includeAll: false,
				includeOther: true,
				noMatchText: noMatch
			} );
		}

		// Release display
		this.find( '.srf-processing' ).hide();
		this.find( '.srf-listwidget-navigation' ).show();
		this.find( '.container' ).show();
	};

	$(document).ready( function() {
		$( '.srf-listwidget' ).each( function() {
			$( this ).srfListwidget();
		} );
	} ); // end $(document).ready
} )( window.jQuery );