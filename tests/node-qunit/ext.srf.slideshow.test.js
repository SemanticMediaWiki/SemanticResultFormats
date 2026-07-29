'use strict';

const path = require( 'path' );
const sinon = require( 'sinon' );
const modulePath = path.resolve( __dirname, '../../formats/slideshow/resources/ext.srf.slideshow.js' );

// the module is wrapped as `( function ( $, mw ) { ... } )( jQuery, mediaWiki )`:
// it closes over whatever `global.mediaWiki` was at require() time, not a live
// `global.mw` reference. setup.js's afterEach hook replaces `global.mw` with a
// brand new object between tests, so the module must be required exactly once
// (capturing one `mw` instance) and that same instance mutated in place for
// per-test stubs, instead of relying on setup.js's per-test mw reset.
// eslint-disable-next-line camelcase -- srf_slideshow is a PHP-generated global, see SRF_SlideShow.php
global.srf_slideshow = {};
require( modulePath );
const capturedMw = global.mw;

// $.fn.button is a jQuery UI widget; setup.js only vendors widget/mouse/slider
// (the ones srf.formats.eventcalendar and others need), so stub it here as a
// no-op plugin rather than widening the shared setup for a single consumer.
QUnit.module( 'ext.srf.slideshow', {
	beforeEach: () => {
		$.fn.button = $.fn.button || function () {
			return this;
		};
		sinon.stub( $.fn, 'button' ).returnsThis();
		// setup.js's mw.msg stub always returns '', which collapses the
		// pause/play aria-label into a single value; echo the key instead so the
		// pause/play toggle is actually observable. Stub the captured instance
		// directly since the module no longer re-reads global.mw per test.
		sinon.stub( capturedMw, 'msg' ).callsFake( ( key ) => key );
		// setup.js's mw.loader.using() never invokes its callback (kept minimal
		// since most consumers only check .then()); the nav-controls setup here
		// is wired entirely inside that callback, so resolve it synchronously —
		// scoped to this captured mw instance only, not the shared mock.
		sinon.stub( capturedMw.loader, 'using' ).callsFake( ( modules, callback ) => {
			if ( callback ) {
				callback();
			}
			return Promise.resolve();
		} );
		// jQuery's animation queue defers to requestAnimationFrame-driven ticks;
		// $.fx.off makes .animate() apply end-state synchronously so assertions
		// don't need to wait on jQuery's internal timer loop.
		$.fx.off = true;
		// eslint-disable-next-line camelcase -- srf_slideshow is a PHP-generated global, see SRF_SlideShow.php
		global.srf_slideshow = {};
	},
	afterEach: () => {
		$.fx.off = false;
		delete global.srf_slideshow;
	}
}, () => {

	/**
	 * @param {Array} results array of `[ pageId ]` (unloaded) result entries
	 * @param {Object} [overrides] overrides for the default slideshow options
	 * @return {{context: jQuery, options: Object}} the target element and the built options object
	 */
	function buildSlideshow( results, overrides ) {
		const context = $( '<div id="test-slideshow">' ).appendTo( document.body );

		const options = Object.assign( {
			data: [
				results,
				'template',
				0,
				'100px',
				'200px',
				false,
				'none',
				'[]'
			]
		}, overrides );

		return { context: context, options: options };
	}

	QUnit.test( 'fetches and displays the first result on init', ( assert ) => {
		const ajax = sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 7 ] ] );
		context.slideshow( options );

		assert.strictEqual( ajax.callCount, 1, 'a single AJAX request was issued for the only result' );
		assert.deepEqual(
			ajax.firstCall.args[ 0 ].data,
			{ action: 'ext.srf.slideshow.show', format: 'json', pageid: 7, template: 'template', printouts: '[]' },
			'the AJAX request was built from the given template/printouts/pageid'
		);
		assert.strictEqual(
			context.find( '.slideshow-element' ).html(),
			'<p>content-7</p>',
			'the fetched HTML was inserted into a .slideshow-element'
		);
	} );

	QUnit.test( 'renders the API error message when the AJAX response contains an error', ( assert ) => {
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { error: { info: 'something went wrong' } } );
		} );

		const { context, options } = buildSlideshow( [ [ 7 ] ] );
		context.slideshow( options );

		const element = context.find( '.slideshow-element' );
		assert.true( element.hasClass( 'error' ), 'the element was flagged with the error class' );
		assert.strictEqual( element.text(), 'something went wrong', 'the error info was rendered as the element content' );
	} );

	QUnit.test( 'preloads the next result while showing the current one', ( assert ) => {
		const ajax = sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ] ] );
		context.slideshow( options );

		assert.strictEqual( ajax.callCount, 2, 'both the current and the next result were fetched' );
		assert.strictEqual( ajax.firstCall.args[ 0 ].data.pageid, 1, 'the current result was fetched first' );
		assert.strictEqual( ajax.secondCall.args[ 0 ].data.pageid, 2, 'the next result was preloaded' );
		assert.strictEqual(
			context.find( '.slideshow-element' ).text(),
			'content-1',
			'only the current (first) result is displayed'
		);
	} );

	QUnit.test( 'auto-advances to the next result after the configured delay', ( assert ) => {
		const clock = sinon.useFakeTimers( { toFake: [ 'setTimeout', 'clearTimeout' ] } );
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ] ], { data: undefined } );
		options.data = [ [ [ 1 ], [ 2 ] ], 'template', 500, '100px', '200px', false, 'none', '[]' ];
		context.slideshow( options );

		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-1', 'the first result is shown initially' );

		clock.tick( 500 );

		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-2', 'the slideshow advanced to the next result after the delay elapsed' );

		clock.restore();
	} );

	QUnit.test( 'does not auto-advance when delay is 0', ( assert ) => {
		const clock = sinon.useFakeTimers( { toFake: [ 'setTimeout', 'clearTimeout' ] } );
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ] ] );
		context.slideshow( options );

		clock.tick( 100000 );

		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-1', 'the slideshow stayed on the first result with no delay configured' );

		clock.restore();
	} );

	QUnit.test( 'wraps to the first result after the last one', ( assert ) => {
		const clock = sinon.useFakeTimers( { toFake: [ 'setTimeout', 'clearTimeout' ] } );
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ] ], { data: undefined } );
		options.data = [ [ [ 1 ], [ 2 ] ], 'template', 100, '100px', '200px', false, 'none', '[]' ];
		context.slideshow( options );

		clock.tick( 100 ); // -> result 2
		clock.tick( 100 ); // -> wraps back to result 1

		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-1', 'the slideshow wrapped around to the first result' );

		clock.restore();
	} );

	[ 'none', 'slide left', 'slide right', 'slide up', 'slide down', 'fade', 'hide' ].forEach( ( effect ) => {
		QUnit.test( 'switches results using the "' + effect + '" effect without throwing', ( assert ) => {
			sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
				request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
			} );

			const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ] ], { data: undefined } );
			options.data = [ [ [ 1 ], [ 2 ] ], 'template', 0, '100px', '200px', false, effect, '[]' ];

			context.slideshow( options );

			assert.strictEqual(
				context.find( '.slideshow-element' ).text(),
				'content-1',
				'the "' + effect + '" effect displayed the first result'
			);
		} );
	} );

	QUnit.test( 'builds a navigation slider when nav controls are enabled', ( assert ) => {
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ], [ 3 ] ], { data: undefined } );
		options.data = [ [ [ 1 ], [ 2 ], [ 3 ] ], 'template', 0, '100px', '200px', true, 'none', '[]' ];
		context.slideshow( options );

		const nav = context.find( '.slideshow-nav' );
		assert.strictEqual( nav.length, 1, 'a .slideshow-nav element was created' );
		assert.strictEqual( nav.slider( 'option', 'max' ), 3, 'the slider max matches the number of results' );
		assert.strictEqual( nav.slider( 'option', 'value' ), 1, 'the slider starts at position 1' );
		assert.strictEqual( context.find( '.button' ).length, 1, 'a pause/play button was created' );
	} );

	QUnit.test( 'dragging the navigation slider switches to the selected result', ( assert ) => {
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ], [ 3 ] ], { data: undefined } );
		options.data = [ [ [ 1 ], [ 2 ], [ 3 ] ], 'template', 0, '100px', '200px', true, 'none', '[]' ];
		context.slideshow( options );

		const nav = context.find( '.slideshow-nav' );

		// jQuery UI's `slide` option only fires from a live pointer-drag interaction
		// (not from a programmatic `.slider( 'value', n )` call), so invoke it
		// directly to simulate a user drag to position 3.
		nav.slider( 'option', 'slide' ).call( nav[ 0 ], {}, { value: 3 } );

		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-3', 'the slideshow switched to the dragged-to result' );
		assert.strictEqual( nav.find( '.slideshow-nav-readout' ).text(), '3', 'the readout reflects the dragged-to position' );
	} );

	QUnit.test( 'clicking the pause button stops the automatic advance, clicking again resumes it', ( assert ) => {
		const clock = sinon.useFakeTimers( { toFake: [ 'setTimeout', 'clearTimeout' ] } );
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ] ], { data: undefined } );
		options.data = [ [ [ 1 ], [ 2 ] ], 'template', 500, '100px', '200px', true, 'none', '[]' ];
		context.slideshow( options );

		const button = context.find( '.button' );
		assert.strictEqual( button.attr( 'aria-label' ), 'srf-ui-slideshow-slide-button-pause', 'the button initially offers to pause' );

		button.trigger( 'click' );
		assert.strictEqual( button.attr( 'aria-label' ), 'srf-ui-slideshow-slide-button-play', 'after clicking, the button offers to play' );

		clock.tick( 5000 );
		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-1', 'auto-advance did not run while paused' );

		button.trigger( 'click' );
		assert.strictEqual( button.attr( 'aria-label' ), 'srf-ui-slideshow-slide-button-pause', 'after clicking again, the button offers to pause' );

		clock.tick( 500 );
		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-2', 'auto-advance resumed after unpausing' );

		clock.restore();
	} );

	QUnit.test( 'the pause button also responds to the Enter key (keypress with which 13)', ( assert ) => {
		const clock = sinon.useFakeTimers( { toFake: [ 'setTimeout', 'clearTimeout' ] } );
		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		const { context, options } = buildSlideshow( [ [ 1 ], [ 2 ] ], { data: undefined } );
		options.data = [ [ [ 1 ], [ 2 ] ], 'template', 500, '100px', '200px', true, 'none', '[]' ];
		context.slideshow( options );

		const button = context.find( '.button' );
		button.trigger( $.Event( 'keypress', { which: 13 } ) );

		clock.tick( 5000 );
		assert.strictEqual( context.find( '.slideshow-element' ).text(), 'content-1', 'auto-advance was paused by the Enter keypress' );

		clock.restore();
	} );

	QUnit.test( 'initializes every slideshow listed in the srf_slideshow global on document ready', ( assert ) => {
		const done = assert.async();

		sinon.stub( $, 'ajax' ).callsFake( ( request ) => {
			request.success( { [ request.data.pageid ]: '<p>content-' + request.data.pageid + '</p>' } );
		} );

		$( '<div id="global-slideshow">' ).appendTo( document.body );

		// the module was already require()'d once at file scope (to capture a
		// stable `mw` instance, see top of file) and its document-ready handler
		// already fired against an empty srf_slideshow; bust the require cache
		// and re-require so the handler runs again against this test's data.
		delete require.cache[ require.resolve( modulePath ) ];
		// eslint-disable-next-line camelcase -- srf_slideshow is a PHP-generated global, see SRF_SlideShow.php
		global.srf_slideshow = {
			'global-slideshow': [ [ [ 1 ] ], 'template', 0, '100px', '200px', false, 'none', '[]' ]
		};
		require( modulePath );

		setTimeout( () => {
			assert.strictEqual(
				$( '#global-slideshow' ).find( '.slideshow-element' ).text(),
				'content-1',
				'the slideshow listed under its id in srf_slideshow was auto-initialized'
			);

			// restore the plugin to close over `capturedMw` again: other tests in
			// this module stub methods on `capturedMw` specifically (see
			// beforeEach), and the re-require above just rebound $.fn.slideshow
			// to whatever global.mw was at that point instead.
			const realMw = global.mw;
			global.mw = global.mediaWiki = capturedMw;
			delete require.cache[ require.resolve( modulePath ) ];
			// eslint-disable-next-line camelcase -- srf_slideshow is a PHP-generated global, see SRF_SlideShow.php
			global.srf_slideshow = {};
			require( modulePath );
			global.mw = global.mediaWiki = realMw;

			done();
		}, 0 );
	} );

} );
