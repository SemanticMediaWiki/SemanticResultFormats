/**
 * QUnit tests for the SRF gallery overlay functionality
 *
 * @file
 * @since 5.1.0
 *
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author GitHub Copilot Assistant
 */

( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.formats.gallery.overlay', QUnit.newMwEnvironment() );

	/**
	 * Test overlay initialization with mw-file-description selector
	 */
	QUnit.test( 'overlay handles mw-file-description selector', function ( assert ) {
		assert.expect( 4 );

		// Create mock HTML structure with new MediaWiki gallery format
		var mockHtml = $( 
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

		$( '#qunit-fixture' ).append( mockHtml );
		var context = $( '#test-gallery' );
		var gallery = new srf.formats.gallery();

		// Test that the overlay function finds the correct selector
		gallery.overlay( context, 'File' );

		var imageLink = context.find( 'a.mw-file-description' );
		assert.ok( imageLink.length > 0, 'Found image link with mw-file-description class' );
		assert.equal( imageLink.attr( 'rel' ), 'test-gallery', 'Image link has correct rel attribute for grouping' );
		assert.equal( imageLink.attr( 'title' ), 'Test image description', 'Image link has correct title from gallery text' );
		assert.ok( imageLink.attr( 'href' ).indexOf( 'File:Test.jpg' ) > -1, 'Image link href points to correct file' );
	} );

	/**
	 * Test overlay with empty gallery
	 */
	QUnit.test( 'overlay handles empty gallery gracefully', function ( assert ) {
		assert.expect( 1 );

		var emptyGallery = $( '<ul class="gallery mw-gallery-traditional" id="empty-gallery"></ul>' );
		$( '#qunit-fixture' ).append( emptyGallery );

		var gallery = new srf.formats.gallery();
		
		// Should not throw error with empty gallery
		gallery.overlay( emptyGallery, 'File' );
		
		assert.ok( true, 'Overlay handles empty gallery without errors' );
	} );

	/**
	 * Test overlay with missing href attribute
	 */
	QUnit.test( 'overlay handles missing href gracefully', function ( assert ) {
		assert.expect( 2 );

		var mockHtml = $( 
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

		$( '#qunit-fixture' ).append( mockHtml );
		var context = $( '#test-gallery-no-href' );
		var gallery = new srf.formats.gallery();

		gallery.overlay( context, 'File' );

		var galleryBox = context.find( '.gallerybox' );
		assert.ok( galleryBox.find( '.error' ).length > 0, 'Error message displayed for missing href' );
		assert.ok( galleryBox.html().indexOf( 'srf-gallery-image-url-error' ) > -1, 'Correct error message key used' );
	} );

}( jQuery, mediaWiki, semanticFormats ) );