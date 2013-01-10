/**
 * JavaScript for the semanticFormats api/query
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

	var results = new srf.api.results();

	////////////////////////// PUBLIC METHODS /////////////////////////

	/**
	 * API namespace declaration
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
			 * Normalize printouts in order to get access to an indexed list
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
				 * Match type that is quried from the printrequest
				 *
				 * Use normalized printouts, printrequest array available via .toArray()
				 *
				 * For example
				 * search.type( [ ["Has location", "location"] ], [ ["location", "_str"] ], ["_str","_txt"] )
				 * will result in ["Has location", "..."] that matches the type _str
				 *
				 * @note Properties with the notion of Has location=location will have
				 * an array where its strcuture is property: Has location, label:location
				 * therefore we first have to check with the label as refrence (printrequests
				 * stores the label and the type together) but we export the property text
				 * instead of the label
				 *
				 * @since 1.9
				 * @type Object
				 * @type Object
				 * @type Object
				 *
				 * @return array
				 */
				type: function( printouts, printrequests, keys ){
					var matches = [];
					$.each( printouts, function( index, property ) {
						if ( typeof property === 'object' ) {
							if ( $.inArray( results.printrequests( printrequests ).getTypeId( property[1] ), keys ) > -1 ){
								matches.push( property[0] );
							}
						} else {
							if ( $.inArray( results.printrequests( printrequests ).getTypeId( property ), keys ) > -1 ){
								matches.push( property );
							}
						}
					} );
					return matches;
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
			$.each( options.printouts , function( key, value ) {
				printouts = printouts + '|' + value;
			} );

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
		 * Returns results from the SMWAPI interface
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
				url: mw.config.get( 'wgScriptPath' ) + '/api.php',
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