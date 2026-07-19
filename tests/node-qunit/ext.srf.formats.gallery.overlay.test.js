'use strict';

require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js' ) );
require( require( 'path' ).resolve( __dirname, '../../formats/gallery/resources/ext.srf.gallery.overlay.js' ) );

const sinon = require( 'sinon' );

QUnit.module( 'ext.srf.formats.gallery.overlay', {
	// overlay() unconditionally calls .fancybox() on the matched anchors, even in
	// the "empty gallery"/"missing href" cases below — stub it as a no-op plugin.
	beforeEach: () => {
		$.fn.fancybox = $.fn.fancybox || function () {
			return this;
		};
		sinon.stub( $.fn, 'fancybox' ).returnsThis();
	},
	afterEach: () => {
		$.fn.fancybox.restore();
	}
}, () => {

	QUnit.test( 'overlay handles mw-file-description selector', ( assert ) => {
		const mockHtml = $(
			'<ul class="gallery mw-gallery-traditional" id="test-gallery">' +
				'<li class="gallerybox" style="width: 155px">' +
					'<div style="width: 155px">' +
						'<div class="thumb" style="width: 150px;">' +
							'<div style="margin:15px auto;">' +
								'<a class="mw-file-description" href="/wiki/File:Test.jpg" title="Test image">' +
									'<img alt="Test image" src="/thumb/Test.jpg/120px-Test.jpg" width="120" height="90" />' +
								'</a>' +
							'</div>' +
						'</div>' +
						'<div class="gallerytext">' +
							'<p>Test image description</p>' +
						'</div>' +
					'</div>' +
				'</li>' +
			'</ul>'
		);

		$( document.body ).append( mockHtml );
		const context = $( '#test-gallery' );
		const gallery = new srf.formats.gallery();

		gallery.overlay( context, 'File' );

		const imageLink = context.find( 'a.mw-file-description' );
		assert.true( imageLink.length > 0, 'Found image link with mw-file-description class' );
		assert.strictEqual( imageLink.attr( 'rel' ), 'test-gallery', 'Image link has correct rel attribute for grouping' );
		assert.strictEqual( imageLink.attr( 'title' ), 'Test image description', 'Image link has correct title from gallery text' );
		assert.true( imageLink.attr( 'href' ).includes( 'File:Test.jpg' ), 'Image link href points to correct file' );
	} );

	QUnit.test( 'overlay handles empty gallery gracefully', ( assert ) => {
		const emptyGallery = $( '<ul class="gallery mw-gallery-traditional" id="empty-gallery"></ul>' );
		$( document.body ).append( emptyGallery );

		const gallery = new srf.formats.gallery();

		gallery.overlay( emptyGallery, 'File' );

		assert.true( true, 'Overlay handles empty gallery without errors' );
	} );

	QUnit.test( 'overlay wires up a fancybox titleFormat that renders the close button and title', ( assert ) => {
		const mockHtml = $(
			'<ul class="gallery mw-gallery-traditional" id="test-gallery-title">' +
				'<li class="gallerybox">' +
					'<div>' +
						'<a class="mw-file-description" href="/wiki/File:Test.jpg" title="Test image">' +
							'<img alt="Test image" src="/thumb/Test.jpg/120px-Test.jpg" />' +
						'</a>' +
					'</div>' +
					'<div class="gallerytext"><p>Test description</p></div>' +
				'</li>' +
			'</ul>'
		);

		$( document.body ).append( mockHtml );
		const context = $( '#test-gallery-title' );
		const gallery = new srf.formats.gallery();

		gallery.overlay( context, 'File' );

		const titleFormat = $.fn.fancybox.getCall( 0 ).args[ 0 ].titleFormat;
		const title = titleFormat( 'My Title', [ 'a', 'b' ], 0 );

		assert.true( title.startsWith( '<div class="srf-fancybox-title">' ), 'title markup starts with the fancybox title wrapper' );
		assert.true( title.includes( '<img src=/srf/resources/jquery/fancybox/closelabel.gif>' ), 'close icon src is concatenated without a stray split' );
		assert.true( title.includes( '<b>My Title' ), 'the given title text is included' );
	} );

	QUnit.test( 'overlay handles missing href gracefully', ( assert ) => {
		const mockHtml = $(
			'<ul class="gallery mw-gallery-traditional" id="test-gallery-no-href">' +
				'<li class="gallerybox">' +
					'<div>' +
						'<a class="mw-file-description" title="Test image">' +
							'<img alt="Test image" src="/thumb/Test.jpg/120px-Test.jpg" />' +
						'</a>' +
					'</div>' +
					'<div class="gallerytext"><p>Test description</p></div>' +
				'</li>' +
			'</ul>'
		);

		$( document.body ).append( mockHtml );
		const context = $( '#test-gallery-no-href' );
		const gallery = new srf.formats.gallery();

		gallery.overlay( context, 'File' );

		const galleryBox = context.find( '.gallerybox' );
		assert.true( galleryBox.find( '.error' ).length > 0, 'Error message displayed for missing href' );
		assert.true( galleryBox.html().includes( 'srf-gallery-image-url-error' ), 'Correct error message key used' );
	} );

} );
