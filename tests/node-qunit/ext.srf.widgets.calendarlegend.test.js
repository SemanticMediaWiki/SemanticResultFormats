'use strict';

const path = require( 'path' );

require( path.resolve( __dirname, '../../resources/ext.srf.api.results.js' ) );
require( path.resolve( __dirname, '../../formats/calendar/resources/ext.srf.widgets.calendarpane.js' ) );
require( path.resolve( __dirname, '../../formats/calendar/resources/ext.srf.widgets.calendarbutton.js' ) );
require( path.resolve( __dirname, '../../formats/calendar/resources/ext.srf.widgets.calendarlegend.js' ) );

QUnit.module( 'ext.srf.widgets.calendarlegend', () => {

	const list = {
		Meeting: { color: [ '#ff0000' ], filter: true },
		Holiday: { color: [ '#00ff00', '#00ff00' ], filter: false }
	};

	QUnit.test( 'position=top: creates the legend container inside the wrapper and renders the list', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc'
		} );

		const legend = context.find( '.srf-container > .srf-ui-legendList' );
		assert.strictEqual( legend.length, 1, 'the legend container was prepended inside the wrapper' );
		assert.true( legend.hasClass( 'top' ), 'the container got the "top" position class' );
		assert.true( legend.hasClass( 'ui-state-default' ), 'the container got the ui-state-default theme class (theme !== "fc")' );
		assert.strictEqual( legend.find( '.srf-legend-item' ).length, 2, 'one item was rendered per list entry' );
		assert.strictEqual( legend.find( '.srf-ui-legend-label' ).map( ( i, el ) => $( el ).text() ).get().join( ',' ), 'Meeting,Holiday', 'the item labels match the list keys' );
	} );

	QUnit.test( 'position=top with theme=fc: uses the fullcalendar "basic" theme class instead of ui-state-default', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc',
			theme: 'fc'
		} );

		const legend = context.find( '.srf-ui-legendList' );
		assert.true( legend.hasClass( 'basic' ), 'the fc theme class was applied' );
		assert.false( legend.hasClass( 'ui-state-default' ), 'the non-fc theme class was not applied' );
	} );

	QUnit.test( 'position=bottom: appends the legend container after any existing wrapper content', ( assert ) => {
		const context = $( '<div><div class="srf-container"><span class="existing"></span></div></div>' ).appendTo( document.body );

		context.calendarlegend( {
			position: 'bottom',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc'
		} );

		const wrapper = context.find( '.srf-container' );
		assert.true( wrapper.children().last().hasClass( 'srf-ui-legendList' ), 'the legend container was appended after existing content' );
		assert.true( wrapper.find( '.srf-ui-legendList' ).hasClass( 'bottom' ), 'the container got the "bottom" position class' );
	} );

	QUnit.test( 'itemSquare: renders one coloured square per unique color, substituting the default color for falsy entries', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: {
				Holiday: { color: [ '#00ff00', '#00ff00', '' ], filter: false }
			},
			defaultColor: '#ccc'
		} );

		const squares = context.find( '.srf-ui-legend-square' );
		assert.strictEqual( squares.length, 2, 'duplicate colors were deduplicated, leaving one square per unique color' );
		assert.strictEqual( squares.eq( 0 ).attr( 'style' ), 'background-color:#00ff00', 'the first square uses the item color' );
		assert.strictEqual( squares.eq( 1 ).attr( 'style' ), 'background-color:#ccc', 'a falsy color entry falls back to the default color' );
	} );

	QUnit.test( 'type=filter: renders a checked checkbox per item; clicking one invokes onFilter with its checked state and name', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );
		let onFilterResult = null;

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc',
			onFilter: ( event, checked, filter ) => {
				onFilterResult = { checked: checked, filter: filter };
			}
		} );

		context.calendarlegend( 'option', 'list', {
			type: 'filter',
			list: list
		} );

		const checkboxes = context.find( ':checkbox' );
		assert.strictEqual( checkboxes.length, 2, 'one checkbox was rendered per list entry' );
		assert.true( checkboxes.first().prop( 'checked' ), 'checkboxes are checked by default' );

		// checkboxes start checked (rendered with checked="checked"); a real/simulated
		// click toggles the underlying DOM state before the handler reads it.
		checkboxes.filter( '[name=Meeting]' ).trigger( 'click' );
		assert.deepEqual( onFilterResult, { checked: false, filter: 'Meeting' }, 'onFilter received the unchecked state and the filter name' );
	} );

	QUnit.test( 'type=legend (not "filter"): renders labels without checkboxes', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc'
		} );

		context.calendarlegend( 'option', 'list', {
			type: 'legend',
			list: list
		} );

		assert.strictEqual( context.find( ':checkbox' ).length, 0, 'no checkboxes were rendered for type=legend' );
		assert.strictEqual( context.find( '.srf-ui-legend-label' ).length, 2, 'the plain labels were still rendered' );
	} );

	QUnit.test( 'an empty list hides the legend container instead of rendering', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc'
		} );

		assert.notStrictEqual( context.find( '.srf-ui-legendList' ).css( 'display' ), 'none', 'the legend is visible while the list is non-empty' );

		context.calendarlegend( 'option', 'list', {
			list: {}
		} );

		assert.strictEqual( context.find( '.srf-ui-legendList' ).css( 'display' ), 'none', 'the legend container was hidden for an empty list' );
	} );

	QUnit.test( 'option( list ) refresh: removes the previous list items before rendering the new ones', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc'
		} );

		assert.strictEqual( context.find( '.srf-legend-item' ).length, 2, 'the initial list rendered 2 items' );

		context.calendarlegend( 'option', 'list', {
			list: { Meeting: { color: [ '#ff0000' ], filter: true } }
		} );

		assert.strictEqual( context.find( '.srf-legend-item' ).length, 1, 'the refreshed list replaced the previous items rather than appending to them' );
	} );

	QUnit.test( 'position=pane: renders the legend list inside the calendarpane portlet fieldset', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );

		// real usage (ext.srf.formats.eventcalendar.js) always initializes the
		// calendarpane widget on the wrapper before creating calendarlegend with
		// position: 'pane', since calendarlegend calls calendarpane( 'portlet', ... )
		// on it directly without initializing the widget itself.
		context.find( '.srf-container' ).calendarpane();

		context.calendarlegend( {
			position: 'pane',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc'
		} );

		const portlet = context.find( '#srf-calendarpane-srf-ui-legendList\\ pane' );
		assert.strictEqual( portlet.find( 'fieldset > legend' ).length, 1, 'the portlet fieldset with its legend title was created' );
		assert.strictEqual( portlet.find( '.srf-legend-item' ).length, 2, 'the legend items were inserted after the fieldset legend' );
	} );

	QUnit.test( 'destroy( { class } ) only removes matching elements within this widget instance', ( assert ) => {
		const context = $( '<div><div class="srf-container"></div></div>' ).appendTo( document.body );
		const unrelated = $( '<div class="unrelated-widget"></div>' ).appendTo( document.body );
		$( '<span class="shared-marker"></span>' ).appendTo( unrelated );

		context.calendarlegend( {
			position: 'top',
			wrapper: 'srf-container',
			list: list,
			defaultColor: '#ccc'
		} );
		$( '<span class="shared-marker"></span>' ).appendTo( context.find( '.srf-ui-legendList' ) );

		context.calendarlegend( 'destroy', { class: 'shared-marker' } );

		assert.strictEqual( unrelated.find( '.shared-marker' ).length, 1, 'destroy( { class } ) must not remove same-class elements belonging to an unrelated widget instance' );
	} );

} );
