/**
 * JavaScript for SRF boilerplate format
 *
 * @param $
 * @see http://www.semantic-mediawiki.org/wiki/Help:Boilerplate format
 *
 * Please remember to add your documentation to http://www.semantic-mediawiki.org
 *
 * @since 1.8
 * @release 0.1
 *
 * @licence GPL-2.0-or-later
 * @author mwjames
 */
( function ( $ ) {

	// Use EcmaScript 5 to improve code quality and check with jshint/jslint
	// if the code adheres standard coding conventions

	// Strict mode eliminates some JavaScript pitfalls
	'use strict';

	/**
	 * Document ready instance
	 *
	 * @since 1.8
	 * @type Object
	 */
	$( document ).ready( () => {

		// Use the class selector to find all instances relevant to the "boilerplate" printer
		// since a wiki page can have more than one instance of the same result printer
		// .each() ensures instances are handled separately
		$( '.srf-boilerplate' ).each( function () {

			// Ensure variables have only local scope otherwise leaked content might
			// cause issues for other plugins
			const $this = $( this );

			// Find the container instance that was created by the PHP output
			// and store it as "container" variable which all preceding steps
			// working on a localized instance
			const container = $this.find( '.container' );

			// Find the ID that connects to the current instance with the published data
			const ID = container.attr( 'id' );

			// Fetch the stored data with help of mw.config.get() method and the current instance ID
			// @see http://www.mediawiki.org/wiki/ResourceLoader/Default_modules#mediaWiki.config
			const json = mw.config.get( ID );

			// Parse the fetched json string and convert it back into objects/arrays
			const data = typeof json === 'string' ? jQuery.parseJSON( json ) : json;

			// Hide the spinner which belongs to the outer wrapper
			$this.find( '.srf-spinner' ).hide();

			// You got everything you need to work your magic
			// A clean instance, data from the wiki, and a separate container

			// If you need to see what data you've got from your result printer it is
			// always helpfull to do
			mw.log( data );

			// Happy coding ...

		} );
	} );
}( jQuery ) );
