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
	var testJSON3 = {"smw-test":"{\"query\":{\"result\":{\"printrequests\":[{\"label\":\"\",\"typeid\":\"_wpg\",\"mode\":2},{\"label\":\"title\",\"typeid\":\"_str\",\"mode\":1},{\"label\":\"Has event start\",\"typeid\":\"_dat\",\"mode\":1},{\"label\":\"Has event end\",\"typeid\":\"_dat\",\"mode\":1},{\"label\":\"Description\",\"typeid\":\"_txt\",\"mode\":1},{\"label\":\"icon\",\"typeid\":\"_str\",\"mode\":1},{\"label\":\"color\",\"typeid\":\"_str\",\"mode\":1},{\"label\":\"Has event location\",\"typeid\":\"_wpg\",\"mode\":1}],\"results\":{\"Event calendar test\\/1# dd20ac429f9cec8c9e0fb69719753261\":{\"printouts\":{\"title\":[\"Pellentesque dui pretiu\"],\"Has event start\":[\"1360886400\"],\"Has event end\":[],\"Description\":[\"\\u0432\\u0442\\u043e\\u0440\\u043e\\u0435\"],\"icon\":[\"File:Event-presentation-icon.png\"],\"color\":[\"\\n#A0D8F1\"],\"Has event location\":[]},\"fulltext\":\"Event calendar test\\/1# dd20ac429f9cec8c9e0fb69719753261\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event_calendar_test\\/1#_dd20ac429f9cec8c9e0fb69719753261\",\"namespace\":0,\"exists\":true},\"Event calendar test\\/2# 9040bf1853edcb06d47891ed0760bb35\":{\"printouts\":{\"title\":[\"Lorem ipsum dolor ... nascetur ipsum.\"],\"icon\":[\"File:Event-meeting-icon.gif\"],\"color\":[\"\\n#E9AF32\"],\"Has event location\":[{\"fulltext\":\"Eveline Hall\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Eveline_Hall\",\"namespace\":0,\"exists\":false}]},\"fulltext\":\"Event calendar test\\/2# 9040bf1853edcb06d47891ed0760bb35\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event_calendar_test\\/2#_9040bf1853edcb06d47891ed0760bb35\",\"namespace\":0,\"exists\":true},\"Event calendar test\\/3# b37ba5543d4fd3485d86576a3cbddf48\":{\"printouts\":{\"title\":[\"Condimentum ut ... amet Cras tempus.\"],\"icon\":[\"File:Event-presentation-icon.png\"],\"color\":[\"\\n#A0D8F1\"],\"Has event location\":[]},\"fulltext\":\"Event calendar test\\/3# b37ba5543d4fd3485d86576a3cbddf48\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Event_calendar_test\\/3#_b37ba5543d4fd3485d86576a3cbddf48\",\"namespace\":0,\"exists\":true}},\"meta\":{\"hash\":\"5c7f3ce435d3918537e4090289716bda\",\"count\":3,\"offset\":0}},\"ask\":{\"conditions\":\"[[Has project::test 2]]\",\"parameters\":{\"limit\":50,\"offset\":0,\"format\":\"eventcalendar\",\"link\":\"subject\",\"headers\":\"show\",\"mainlabel\":\"\",\"intro\":\"\",\"outro\":\"\",\"searchlabel\":\"\\u2026 further results\",\"default\":\"\",\"defaultview\":\"month\",\"firstday\":\"Monday\",\"start\":\"earliest\",\"legend\":\"pane\",\"class\":\"\",\"theme\":\"vector\",\"dayview\":true},\"printouts\":[\"?Has event=title\",\"?Has event start\",\"?Has event end\",\"?Has event description=Description\",\"?Has event icon=icon\",\"?Has event color=color\",\"?Has event location\"]}},\"version\":\"0.7.4\"}"};

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
	QUnit.test( 'parse', 8, function ( assert ) {
		var calendar = new srf.formats.eventcalendar();

		assert.equal( $.type( calendar.test._parse ), 'object', pass + 'the parse object was accessible' );

		// Base data set
		mw.config.set( testJSON1 );
		assert.equal( $.type( calendar.test._getData( container ) ), 'object', pass + 'getData() returned an object' );

		// Check api is available
		var testData = calendar.test._getData( container );
		assert.equal( $.type( calendar.test._parse.api( testData ) ), 'object', pass + 'api() was accessible' );

		var expected = {
			"dates": [
						"1325390400",
						"1325563200",
						"1325300400",
						"1357304400",
						"1357308000"
						]
		}

		// Check for parsed dates array
		var results = calendar.test._parse.api( testData );
		assert.deepEqual( results.dates, expected.dates , pass + 'dates [] did match' );

		// Second test data contains type data for colorFilter settings
		mw.config.set( testJSON2 );
		var testData = calendar.test._getData( container );

		results = calendar.test._parse.api( testData );
		assert.deepEqual( results.legend, {} , pass + 'filterProperty was empty and therefore an empty {} was returned' );

		testData.query.ask.parameters.filterProperty = 'Has event type';
		var results = calendar.test._parse.api( testData ),
			expected = { "legend": {
				"Meeting": {"color": ["green"],"filter": false},
				"Talk": {"color": ["yellow"],"filter": false} }
			};

		assert.deepEqual( results.legend, expected.legend , pass + 'filterProperty was set and a legend {} was returned' );

		// Id101d97fc69a: title test (typeof _wpg)
		assert.equal( results.events[0].title, 'Demo 230' , pass + 'correct title (typeof _wpg) was returned' );

		// Id101d97fc69a: title test (typeof _str)
		mw.config.set( testJSON3 );
		var testData = calendar.test._getData( container );
		results = calendar.test._parse.api( testData );
		assert.equal( results.events[0].title, 'Pellentesque dui pretiu' , pass + 'correct title (typeof _str) was returned' );

	} );

	/**
	 * startDate testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'startDate', 10, function ( assert ) {

		var calendar = new srf.formats.eventcalendar();

		var testData = [ '1325390400', '1325563200', '1357304400', '1357308000' ];

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

		var testData = [ '633830400', '634176000', '1262563200' ];
		var results = calendar.test._startDate( testData ).minmax();
		assert.deepEqual( results, {"max": "1262563200","min": "633830400"}, pass + 'minmax() returned the correct object' );

		// I7156d76086b62: Regression test
		var testData = ["1360886400", "1347753600", "1347753600", "1347926400", "1347926400"];
		var results = calendar.test._startDate( testData ).minmax();
		assert.deepEqual( results, {"max": "1360886400","min": "1347753600"}, pass + 'minmax() returned the correct object' );

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
		var testData = calendar.test._getData( container );

		// Wait for an asynchronous action to complete
		calendar.update( context, container, testData );
		stop();
		container.on( "srf.eventcalendar.updateAfterParse", function() {
			assert.ok( true, pass + 'the "srf.eventcalendar.updateAfterParse" event was triggered' );
			start();
		} );

	} );

}( jQuery, mediaWiki, semanticFormats ) );