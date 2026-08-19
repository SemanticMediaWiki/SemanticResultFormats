'use strict';

require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.slideshow.js' ) );

const sinon = require( 'sinon' );

QUnit.module( 'ext.srf.formats.gallery.slideshow', {
	// slideshow() unconditionally calls .responsiveSlides() on the gallery <ul> — stub it as a no-op plugin.
	beforeEach: () => {
		$.fn.responsiveSlides = $.fn.responsiveSlides || function () {
			return this;
		};
		sinon.stub( $.fn, 'responsiveSlides' ).returnsThis();
	},
	afterEach: () => {
		$.fn.responsiveSlides.restore();
	}
}, () => {

	QUnit.test( 'slideshow hides a single preceding empty <p><br></p>', ( assert ) => {
		const mockHtml = $(
			'<p><br></p>' +
			'<div class="srf-gallery-slideshow"><ul class="gallery" id="test-slideshow"><li>item</li></ul></div>'
		);

		$( document.body ).append( mockHtml );
		const context = $( '.srf-gallery-slideshow' );
		const gallery = new srf.formats.gallery();

		gallery.slideshow( context );

		const previous = context.prev( 'p' ).children( 'br' );
		assert.strictEqual( previous.css( 'display' ), 'none', 'the single preceding <br> was hidden' );
	} );

	QUnit.test( 'slideshow leaves multiple preceding <br> elements untouched', ( assert ) => {
		const mockHtml = $(
			'<p><br><br></p>' +
			'<div class="srf-gallery-slideshow"><ul class="gallery" id="test-slideshow-multi"><li>item</li></ul></div>'
		);

		$( document.body ).append( mockHtml );
		const context = $( '.srf-gallery-slideshow' );
		const gallery = new srf.formats.gallery();

		gallery.slideshow( context );

		const previous = context.prev( 'p' ).children( 'br' );
		assert.notStrictEqual( previous.css( 'display' ), 'none', 'the <br> elements were left visible when there is more than one' );
	} );

} );
