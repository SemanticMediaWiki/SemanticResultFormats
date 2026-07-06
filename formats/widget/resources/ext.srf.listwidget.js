/**
 * JavaScript for SRF ListWidget module
 *
 * @param $
 * @param mw
 * @param srf
 * @see http://www.semantic-mediawiki.org/wiki/Help:Listwidget format
 *
 * @since 1.8
 * @release 0.3
 *
 * @licence GPL-2.0-or-later
 * @author mwjames
 */
( function ( $, mw, srf ) {
	'use strict';

	/* global mw:true */

	// //////////////////////// PRIVATE METHODS ////////////////////////

	const util = new srf.util();

	// //////////////////////// PUBLIC METHODS ////////////////////////

	$.fn.srfListwidget = function () {
		const widgetID = this.find( '.listwidget-container' ).attr( 'id' ),
			listType = this.data( 'listtype' ),
			widget = this.data( 'widget' ),
			pageitems = this.data( 'pageitems' ),
			noMatch = mw.msg( 'srf-module-nomatch' );

		// Update list with ID and class
		this.find( listType ).attr( { id: widgetID, class: widget + '-container' } );

		// Navigation class
		const navClass = widget === 'pagination' ? '.listwidget-container' : '.' + widget + '-container';

		// Navigation element
		const navigation = '<div id="' + widgetID + '-nav" class="srf-listwidget-navigation"></div>';
		this.find( navClass ).before( navigation );

		if ( widget === 'pagination' ) {
			// Pagination widget
			this.pajinate( {
				items_per_page: pageitems,
				item_container_id: '.pagination-container',
				nav_panel_id: '#' + widgetID + '-nav'
			} );

		} else if ( widget === 'menu' ) {
			// Alphabet menu navigation
			this.find( listType ).listmenu( {
				includeAll: false,
				includeOther: true,
				showCounts: false,
				cols: { count: 3, gutter: 35 },
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
		util.spinner.hide( { context: this } );
		this.find( '.srf-listwidget-navigation' ).show();
		this.find( '.listwidget-container' ).show();
	};

	$( document ).ready( () => {
		$( '.srf-listwidget' ).each( function () {
			$( this ).srfListwidget();
		} );
	} );
}( jQuery, mediaWiki, semanticFormats ) );
