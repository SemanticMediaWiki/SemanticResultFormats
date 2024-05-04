/**
 * QUnit tests
 *
 * @since 4.0.2
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it
 */
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.formats.carousel', QUnit.newMwEnvironment() );

	var pass = 'Passes because ';
	var context = $( '<div class="slick-slider my-class" style="height: 400px; width: 100%" data-slick="{&quot;accessibility&quot;:true}"><div class="slick-slide" data-url="http://127.0.0.1/mediawiki/index.php/Test/Carousel_test_c" style="cursor: pointer;"><img src="/mediawiki/images/2/2d/800px-Cover_fina.jpg" alt="abc" style="height: 400px; width: 100%; object-fit: cover" class="slick-slide-content img"><div class="slick-slide-content caption"><div class="slick-slide-content caption-title">Carousel test c</div><div class="slick-slide-content caption-text">abc</div></div></div></div>', '#qunit-fixture' );

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'instance', function ( assert ) {
		assert.expect( 1 );

		var carousel = new srf.formats.carousel();
		assert.ok( carousel instanceof srf.formats.carousel, pass + 'the srf.formats.carousel instance was accessible' );

	} );

	/**
	 * Update testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'carousel init', function ( assert ) {
		assert.expect( 1 );
		var carousel = new srf.formats.carousel();

		$( context, ".slick-slider").each(function () {
			carousel.init( $(this) );
		});

		assert.ok( true , pass + 'no errors after init' );

	} );

}( jQuery, mediaWiki, semanticFormats ) );
