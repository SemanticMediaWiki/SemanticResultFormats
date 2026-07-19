'use strict';

// ext.srf.filtered.select.js resolves its own require('jquery') against
// formats/filtered/node_modules/jquery (a separate copy from the repo root's),
// so it must be loaded against that same jQuery instance or select2 registers
// on a jQuery.fn the rest of the test never sees.
const selectJsPath = require.resolve( '../../formats/filtered/resources/js/ext.srf.filtered.select.js' );
const select2JQuery = require( require.resolve( 'jquery', { paths: [ selectJsPath ] } ) );
const select2Export = require( selectJsPath );
// Select2's UMD wrapper changed between 4.0.3 and later releases: newer
// builds export a factory (module.exports = function (root, jQuery) {...})
// instead of attaching to jQuery.fn as a require() side effect. In the
// browser (MediaWiki's ResourceLoader <script> load) this never matters —
// only the "Browser globals: factory(jQuery)" branch of the UMD wrapper runs —
// but under Node's CommonJS branch the factory must be invoked explicitly.
if ( typeof select2Export === 'function' ) {
	select2Export( window, select2JQuery );
}

QUnit.module( 'ext.srf.filtered select2 integration', {
	// select2 is registered on a separate jQuery copy (see above) — swap it in
	// for this module only, so other test files keep using the root jQuery
	// instance (and its own registered plugins, e.g. blockUI) undisturbed.
	beforeEach: () => {
		global.$ = global.jQuery = select2JQuery;
	},
	afterEach: () => {
		global.$ = global.jQuery = require( 'jquery' );
	}
}, () => {

	QUnit.test( 'select2 attaches to the shared jQuery instance', ( assert ) => {
		assert.strictEqual( typeof $.fn.select2, 'function', '$.fn.select2 is registered' );
	} );

	QUnit.test( 'select2 initializes on a <select> with the options used by ValueFilter', ( assert ) => {
		const $select = $( '<select class="filtered-value-select" style="width: 100%;">' ).appendTo( document.body );

		const data = [
			{ id: 'Foo', text: 'Foo' },
			{ id: 'Bar', text: 'Bar' }
		];

		$select.select2( {
			multiple: true,
			placeholder: 'Select a value',
			data: data
		} );

		assert.true( $select.hasClass( 'select2-hidden-accessible' ), 'select2 markup is applied to the source select' );
		assert.strictEqual( $( '.select2-container' ).length, 1, 'a select2 container is rendered' );
	} );

	QUnit.test( 'select2:select and select2:unselect fire with the option id in e.params.data.id', ( assert ) => {
		// ValueFilter.getSelected2Control() listens for these two DOM events and reads
		// e.params.data.id to update the filter — this is the contract that must not break.
		const $select = $( '<select class="filtered-value-select" multiple>' ).appendTo( document.body );

		$select.select2( {
			multiple: true,
			data: [
				{ id: 'Foo', text: 'Foo' },
				{ id: 'Bar', text: 'Bar' }
			]
		} );

		const selected = [];
		$select.on( 'select2:select', ( e ) => selected.push( [ 'select', e.params.data.id ] ) );
		$select.on( 'select2:unselect', ( e ) => selected.push( [ 'unselect', e.params.data.id ] ) );

		// Selecting/unselecting is driven by UI interaction inside select2's dropdown,
		// which jsdom does not render; the public Select2.prototype.trigger() API is what
		// select2's own result-click handlers call internally, so it exercises the same path.
		const select2Instance = $select.data( 'select2' );
		select2Instance.trigger( 'select', { data: { id: 'Foo', text: 'Foo' } } );
		assert.deepEqual( selected, [ [ 'select', 'Foo' ] ], 'select2:select fires with the chosen option id' );

		select2Instance.trigger( 'unselect', { data: { id: 'Foo', text: 'Foo' } } );
		assert.deepEqual(
			selected,
			[ [ 'select', 'Foo' ], [ 'unselect', 'Foo' ] ],
			'select2:unselect fires with the previously chosen option id'
		);
	} );

} );
