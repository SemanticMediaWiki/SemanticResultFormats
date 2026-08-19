/**
 * SRF Carousel using slick https://github.com/kenwheeler/slick
 *
 * @param $
 * @param mw
 * @param srf
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it
 */
( function ( $, mw, srf ) {
	'use strict';

	/**
	 * Inheritance class for the srf.formats constructor
	 *
	 * @since 1.9
	 *
	 * @class
	 * @abstract
	 */
	srf.formats = srf.formats || {};

	/**
	 * Class that contains the DataTables JavaScript result printer
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.formats
	 */
	srf.formats.carousel = function () {};

	/* Public methods */

	srf.formats.carousel.prototype = {
		init: function ( $slide ) {
			$slide.slick( $slide.data().slick );

			if ( $slide.data().slick.adaptiveHeight ) {
				$slide.addClass( 'adaptiveHeight' );
			}

			$( '.slick-slider .slick-slide' ).each( function () {
				// hide caption frame if title is empty
				// and screen < 800px
				if ( $( window ).width() < 800 && !$( '.slick-slide-content.caption-title', $( this ) ).length ) {
					$( '.slick-slide-content.caption', $( this ) ).hide();
				} else {
					$( '.slick-slide-content.caption', $( this ) ).show();
				}

				if ( $( this ).attr( 'data-url' ) ) {
					// $(this).attr('title', $(this).attr('data-title') )
					$( this ).css( 'cursor', 'pointer' );
					$( this ).click( function () {
						window.location = $( this ).attr( 'data-url' );
					} );
				}

			} );

		}
	};

	/**
	 * carousel implementation
	 *
	 * @ignore
	 */
	const carousel = new srf.formats.carousel();

	$( document ).ready( () => {

		// hide caption frame if title is empty
		// and screen < 800px
		$( window ).on( 'resize', () => {
			$( '.slick-slider' ).each( () => {
				$( '.slick-slider .slick-slide' ).each( function () {
					if ( $( window ).width() < 800 && !$( '.slick-slide-content.caption-title', $( this ) ).length ) {
						$( '.slick-slide-content.caption', $( this ) ).hide();
					} else {
						$( '.slick-slide-content.caption', $( this ) ).show();
					}
				} );
			} );
		} );

		$( '.slick-slider' ).each( function () {
			carousel.init( $( this ) );
		} );

	} );
}( jQuery, mediaWiki, semanticFormats ) );
