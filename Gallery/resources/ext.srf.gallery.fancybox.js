/**
 * JavaSript for SRF gallery fancybox module
 *
 * There is a method ImageGallery->add which allows to override the
 * image url but this feature is only introduced in MW 1.20 therefore
 * we have to catch the "real" image location url from the api to be able
 * to display the image in the fancybox
 *
 * @licence: GNU GPL v2 or later
 * @author:  mwjames
 *
 * @since: 1.8
 *
 * @release: 0.1
 */
(function( $ ) {

/* Error handling *************************************************************/
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }
/* Start javascript ***********************************************************/

	var _this = this;

	// Jeroen's SF image preview code snippset
	this.getImageURL = function( title , callback ) {
		$.getJSON(
			mw.config.get( 'wgScriptPath' ) + '/api.php',
			{
				'action': 'query',
				'format': 'json',
				'prop'  : 'imageinfo',
				'iiprop': 'url',
				'titles': 'File:' + title,
			},
			function( data ) {
				if ( data.query && data.query.pages ) {
					var pages = data.query.pages;

					for ( p in pages ) {
						var info = pages[p].imageinfo;
						for ( i in info ) {
							callback( info[i].url );
							return;
						}
					}
				}
				callback( false );
			}
		);
	};

	$(document).ready(function() {

		$( ".srf-fancybox" ).each(function() {
			var $this   = $( this );
			var srfPath = mw.config.get( 'srf.options' ).srfgScriptPath;

			// Loop over all relevant gallery items
			$this.find( 'a.image' ).each( function () {
				var $this = $( this );

				// Group images
				$this.attr( 'rel', $this.has('img').length ? 'fancybox' : '' );

				// Copy text information for image text display
				$this.attr( 'title', $this.find('img').attr('alt') );

				// There should be a better way to find the title object but there isn't
				title = $this.attr( 'href' ).replace(/.+?\File:(.*)$/, "$1" ).replace( "%27", "\'" );

				// Assign image url
				_this.getImageURL( title ,
						function( url ) { if ( url === false ) {
							$this.attr( 'href', '' );
						}	else {
							$this.attr( 'href', url );
						}
				} );
			} );

			// Format title display
			function formatTitle(title, currentArray, currentIndex, currentOpts) {
				return '<div class="srf-fancybox-title"><span class="button"><a href="javascript:;" onclick="$.fancybox.close();"><img src=' +  srfPath + '/resources/fancybox/closelabel.gif' + '></a></span>' + (title && title.length ? '<b>' + title : '' ) + '<span class="count"> (' +  mw.msg( 'srf-gallery-overlay-count', (currentIndex + 1) , currentArray.length ) + ')</span></div>';
			}

			// Display all images related to a group
			$this.find("a[rel^='fancybox']").fancybox( {
				'showCloseButton' : false,
				'titlePosition'   : 'inside',
				'titleFormat'     : formatTitle
			} );
/* End javascript ************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready
} )( window.jQuery, window.mediaWiki );