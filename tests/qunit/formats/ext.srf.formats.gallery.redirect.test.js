/**
 * QUnit tests for the SRF gallery redirect functionality
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

	QUnit.module( 'ext.srf.formats.gallery.redirect', QUnit.newMwEnvironment() );

	/**
	 * Mock srf.util for testing
	 */
	var mockUtil = function() {
		return {
			spinner: {
				create: function() {},
				replace: function() {}
			},
			getTitleURL: function( params, callback ) {
				// Mock successful URL retrieval
				if ( params.title === 'Valid_Title' ) {
					callback( '/wiki/Valid_Title' );
				} else {
					callback( false );
				}
			}
		};
	};

	// Mock srf.util
	var originalUtil = srf.util;
	srf.util = mockUtil;

	/**
	 * Test redirect functionality with mw-file-description selector
	 */
	QUnit.test( 'redirect handles mw-file-description selector', function ( assert ) {
		assert.expect( 3 );

		var mockHtml = $( 
			'<div class="srf-redirect" data-redirect-type="_uri">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description" href="/wiki/File:Test.jpg">' +
								'<img alt="http://example.com/redirect-target" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$( '#qunit-fixture' ).append( mockHtml );
		var context = mockHtml;
		var gallery = new srf.formats.gallery();

		gallery.redirect( context );

		var imageLink = context.find( 'a.mw-file-description' );
		var redirectIcon = context.find( '.redirecticon' );

		assert.ok( imageLink.length > 0, 'Found image link with mw-file-description class' );
		assert.equal( imageLink.attr( 'href' ), 'http://example.com/redirect-target', 'Image href updated to redirect URL from alt attribute' );
		assert.ok( redirectIcon.is( ':visible' ), 'Redirect icon is visible for URI type redirect' );
	} );

	/**
	 * Test redirect with article title resolution
	 */
	QUnit.test( 'redirect resolves article titles', function ( assert ) {
		var done = assert.async();
		assert.expect( 3 );

		var mockHtml = $( 
			'<div class="srf-redirect" data-redirect-type="article">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description" href="/wiki/File:Test.jpg">' +
								'<img alt="Valid_Title" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$( '#qunit-fixture' ).append( mockHtml );
		var context = mockHtml;
		var gallery = new srf.formats.gallery();

		gallery.redirect( context );

		// Wait for async URL resolution
		setTimeout( function() {
			var imageLink = context.find( 'a.mw-file-description' );
			var redirectIcon = context.find( '.redirecticon' );

			assert.ok( imageLink.length > 0, 'Found image link with mw-file-description class' );
			assert.equal( imageLink.attr( 'href' ), '/wiki/Valid_Title', 'Image href updated to resolved article URL' );
			assert.ok( redirectIcon.is( ':visible' ), 'Redirect icon is visible after successful title resolution' );
			done();
		}, 100 );
	} );

	/**
	 * Test redirect with missing href attribute
	 */
	QUnit.test( 'redirect handles missing href gracefully', function ( assert ) {
		assert.expect( 2 );

		var mockHtml = $( 
			'<div class="srf-redirect" data-redirect-type="_uri">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description">' +
								'<img alt="http://example.com/redirect" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$( '#qunit-fixture' ).append( mockHtml );
		var context = mockHtml;
		var gallery = new srf.formats.gallery();

		gallery.redirect( context );

		var galleryBox = context.find( '.gallerybox' );
		assert.ok( galleryBox.find( '.error' ).length > 0, 'Error message displayed for missing href' );
		assert.ok( galleryBox.html().indexOf( 'srf-gallery-image-url-error' ) > -1, 'Correct error message key used' );
	} );

	/**
	 * Test redirect with failed title resolution
	 */
	QUnit.test( 'redirect handles failed title resolution', function ( assert ) {
		var done = assert.async();
		assert.expect( 2 );

		var mockHtml = $( 
			'<div class="srf-redirect" data-redirect-type="article">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description" href="/wiki/File:Test.jpg">' +
								'<img alt="Invalid_Title" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$( '#qunit-fixture' ).append( mockHtml );
		var context = mockHtml;
		var gallery = new srf.formats.gallery();

		gallery.redirect( context );

		// Wait for async URL resolution
		setTimeout( function() {
			var imageLink = context.find( 'a.mw-file-description' );
			var redirectIcon = context.find( '.redirecticon' );

			assert.equal( imageLink.attr( 'href' ), '', 'Image href cleared on failed title resolution' );
			assert.ok( redirectIcon.is( ':hidden' ), 'Redirect icon hidden on failed title resolution' );
			done();
		}, 100 );
	} );

	// Restore original srf.util after tests
	QUnit.testDone( function() {
		srf.util = originalUtil;
	} );

}( jQuery, mediaWiki, semanticFormats ) );