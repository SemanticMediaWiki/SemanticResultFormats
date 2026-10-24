'use strict';

const jcarouselPath = require( 'path' ).resolve( __dirname, '../../resources/jquery/jquery.jcarousel.js' );

require( jcarouselPath );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.carousel.js' ) );

QUnit.module( 'ext.srf.formats.gallery.carousel', () => {

	function startCarousel() {
		$( document.body ).append(
			'<div class="srf-gallery-carousel">' +
				'<ul class="gallery mw-gallery-traditional jcarousel jcarousel-skin-smw" id="test-carousel" style="display:none;" data-wrap="both" data-vertical="false" data-rtl="false" data-scroll="1">' +
					'<li class="gallerybox" style="width: 335px">first</li>' +
					'<li class="gallerybox" style="width: 335px">second</li>' +
					'<li class="gallerybox" style="width: 335px">third</li>' +
				'</ul>' +
			'</div>'
		);
		const context = $( '.srf-gallery-carousel' );

		new srf.formats.gallery().carousel( context );

		return context;
	}

	function showNextImage( context ) {
		context.find( '.jcarousel-next' ).trigger( 'click' );

		return context.find( 'ul.gallery' ).promise();
	}

	QUnit.test( 'carousel turns a gallery with several images into a carousel', ( assert ) => {
		const context = startCarousel();

		assert.strictEqual(
			context.find( '.jcarousel-skin-smw .jcarousel-container-horizontal ul.gallery' ).length,
			1,
			'the gallery list sits in the container the carousel stylesheet styles'
		);
	} );

	QUnit.test( 'carousel lets the reader move on to the next image', async ( assert ) => {
		const context = startCarousel();

		await showNextImage( context );

		assert.strictEqual( context.find( 'ul.gallery' ).css( 'left' ), '-335px' );
	} );

	QUnit.module( 'on Safari, with jCarousel run after the page has loaded', ( hooks ) => {
		let navigatorDescriptor;

		hooks.beforeEach( () => {
			navigatorDescriptor = Object.getOwnPropertyDescriptor( global, 'navigator' );
			Object.defineProperty( global, 'navigator', {
				configurable: true,
				value: { userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Safari/605.1.15' }
			} );
			// evaluated afresh after jsdom's load event, as ResourceLoader may evaluate it
			delete require.cache[ jcarouselPath ];
			require( jcarouselPath );
		} );

		hooks.afterEach( () => {
			Object.defineProperty( global, 'navigator', navigatorDescriptor );
		} );

		QUnit.test( 'carousel lets the reader move on to the next image', async ( assert ) => {
			const context = startCarousel();

			await showNextImage( context );

			assert.strictEqual( context.find( 'ul.gallery' ).css( 'left' ), '-335px' );
		} );
	} );

} );
