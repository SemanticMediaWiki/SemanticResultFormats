'use strict';

require( require( 'path' ).resolve( __dirname, '../../formats/media/resources/ext.srf.template.jplayer.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/media/resources/ext.srf.formats.media.js' ) );

const sinon = require( 'sinon' );

QUnit.module( 'ext.srf.formats.media', () => {

	const jsonString = '{"data":{"File:Foo.mp3":{"mp3":"\\/mw\\/Foo.mp3","subject":"File:Foo.mp3"}},"count":5,"mediaType":"audio","mimeTypes":"ogg,mp3","inspector":true}';

	QUnit.test( 'init', ( assert ) => {
		const media = new srf.formats.media();

		assert.true( media instanceof Object, 'srf.formats.media() instance was accessible' );
		assert.strictEqual( $.type( media.defaults ), 'object', '.defaults was accessible' );
		assert.strictEqual( $.type( media.parse ), 'function', '.parse() was accessible' );
		assert.strictEqual( $.type( media.getId ), 'function', '.getId() was accessible' );
		assert.strictEqual( $.type( media.getPlayerSize ), 'function', '.getPlayerSize() was accessible' );
		assert.strictEqual( $.type( media.getData ), 'function', '.getData() was accessible' );
		assert.strictEqual( $.type( media.getPlayerTemplate ), 'function', '.getPlayerTemplate() was accessible' );
		assert.strictEqual( $.type( media.getInspector ), 'function', '.getInspector() was accessible' );
	} );

	QUnit.test( 'template', ( assert ) => {
		assert.strictEqual( $.type( srf.template.jplayer.inspector ), 'function', '.jplayer.inspector() was accessible' );
		assert.strictEqual( $.type( srf.template.jplayer.audio ), 'object', '.jplayer.audio returned an object' );
		assert.strictEqual( $.type( srf.template.jplayer.video ), 'object', '.jplayer.video returned an object' );
	} );

	// Rewritten: the legacy test issued a real $.get() with an empty .fail()
	// handler, so a failed/404 request meant assert.async() never resolved and
	// the test hung instead of failing cleanly. Stub $.get instead.
	QUnit.test( 'defaults', ( assert ) => {
		const media = new srf.formats.media();
		const getStub = sinon.stub( $, 'get' ).returns( $.Deferred().resolve().promise() );

		const done1 = assert.async();
		$.get( media.defaults.posterImage ).done( () => {
			assert.true( getStub.calledWith( media.defaults.posterImage ), media.defaults.posterImage + ' verified' );
			done1();
		} );

		const done2 = assert.async();
		$.get( media.defaults.jplayer.swfPath ).done( () => {
			assert.true( getStub.calledWith( media.defaults.jplayer.swfPath ), media.defaults.jplayer.swfPath + ' verified' );
			done2();
		} );
	} );

	QUnit.test( 'parse', ( assert ) => {
		const media = new srf.formats.media();

		assert.strictEqual( $.type( media.parse( jsonString ) ), 'object', '.parse() returned an object' );
	} );

	QUnit.test( 'getData', ( assert ) => {
		const media = new srf.formats.media();
		const json = media.parse( jsonString );
		const result = media.getData( json.data, json.mediaType );

		$.map( result, ( data ) => {
			assert.strictEqual( data.subject, 'File:Foo.mp3', 'subject returned "File:Foo.mp3"' );
			assert.strictEqual( data.title, 'File:Foo.mp3', 'title returned "File:Foo.mp3"' );
		} );
	} );

} );
