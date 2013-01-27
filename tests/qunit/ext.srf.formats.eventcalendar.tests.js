/**
 * QUnit tests
 *
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.formats.eventcalendar', QUnit.newMwEnvironment() );

	var pass = 'Passes because ';
	var context = $( '<div class="srf-eventcalendar"><div id="smw-test" class="container"></div></div>', '#qunit-fixture' ),
		container = context.find( '.container' );

	var testJSON1 = {"smw-test":"{\"query\":{\"result\":{\"printrequests\":[{\"label\":\"\",\"typeid\":\"_wpg\",\"mode\":2},{\"label\":\"title\",\"typeid\":\"_wpg\",\"mode\":1},{\"label\":\"Has event start\",\"typeid\":\"_dat\",\"mode\":1},{\"label\":\"Has event end\",\"typeid\":\"_dat\",\"mode\":1}],\"results\":{\"Event\\/1\":{\"printouts\":{\"title\":[{\"fulltext\":\"Demo 230\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Demo_230\"}],\"Has event start\":[\"1325390400\"],\"Has event end\":[\"1325563200\"]},\"fulltext\":\"Event\\/1\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event\\/1\"},\"Event\\/2\":{\"printouts\":{\"title\":[{\"fulltext\":\"Demo 230\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Demo_230\"}],\"Has event start\":[\"1325300400\"],\"Has event end\":[]},\"fulltext\":\"Event\\/2\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event\\/2\"},\"Event\\/4\":{\"printouts\":{\"title\":[{\"fulltext\":\"Demo 230\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Demo_230\"}],\"Has event start\":[\"1357304400\"],\"Has event end\":[\"1357308000\"]},\"fulltext\":\"Event\\/4\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event\\/4\"}},\"meta\":{\"hash\":\"ddf5e7e1558d010bf1a1e9ab5c2fa54b\",\"count\":3,\"offset\":0}},\"ask\":{\"conditions\":\"[[Has event start::+]]\",\"parameters\":{\"limit\":50,\"offset\":0,\"format\":\"eventcalendar\",\"link\":\"all\",\"headers\":\"show\",\"mainlabel\":\"\",\"intro\":\"\",\"outro\":\"\",\"searchlabel\":\"\\u2026 further results\",\"default\":\"\",\"defaultview\":\"month\",\"firstday\":\"Sunday\",\"start\":\"earliest\",\"legend\":\"none\",\"class\":\"\",\"theme\":\"basic\",\"dayview\":false},\"printouts\":[\"?Has event=title\",\"?Has event start\",\"?Has event end\"]}},\"version\":\"0.7.4\"}"};
	var testJSON2 = {"smw-test":"{\"query\":{\"result\":{\"printrequests\":[{\"label\":\"\",\"typeid\":\"_wpg\",\"mode\":2},{\"label\":\"title\",\"typeid\":\"_wpg\",\"mode\":1},{\"label\":\"Has event start\",\"typeid\":\"_dat\",\"mode\":1},{\"label\":\"Has event end\",\"typeid\":\"_dat\",\"mode\":1},{\"label\":\"Has event type\",\"typeid\":\"_wpg\",\"mode\":1},{\"label\":\"color\",\"typeid\":\"_str\",\"mode\":1}],\"results\":{\"Event\\/1# c39a574c835d239ebf85019eeb91e4bb\":{\"printouts\":{\"title\":[{\"fulltext\":\"Demo 230\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Demo_230\"}],\"Has event start\":[\"1325390400\"],\"Has event end\":[\"1325563200\"],\"Has event type\":[{\"fulltext\":\"Meeting\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Meeting\"}],\"color\":[\"green\"]},\"fulltext\":\"Event\\/1# c39a574c835d239ebf85019eeb91e4bb\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event\\/1#_c39a574c835d239ebf85019eeb91e4bb\"},\"Event\\/2# 17423f0168b2fd7d113dbd843224884a\":{\"printouts\":{\"title\":[{\"fulltext\":\"Demo 230\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Demo_230\"}],\"Has event start\":[\"1325300400\"],\"Has event end\":[],\"Has event type\":[{\"fulltext\":\"Talk\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Talk\"}],\"color\":[\"yellow\"]},\"fulltext\":\"Event\\/2# 17423f0168b2fd7d113dbd843224884a\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event\\/2#_17423f0168b2fd7d113dbd843224884a\"},\"Event\\/3# 4e920606589bc542048086b8913edb9c\":{\"printouts\":{\"title\":[{\"fulltext\":\"Demo 230\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Demo_230\"}],\"Has event start\":[\"1358910000\"],\"Has event end\":[\"1358913600\"],\"Has event type\":[{\"fulltext\":\"Meeting\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Meeting\"}],\"color\":[]},\"fulltext\":\"Event\\/3# 4e920606589bc542048086b8913edb9c\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event\\/3#_4e920606589bc542048086b8913edb9c\"},\"Event\\/4# 48bff722d643b0c550e6dcdf09699c47\":{\"printouts\":{\"title\":[{\"fulltext\":\"Demo 230\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Demo_230\"}],\"Has event start\":[\"1357304400\"],\"Has event end\":[\"1357308000\"],\"Has event type\":[{\"fulltext\":\"Meeting\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Meeting\"}],\"color\":[]},\"fulltext\":\"Event\\/4# 48bff722d643b0c550e6dcdf09699c47\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event\\/4#_48bff722d643b0c550e6dcdf09699c47\"}},\"meta\":{\"hash\":\"4f7be8ca26f266b1d36fcae82cdbe21c\",\"count\":4,\"offset\":0}},\"ask\":{\"conditions\":\"[[Has event start::+]]\",\"parameters\":{\"limit\":50,\"offset\":0,\"format\":\"eventcalendar\",\"link\":\"all\",\"headers\":\"show\",\"mainlabel\":\"\",\"intro\":\"\",\"outro\":\"\",\"searchlabel\":\"\\u2026 further results\",\"default\":\"\",\"defaultview\":\"month\",\"firstday\":\"Sunday\",\"start\":\"earliest\",\"legend\":\"tooltip\",\"class\":\"\",\"theme\":\"basic\",\"dayview\":true},\"printouts\":[\"?Has event=title\",\"?Has event start\",\"?Has event end\",\"?Has event type\",\"?Has event color=color\"]}},\"version\":\"0.7.4\"}"};

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'instance', 1, function ( assert ) {

		var calendar = new srf.formats.eventcalendar();
		assert.ok( calendar instanceof srf.formats.eventcalendar, pass + 'the srf.formats.eventcalendar instance was accessible' );

	} );

	/**
	 * SMWAPI parse testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'parse', 6, function ( assert ) {
		var calendar = new srf.formats.eventcalendar();

		assert.equal( $.type( calendar.test._parse ), 'object', pass + 'the parse object was accessible' );

		// Base data set
		mw.config.set( testJSON1 );
		assert.equal( $.type( calendar.test._parse.container( container ) ), 'object', pass + 'container() returned an object' );

		// Check api is available
		var testData = calendar.test._parse.container( container );
		assert.equal( $.type( calendar.test._parse.api( testData ) ), 'object', pass + 'api() was accessible' );

		var expected = {
			"dates": [
				{ "end": "1325563200", "start": "1325390400" },
				{ "start": "1325300400" },
				{ "end": "1357308000", "start": "1357304400" }
			]
		}

		// Check for parsed dates array
		var results = calendar.test._parse.api( testData );
		assert.deepEqual( results.dates, expected.dates , pass + 'dates [] did match' );

		// Second test data contains type data for colorFilter settings
		mw.config.set( testJSON2 );
		var testData = calendar.test._parse.container( container );

		results = calendar.test._parse.api( testData );
		assert.deepEqual( results.legend, {} , pass + 'filterProperty was empty and therefore an empty {} was returned' );

		testData.query.ask.parameters.filterProperty = 'Has event type';
		var results = calendar.test._parse.api( testData ),
			expected = {
				"legend": {"Meeting": {"color": ["green","",""],"filter": false},"Talk": {"color": ["yellow"],"filter": false} }
			};

		assert.deepEqual( results.legend, expected.legend , pass + 'filterProperty was set and a legend {} was returned' );

	} );

	/**
	 * startDate testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'startDate', 8, function ( assert ) {

		var calendar = new srf.formats.eventcalendar();

		var testData = [
			{ 'start': '1325390400', 'end': '1325563200' },
			{ 'start': '1357304400', 'end': '1357308000' },
		];

		assert.equal( $.type( calendar.test._startDate() ) , 'object', pass + 'the object was accessible' );
		assert.equal( $.type( calendar.test._startDate( testData ).minmax() ), 'object', pass + 'minmax() was accessible' );
		assert.equal( $.type( calendar.test._startDate( testData ).get() ), 'date', pass + 'get() was accessible' );

		var results = calendar.test._startDate( testData ).minmax();
		assert.deepEqual( results, {"max": "1357308000","min": "1325390400"}, pass + 'minmax() returned an object' );

		var results = calendar.test._startDate( testData ).get();
		assert.deepEqual( results, new Date() , pass + 'get() returned new Date()' );

		var results = calendar.test._startDate( testData ).get( 'foo' );
		assert.deepEqual( results, new Date() , pass + 'get( "foo" ) returned new Date()' );

		var results = calendar.test._startDate( testData ).get( 'earliest' );
		assert.equal( results.getDate(), '1' , pass + 'get( "earliest" ) returned 4' );

		var results = calendar.test._startDate( testData ).get( 'latest' );
		assert.equal( results.getDate(), '4' , pass + 'get( "latest" ) returned 1' );

	} );

	/**
	 * Update testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'update', 2, function ( assert ) {
		var calendar = new srf.formats.eventcalendar();

		assert.equal( $.type( calendar.update ), 'function', pass + 'the function was accessible' );

		mw.config.set( testJSON1 );
		var testData = calendar.test._parse.container( container );

		// Wait for an asynchronous action to complete
		calendar.update( context, container, testData );
		stop();
		container.on( "srf.eventcalendar.updateAfterParse", function() {
			assert.ok( true, pass + 'the "srf.eventcalendar.updateAfterParse" event was triggered' );
			start();
		} );

	} );

}( jQuery, mediaWiki, semanticFormats ) );