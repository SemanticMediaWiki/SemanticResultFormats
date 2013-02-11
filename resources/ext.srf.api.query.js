/**
 * SRF JavaScript for the api/query
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
( function( $, mw, srf ) {
 'use strict';

	/**
	 * Private methods and objects used within the class
	 *
	 * @since  1.9
	 */
	var results = new srf.api.results();

	/**
	 * Public API namespace declaration
	 *
	 * @since 1.9
	 * @type Object
	 */
	srf.api = srf.api || {};

	/**
	 * Base constructor for objects representing a api.query instance
	 *
	 * @since 1.9
	 */
	srf.api.query = function() {};

	/**
	 * Public methods to access information via the SMWAPI
	 *
	 * @since 1.9
	 * @type Object
	 */
	srf.api.query.prototype = {

		conditions: {

			/**
			 * Builds a conditional statement
			 *
			 * For example
			 * operators ( ::, ::>, ::< etc.)
			 *
			 * @since 1.9
			 * @type Object
			 *
			 * @return array
			 */
			build: function ( property, value, operator ){
				return '[[' + property + ( operator || '::' ) + value + ']]';
			}
		},

		printouts: {

			/**
			 * Normalize printouts in order to get access via key reference
			 *
			 * e.g. |?Has location=location	will be transformed into an
			 * array ["Has location", "location"]
			 *
			 * @since 1.9
			 * @type Object
			 *
			 * @return array
			 */
			toList: function( printouts ){
				var list = [];
				var identifier = new RegExp( '[\\?&]' + '(.*?)' + '[=]' );

				$.each( printouts, function( value, text ) {
					// Split the text and find anything that is between ? and = otherwise
					// split the string just after ?
					var match = text.split( identifier );
					if( match !== null ){
						if ( match[0] === '' ){
							// match ["", "Has lcoation", "location"]
							list.push( [ match[1], match[2]] );
						}else{
							list.push( match[0].split( '?' )[1] );
						}
					}
				} );
				return list;
			},

			/**
			 * Find printout matches
			 *
			 * @since 1.9
			 * @type Object
			 */
			search: {

				/**
				 * Find printout that matches a specific identifier
				 *
				 * e.g. |?Has location=location
				 *
				 * search.identifier( [...], 'location' ) will result in "Has location"
				 *
				 * @since 1.9
				 * @type Object
				 * @type Object
				 *
				 * @return array
				 */
				identifier: function( printouts, identifier ){
					var property = '';
					var regexS = '[\\?&]' + '(.*?)' + '=' + identifier;
					var regex = new RegExp(regexS);

					$.each( printouts , function( key, value ) {
						if( value.match( regex ) !== null ){
							property = value.match( regex )[1];
						}
					} );
					return property;
				},

				/**
				 * Returns properties for a specific type where properties
				 * aren't marked with an identifier ( |?property=indentifier)
				 *
				 * SMWQUERY printouts, SMWAPI printrequests, ["_str","_txt"]
				 *
				 * For example
				 * type( printouts, printrequests, ["_str"] )
				 * result in ["Has location", "..."] that matches the type _str
				 *
				 * Filter all printout properties that are of type [...] and check against
				 * the printout list to indentify which of these printouts do not
				 * carry an additional identifier because those are not eligible
				 * to beused as filter properties
				 *
				 * @since 1.9
				 * @type Object
				 * @type Object
				 * @type Object
				 *
				 * @return array
				 */
				type: function( printouts, printrequests, dataTypes ){
					// Cache printouts, printrequests
					var	po = srf.api.query.prototype.printouts.toList( printouts ),
						pr = srf.api.results.prototype.printrequests();
						pr.toArray( printrequests );

					// Normalize printouts to an amendable structure
					function normalize(){
						var matches = [];
						// Match printouts against the list of available printrequests
						$.each( po, function( index, property ) {
							if ( typeof property === 'object' ) {
								if ( $.inArray( pr.getTypeId( property[1] ), dataTypes ) > -1 ){
									matches.push( property[0] );
								}
							} else {
								if ( $.inArray( pr.getTypeId( property ), dataTypes ) > -1 ){
									matches.push( property );
								}
							}
						} );
						return matches;
					}

					// Find those properties that are without an identifier
					function withoutIdentifier( list ){
						var matches = [];
						$.each( list, function( index, property ) {
							if ( $.inArray( property, po ) > -1 ) {
								matches.push( property );
							}
						} );
						return matches;
					}

					var record = withoutIdentifier( normalize() );
					return record.length > 0 ? srf.api.util.prototype.array.unique( record ) : '';
				}
			}
		},

		/**
		 * Returns a concatenated query string
		 *
		 * @since 1.9
		 * @type Object
		 *
		 * @return string
		 */
		toString: function( options ){

			var printouts = '';
			if ( options.printouts ){
				$.each( options.printouts , function( key, value ) {
					printouts = printouts + '|' + value;
				} );
			}

			var parameters = '';
			$.each( options.parameters , function( key, value ) {
				parameters = parameters + '|' + key + '=' + value;
			} );

			var conditions = '';
			if ( typeof options.conditions === 'object' ) {
				$.each( options.conditions , function( key, value ) {
					conditions = conditions + value;
				} );
			} else {
				conditions = options.conditions;
			}

			return  conditions + printouts + parameters;
		},

		/**
		 * Return results from the SMWAPI interface as callback
		 *
		 * @since 1.9
		 * @type Object
		 *
		 * @return array
		 */
		fetch: function( query, callback, log ){

			// Log data is only visible while in debug mode( &debug=true )
			if ( log ){
				var startDate = new Date();
				srf.log( 'Query: ' + query );
			}

			$.ajax( {
				url: mw.util.wikiScript( 'api' ),
				dataType: 'json',
				data: {
					'action': 'ask',
					'format': 'json',
					'query' : query
					}
			} )
			.done( function ( data ) {
				if ( log ){
					srf.log( 'Hash: ' + data.query.meta.hash );
					srf.log( 'Fetched: ' + ( new Date().getTime() - startDate.getTime() ) + ' ms ' + '( ' + data.query.meta.count + ' object )' );
				}

				// Return data to the callback
				if ( typeof callback === 'function' ) {
					callback.call( this, true, data );
				}
				return;
			} )
			.fail( function ( error ) {
				if ( typeof callback === 'function' ) {
					callback.call( this, false, error );
				}
				return;
			} );
		}
	};

} )( jQuery, mediaWiki, semanticFormats );