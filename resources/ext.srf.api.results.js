/**
 * SRF JavaScript for the api/results
 *
 * @param $
 * @param mw
 * @param srf
 * @since 1.9
 * @release 0.1
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author mwjames
 */
( function ( $, mw, srf ) {
	'use strict';

	// //////////////////////// PRIVATE METHODS //////////////////////////

	// Zero-indexed array of localised month names in the user interface
	// language, provided by the mediawiki.language.months module (declared
	// as a dependency of the ext.srf.api module).
	const monthNames = mw.language.months.names;

	// //////////////////////// PUBLIC METHODS /////////////////////////

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
	srf.api.results = function () {};
	srf.api.util = function () {};

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
		array: {
			/**
			 * Array unique function
			 *
			 * $.unique only allows to be an array of DOM elements therefore
			 * this returns a nromal "array" with no duplicates
			 *
			 * @param arr
			 * @credits http://paulirish.com/2010/duck-punching-with-jquery/
			 *
			 * @since 1.9
			 */
			unique: function ( arr ) {
				if ( arr[ 0 ].nodeType ) {
					return $.unique.apply( this, arguments );
				} else {
					return $.grep( arr, ( v, k ) => $.inArray( v, arr ) === k );
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
		 * @param printrequests
		 * @since 1.9
		 */
		printrequests: function ( printrequests ) {
			let list = printrequests;

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
				toArray: function ( printrequests ) {
					const tList = {};
					printrequests = printrequests || list;

					if ( printrequests !== undefined ) {
						$.map( printrequests, ( key, index ) => {
							tList[ key.label ] = { typeid: key.typeid, position: index, meta: key.meta };
						} );
					}
					list = tList;
					return list;
				},

				/**
				 * Returns typeid for a property
				 *
				 * @since 1.9
				 * @type Object
				 */
				getTypeId: function ( property ) {
					return list[ property ].typeid || null;
				},

				/**
				 * Returns some meta data for a property
				 *
				 * @since 1.9
				 * @type Object
				 */
				getMetaData: function ( property ) {
					return list[ property ].meta || null;
				},

				/**
				 * Returns the position for a property
				 *
				 * Printouts in the result object are not sorted
				 * therefore this returns its position in accordance with the query
				 *
				 * @param property
				 * @since 1.9
				 */
				getPosition: function ( property ) {
					return list[ property ].position || null;
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
				parseDate: function ( d ) {
					if ( typeof d === 'object' ) {
						return d;
					}
					if ( typeof d === 'number' ) {
						return new Date( d * 1000 );
					}
					if ( typeof d === 'string' ) {
						if ( d.match( /^\d+(\.\d+)?$/ ) ) {
							return new Date( parseFloat( d ) * 1000 );
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
				getISO8601Date: function ( timestamp ) {
					const d = this.parseDate( timestamp );
					return d !== null ? d.toISOString() : null;
				},

				/**
				 * Returns a formatted time (HH:MM:SS)
				 *
				 * @param string|Date time
				 * @param time
				 * @return string
				 */
				getTime: function ( time ) {
					const d = typeof time === 'object' ? time : this.parseDate( time );
					return ( d.getHours() < 10 ? '0' + d.getHours() : d.getHours() ) +
						':' + ( d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes() ) +
						':' + ( d.getSeconds() < 10 ? '0' + d.getSeconds() : d.getSeconds() );
				},

				/**
				 * Returns a formatted date
				 *
				 * @param string|Date
				 * @param string format
				 * @param date
				 * @param format
				 * @return string
				 */
				getDate: function ( date, format ) {
					const d = typeof date === 'object' ? date : this.parseDate( date );
					let formatDate = '';

					switch ( format ) {
						case 'dmY':
							formatDate = d.getDate() + '.' + ( String( d.getMonth() ) + 1 ) + '.' + d.getFullYear();
							break;
						case 'Ymd':
							formatDate = d.getFullYear() + '.' + ( String( d.getMonth() ) + 1 ) + '.' + d.getDate();
							break;
						default:
							formatDate = d.getDate() + ' ' + monthNames[ d.getMonth() ] + ' ' + d.getFullYear();
					}

					return formatDate;
				},

				/**
				 * Returns date
				 *
				 * @param string timestamp
				 * @param string format
				 * @param timestamp
				 * @param format
				 * @return string
				 */
				getMediaWikiDate: function ( timestamp, format ) {
					const date = this.parseDate( timestamp );
					return this.getDate( date, format ) + ' ' + this.getTime( date );
				}
			}
		}
	};

}( jQuery, mediaWiki, semanticFormats ) );
