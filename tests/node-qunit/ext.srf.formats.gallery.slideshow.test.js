'use strict';

require( require( 'path' ).resolve( __dirname, '../../resources/jquery/jquery.responsiveslides.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.slideshow.js' ) );

const sinon = require( 'sinon' );

QUnit.module( 'ext.srf.formats.gallery.slideshow', {
	// slideshow() unconditionally calls .responsiveSlides() on the gallery <ul> — stub it as a no-op plugin.
	beforeEach: () => {
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

QUnit.module( 'ext.srf.formats.gallery.slideshow with the bundled ResponsiveSlides', () => {

	function startSlideshow( navigation ) {
		$( document.body ).append(
			'<div class="srf-gallery-slideshow">' +
				'<ul class="gallery" id="test-slideshow-real" style="display:none;" data-nav-control="' + navigation + '">' +
					'<li>first</li>' +
					'<li>second</li>' +
					'<li>third</li>' +
				'</ul>' +
			'</div>'
		);
		const context = $( '.srf-gallery-slideshow' );

		new srf.formats.gallery().slideshow( context );

		return context;
	}

	QUnit.test( 'slideshow turns a gallery with several images into a slideshow', ( assert ) => {
		const context = startSlideshow( 'nav' );

		assert.true( context.find( 'ul.gallery' ).hasClass( 'rslides' ), 'the gallery list carries the class the slideshow stylesheet styles' );
	} );

	QUnit.test( 'slideshow with navigation=pager shows page numbers', ( assert ) => {
		const context = startSlideshow( 'pager' );

		assert.strictEqual( context.find( 'ul.rslides_tabs' ).length, 1 );
	} );

	QUnit.test( 'slideshow with navigation=nav shows previous and next links', ( assert ) => {
		const context = startSlideshow( 'nav' );

		assert.strictEqual( context.find( 'a.prev, a.next' ).length, 2 );
	} );

} );
