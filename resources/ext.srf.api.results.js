/**
 * JavaScript for the semanticFormats api/results
 *
 * @since 1.9
 * @release 0.1
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
/*global semanticFormats:true mediaWiki:true*/
( function( $, mw, srf ) {
 'use strict';

	////////////////////////// PRIVATE METHODS //////////////////////////

	////////////////////////// PUBLIC METHODS /////////////////////////

	/**
	 * API namespace declaration
	 *
	 * @since 1.9
	 * @type Object
	 */
	srf.api = srf.api || {};

	/**
	 * Base constructor for objects representing a api.results instance
	 *
	 * @since 1.9
	 */
	srf.api.results = function() {};
	srf.api.util = function() {};

	/**
	 * Public to access results information retrieved through the SMWAPI
	 *
	 * @since 1.9
	 * @type Object
	 */
	srf.api.util.prototype = {

		/**
		 * Array helper functions
		 *
		 * @since 1.9
		 */
		array:{
			/**
			 * Array unique function
			 *
			 * $.unique only allows to be an array of DOM elements therefore
			 * this returns a nromal "array" with no duplicates
			 *
			 * @credits http://paulirish.com/2010/duck-punching-with-jquery/
			 *
			 * @since 1.9
			 */
			unique: function( arr ){
				if ( !!arr[0].nodeType ){
					return $.unique.apply( this, arguments );
				} else {
					return $.grep(arr,function(v,k){ return $.inArray(v,arr) === k; } );
				}
			}
		}
	};

	/**
	 * Public to access results information retrieved through the SMWAPI
	 *
	 * @since 1.9
	 * @type Object
	 */
	srf.api.results.prototype = {

		/**
		 * Collection related to printrequests and properties
		 *
		 * @since 1.9
		 */
		printrequests: function( printrequests ){
			var list = printrequests;

			return {
				list: list,

				/**
				 * Returns a key reference array
				 *
				 * Transforms printrequest objects into an accessible flat array
				 * allowing a key reference
				 *
				 * Call as class instance via printrequests( [...] ).toArray()
				 * or as operational method via printrequests().toArray( [...] )
				 *
				 * @since 1.9
				 * @type Object
				 */
				toArray: function ( printrequests ){
					var tList = {};
					printrequests = printrequests || list;

					if ( printrequests !== undefined ){
						$.map( printrequests, function( key, index ) {
							tList[key.label] = { typeid: key.typeid, position: index };
						} );
					}
					return list = tList;
				},

				/**
				 * Returns typeid for a property
				 *
				 * @since 1.9
				 * @type Object
				 */
				getTypeId: function ( property ){
					return list[property].typeid || null;
				},

				/**
				 * Returns the position for a property
				 *
				 * Printouts in the result object are not sorted
				 * therefore this returns its position in accordance with the query
				 *
				 * @since 1.9
				 */
				getPosition: function ( property ){
					return list[property].position || null;
				}
			};
		},

		/**
		 * Collection related to data values
		 *
		 * @since 1.9
		 */
		dataValues: {

			/**
			 * Methods related to time data value
			 *
			 * @since 1.9
			 */
			time: {

				/**
				 * Returns normalized timestamp as JS date object
				 *
				 * @since 1.9
				 * @type Object
				 */
				parseDate: function( d ) {
					if ( typeof d === 'object') {
						return d;
					}
					if ( typeof d === 'number' ) {
						return new Date( d * 1000);
					}
					if ( typeof d === 'string' ) {
						if ( d.match(/^\d+(\.\d+)?$/) ) {
							return new Date(parseFloat( d ) * 1000);
						}
					}
					return null;
				},

				/**
				 * Create a new JavasScript date object based on the timestamp and return
				 * an ISO string
				 *
				 * @see SMWTimeValue::getISO8601Date()
				 *
				 * @since 1.9
				 * @type Object
				 */
				getISO8601Date: function( timestamp ){
					var d = this.parseDate( timestamp );
					return d !== null ? d.toISOString() : null;
				}
			}
		}
	};

} )( jQuery, mediaWiki, semanticFormats );