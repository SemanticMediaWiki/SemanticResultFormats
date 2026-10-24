'use strict';

require( require( 'path' ).resolve( __dirname, '../../resources/jquery/jquery.jcarousel.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.carousel.js' ) );

QUnit.module( 'ext.srf.formats.gallery.carousel', () => {

	QUnit.test( 'carousel turns a gallery with several images into a carousel', ( assert ) => {
		// carousel() reads wgThumbLimits unguarded, and jsdom knows only the inline item widths
		mw.config.set( 'srf-config', { settings: { wgThumbLimits: [] } } );
		$( document.body ).append(
			'<div class="srf-gallery-carousel">' +
			'<ul class="gallery mw-gallery-traditional jcarousel jcarousel-skin-smw" id="test-carousel" style="display:none;"' +
			' data-wrap="both" data-vertical="false" data-rtl="false" data-scroll="1">' +
			'<li class="gallerybox" style="width: 335px">first</li>' +
			'<li class="gallerybox" style="width: 335px">second</li>' +
			'<li class="gallerybox" style="width: 335px">third</li>' +
			'</ul></div>'
		);
		const context = $( '.srf-gallery-carousel' );

		new srf.formats.gallery().carousel( context );

		assert.strictEqual(
			context.find( '.jcarousel-skin-smw .jcarousel-container-horizontal ul.gallery' ).length,
			1,
			'the gallery list sits in the container the carousel stylesheet styles'
		);
	} );

} );
