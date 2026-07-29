'use strict';

require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.redirect.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.overlay.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.slideshow.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.carousel.js' ) );

QUnit.module( 'ext.srf.formats.gallery', () => {

	// LEGACY: the original "overlay" sub-test used a malformed HTML literal
	// (stray '"' before "</div>") and is superseded by the more thorough
	// ext.srf.formats.gallery.overlay.test.js port below.
	QUnit.test( 'init', ( assert ) => {
		const gallery = new srf.formats.gallery();

		assert.true( gallery instanceof Object, 'gallery instance was accessible' );
		assert.strictEqual( $.type( gallery.redirect ), 'function', '.redirect() was accessible' );
		assert.strictEqual( $.type( gallery.overlay ), 'function', '.overlay() was accessible' );
		assert.strictEqual( $.type( gallery.slideshow ), 'function', '.slideshow() was accessible' );
		assert.strictEqual( $.type( gallery.carousel ), 'function', '.carousel() was accessible' );
	} );

} );
