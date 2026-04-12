/**
 * SRF Carousel using slick https://github.com/kenwheeler/slick
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it
 */
(function ($, mw, srf) {
	"use strict";

	/* Private methods and objects */

	/**
	 * Helper objects
	 *
	 * @since 1.9
	 *
	 * @ignore
	 * @private
	 * @static
	 */
	var html = mw.html,
		profile = $.client.profile(),
		smwApi = new smw.api(),
		util = new srf.util();

	var removedURIs;

	/**
	 * Container for all non-public objects and methods
	 *
	 * @private
	 * @member srf.formats.datatables
	 */
	var _carousel = {
		/**
		 * Returns ID
		 *
		 * @private
		 * @return {string}
		 */
		getID: function (container) {
			return container.attr("id");
		},

		/**
		 * Returns container data
		 *
		 * @private
		 * @return {object}
		 */
		getData: function (container) {
			return mw.config.get(this.getID(container));
		},
	};

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

			$(".slick-slider .slick-slide").each(function () {
				// hide caption frame if title is empty
				// and screen < 800px
				if ( $( window ).width() < 800 && !$( '.slick-slide-content.caption-title', $( this ) ).length ) {
					$( '.slick-slide-content.caption', $( this ) ).hide();
				} else {
					$( '.slick-slide-content.caption', $( this ) ).show();
				}
		
				if ( $(this).attr('data-url') ) {
					// $(this).attr('title', $(this).attr('data-title') )
					$(this).css('cursor', 'pointer')
					$(this).click(function() {
  						window.location = $(this).attr('data-url');
					});
				}	

			})

		},

		/**
		 * Test interface which enables some internal methods / objects
		 * to be tested via qunit
		 *
		 * @ignore
		 */
		test: {
			_parse: _carousel.parse,
		},
	};

	/**
	 * carousel implementation
	 *
	 * @ignore
	 */
	var carousel = new srf.formats.carousel();

	$(document).ready(function () {

		// hide caption frame if title is empty
		// and screen < 800px
		$( window ).on( 'resize', function () {
			$( '.slick-slider' ).each( function () {
				$( '.slick-slider .slick-slide' ).each( function () {
					if ( $( window ).width() < 800 && !$( '.slick-slide-content.caption-title', $( this ) ).length ) {
						$( '.slick-slide-content.caption', $( this ) ).hide();
					} else {
						$( '.slick-slide-content.caption', $( this ) ).show();
					}
				} );
			} );
		} );

		$(".slick-slider").each(function () {
			carousel.init( $(this) );
		});

	});
})(jQuery, mediaWiki, semanticFormats);
