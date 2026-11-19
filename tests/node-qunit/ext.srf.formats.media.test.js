'use strict';

const media = require( require( 'path' ).resolve( __dirname, '../../formats/media/resources/ext.srf.formats.media.js' ) );

const sinon = require( 'sinon' );

QUnit.module( 'ext.srf.formats.media', {
	beforeEach: function () {
		// jsdom does not implement the HTMLMediaElement playback methods
		this.load = sinon.stub( window.HTMLMediaElement.prototype, 'load' );
		this.play = sinon.stub( window.HTMLMediaElement.prototype, 'play' ).returns( Promise.resolve() );
	},
	afterEach: function () {
		this.load.restore();
		this.play.restore();
	}
}, () => {

	function build( count ) {
		let items = '';
		for ( let i = 0; i < count; i++ ) {
			const sources = JSON.stringify( [ { src: '/mw/Foo' + i + '.mp3', type: 'audio/mpeg' } ] )
				.replace( /"/g, '&quot;' );
			items += '<li class="srf-media-item" data-type="audio" data-poster="/mw/Cover' + i + '.jpg" data-sources="' + sources + '"><a href="/mw/Foo' + i + '.mp3">Foo ' + i + '</a></li>';
		}
		document.body.innerHTML = '<div class="srf-media srf-media-audio">' +
			'<div class="srf-media-stage"><img class="srf-media-cover" hidden alt="">' +
			'<audio class="srf-media-element" controls></audio></div>' +
			'<ol class="srf-media-playlist">' + items + '</ol>' +
			'</div>';
		return document.querySelector( '.srf-media' );
	}

	QUnit.test( 'api', ( assert ) => {
		assert.strictEqual( typeof media.init, 'function', 'init() was accessible' );
		assert.strictEqual( typeof media.initAll, 'function', 'initAll() was accessible' );
	} );

	QUnit.test( 'single item gets no previous/next/shuffle controls', ( assert ) => {
		const root = build( 1 );

		assert.notStrictEqual( media.init( root ), null, 'custom controls are built' );
		assert.strictEqual( root.querySelector( '.srf-media-previous' ), null, 'no previous' );
		assert.strictEqual( root.querySelector( '.srf-media-next' ), null, 'no next' );
		assert.strictEqual( root.querySelector( '.srf-media-shuffle' ), null, 'no shuffle' );
		assert.notStrictEqual( root.querySelector( '.srf-media-play' ), null, 'play is there' );
		assert.true( root.classList.contains( 'srf-media-ready' ), 'marked as ready' );
	} );

	QUnit.test( 'native controls are replaced', ( assert ) => {
		const root = build( 2 );
		media.init( root );

		assert.false( root.querySelector( 'audio' ).hasAttribute( 'controls' ), 'controls attribute removed' );
		assert.notStrictEqual( root.querySelector( '.srf-media-seek' ), null, 'seek bar' );
		assert.notStrictEqual( root.querySelector( '.srf-media-volume' ), null, 'volume bar' );
		assert.notStrictEqual( root.querySelector( '.srf-media-stop' ), null, 'stop' );
	} );

	QUnit.test( 'cover art stays visible and follows the current item', ( assert ) => {
		const root = build( 3 );
		const ctl = media.init( root );
		const cover = root.querySelector( '.srf-media-cover' );

		assert.false( cover.hidden, 'cover shown for the first item' );
		assert.strictEqual( cover.getAttribute( 'src' ), '/mw/Cover0.jpg' );

		ctl.player.dispatchEvent( new window.Event( 'play' ) );
		assert.false( cover.hidden, 'cover still shown while playing' );

		root.querySelector( '.srf-media-next' ).click();
		assert.strictEqual( cover.getAttribute( 'src' ), '/mw/Cover1.jpg', 'cover changes with the item' );
	} );

	QUnit.test( 'formatTime', ( assert ) => {
		assert.strictEqual( media.formatTime( 0 ), '00:00' );
		assert.strictEqual( media.formatTime( 65.9 ), '01:05' );
		assert.strictEqual( media.formatTime( NaN ), '00:00' );
		assert.strictEqual( media.formatTime( Infinity ), '00:00' );
	} );

	QUnit.test( 'play/pause toggle follows the media events', ( assert ) => {
		const root = build( 2 );
		media.init( root );
		const player = root.querySelector( 'audio' );

		player.dispatchEvent( new window.Event( 'play' ) );
		assert.true( root.classList.contains( 'srf-media-playing' ), 'playing class set' );
		player.dispatchEvent( new window.Event( 'pause' ) );
		assert.false( root.classList.contains( 'srf-media-playing' ), 'playing class removed' );
	} );

	QUnit.test( 'playlist is enhanced only once', ( assert ) => {
		const root = build( 3 );

		assert.notStrictEqual( media.init( root ), null, 'first call enhances' );
		assert.strictEqual( media.init( root ), null, 'second call is ignored' );
		assert.strictEqual( root.querySelectorAll( '.srf-media-ui' ).length, 1, 'one set of controls' );
	} );

	QUnit.test( 'next / previous / click', function ( assert ) {
		const root = build( 3 );
		const ctl = media.init( root );
		const player = root.querySelector( 'audio' );

		assert.strictEqual( ctl.state.index, 0, 'starts with the first item' );
		assert.strictEqual( player.querySelector( 'source' ).getAttribute( 'src' ), '/mw/Foo0.mp3', 'first source set' );

		root.querySelector( '.srf-media-next' ).click();
		assert.strictEqual( ctl.state.index, 1, 'next moved to the second item' );
		assert.true( this.play.called, 'play() was requested' );

		root.querySelector( '.srf-media-previous' ).click();
		root.querySelector( '.srf-media-previous' ).click();
		assert.strictEqual( ctl.state.index, 2, 'previous wraps around' );

		root.querySelectorAll( '.srf-media-item' )[ 1 ].click();
		assert.strictEqual( ctl.state.index, 1, 'click on an entry selects it' );
		assert.true( root.querySelectorAll( '.srf-media-item' )[ 1 ].classList.contains( 'srf-media-current' ), 'entry marked as current' );
	} );

	QUnit.test( 'ended advances and stops at the end unless repeat is on', ( assert ) => {
		const root = build( 2 );
		const ctl = media.init( root );
		const player = root.querySelector( 'audio' );

		player.dispatchEvent( new window.Event( 'ended' ) );
		assert.strictEqual( ctl.state.index, 1, 'advanced to the last item' );

		player.dispatchEvent( new window.Event( 'ended' ) );
		assert.strictEqual( ctl.state.index, 1, 'stays on the last item' );

		root.querySelector( '.srf-media-repeat' ).click();
		player.dispatchEvent( new window.Event( 'ended' ) );
		assert.strictEqual( ctl.state.index, 0, 'repeat wraps to the first item' );
	} );

	QUnit.test( 'shuffle keeps the current item first and visits every item', ( assert ) => {
		const root = build( 5 );
		const ctl = media.init( root );

		root.querySelector( '.srf-media-shuffle' ).click();
		assert.strictEqual( ctl.state.order[ 0 ], 0, 'current item first' );
		assert.deepEqual( ctl.state.order.slice().sort(), [ 0, 1, 2, 3, 4 ], 'all items present once' );
	} );

} );
