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

	QUnit.module( 'ext.srf.formats.datatables', QUnit.newMwEnvironment() );

	var pass = 'Passes because ';
	var context = $( '<div class="srf-datatables"><div id="smw-test" class="container"></div></div>', '#qunit-fixture' ),
		container = context.find( '.container' );

	/**
	 * Instance testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'instance', 1, function ( assert ) {

		var datatables = new srf.formats.datatables();
		assert.ok( datatables instanceof srf.formats.datatables, pass + 'the srf.formats.datatables instance was accessible' );

	} );

	/**
	 * SMWAPI parse testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'parse', 3, function ( assert ) {
		var datatables = new srf.formats.datatables();

		var _uriTestCase = '{\"query\":{\"result\":{\"printrequests\":[{\"label\":\"\",\"typeid\":\"_wpg\",\"mode\":2},{\"label\":\"Has url\",\"typeid\":\"_uri\",\"mode\":1}],\"results\":{\"Data\\/1\":{\"printouts\":{\"Has url\":[\"http:\\/\\/localhost\\/mw\\/index.php?title=Data\\/\"]},\"fulltext\":\"Data\\/1\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Data\\/1\"},\"Main Page\":{\"printouts\":{\"Has url\":[\"http:\\/\\/localhost\\/mw\\/test\\/testcase\"]},\"fulltext\":\"Main Page\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Main_Page\"}},\"meta\":{\"hash\":\"c957c73b571d202b08f1385faf7550ec\",\"count\":2,\"offset\":0}},\"ask\":{\"conditions\":\"[[Has url::+]]\",\"parameters\":{\"limit\":50,\"offset\":0,\"format\":\"datatables\",\"link\":\"all\",\"headers\":\"show\",\"mainlabel\":\"\",\"intro\":\"\",\"outro\":\"\",\"searchlabel\":\"\\u2026 further results\",\"default\":\"\",\"class\":\"sortable wikitable smwtable\",\"theme\":\"bootstrap\"},\"printouts\":[\"?Has url\"]}},\"version\":\"0.1\"}';
		assert.equal( $.type( datatables.test._parse ), 'object', pass + 'the parse object was accessible' );

		var smwAPI = new smw.api();
		var data = smwAPI.parse( _uriTestCase );
		var result = datatables.test._parse.results( context, data );
		assert.equal( result.aaData.length, 2, pass + 'the result parsing returned an aaData array' );
		assert.equal( data.aoColumnDefs.length, 2, pass + 'the result parsing returned an aoColumnDefs array' );

	} );

	/**
	 * Update testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'table init and update test', 3, function ( assert ) {
		var datatables = new srf.formats.datatables();

		assert.equal( $.type( datatables.update ), 'function', pass + 'the function was accessible' );

		var _modDateCase= '{\"query\":{\"result\":{\"printrequests\":[{\"label\":\"\",\"typeid\":\"_wpg\",\"mode\":2},{\"label\":\"Modification date\",\"typeid\":\"_dat\",\"mode\":1}],\"results\":{\"File:5025159-view-of-the-golden-gate-bridge-san-francisco.jpg\":{\"printouts\":{\"Modification date\":[\"1360064258\"]},\"fulltext\":\"File:5025159-view-of-the-golden-gate-bridge-san-francisco.jpg\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/File:5025159-view-of-the-golden-gate-bridge-san-francisco.jpg\",\"namespace\":6,\"exists\":true},\"Concepttest3\":{\"printouts\":{\"Modification date\":[\"1358906761\"]},\"fulltext\":\"Concepttest3\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Concepttest3\",\"namespace\":0,\"exists\":true},\"Concepttest4\":{\"printouts\":{\"Modification date\":[\"1358905485\"]},\"fulltext\":\"Concepttest4\",\"fullurl\":\"http:\\/\\/localhost\\/mw\\/index.php\\/Concepttest4\",\"namespace\":0,\"exists\":true}},\"meta\":{\"hash\":\"f790045e40c932332c73b3c8bf7139a8\",\"count\":3,\"offset\":0}},\"ask\":{\"conditions\":\"[[Modification date::+]]\",\"parameters\":{\"limit\":3,\"offset\":0,\"format\":\"datatables\",\"link\":\"all\",\"headers\":\"show\",\"mainlabel\":\"\",\"intro\":\"\",\"outro\":\"\",\"searchlabel\":\"\\u2026 further results\",\"default\":\"\",\"class\":\"\",\"theme\":\"bootstrap\"},\"printouts\":[\"?Modification date\"]}},\"version\":\"0.1\"}';

		var smwAPI = new smw.api();
		var data = smwAPI.parse( _modDateCase );

		datatables.init( context, container, data );
		assert.ok( container.find( 'table' ) , pass + 'table was created' );

		datatables.update( context, data );
		assert.ok( container.find( 'table' ) , pass + 'table was updated' );

	} );
	
	QUnit.test( 'Issue#172: table with subject printout', 1, function ( assert ) {
		var oldAlert = window.alert;
		try {
			var alerts = [];
			window.alert = function( msg ) {
				alerts.push( msg );
			};
			var datatables = new srf.formats.datatables();
			
			var parameters = {"limit":50,"offset":0,"sortkeys":{"":"ASC"},"mainlabel":"-","querymode":1,"format":"datatables","source":"","link":"all","headers":"show","intro":"","outro":"","searchlabel":"... further results","default":"","class":"","theme":"bootstrap"};
			var data1 = new smw.dataItem.wikiPage( "Data/1", "http://localhost/wiki/Data/1", 0, "1", "Data 1" );
			data1.printouts = {
				"Has value":{"0":new smw.dataItem.wikiPage( "Value 1","http://localhost/wiki/Value_1",0,"1",null),"property":"Has value"}
				};
			var data2 = new smw.dataItem.wikiPage( "Data/2", "http://localhost/wiki/Data/2", 0, "1", "Data 2" );
			data2.printouts = {
				"Has value":{"0":new smw.dataItem.wikiPage( "Value 2","http://localhost/wiki/Value_2",0,"1",null),"property":"Has value"}
				};
			var results = {
				"Data/1": data1,
				"Data/2": data2
				};
			var printReqs = [{"label":"Has value","key":"Has_value","redi":"","typeid":"_wpg","mode":1,"format":""},{"label":"Data","key":"","redi":"","typeid":"_wpg","mode":2,"format":""}];
			
			var data = {
				"query" : {
					"ask" : {
						"parameters" : parameters
					},
					"result" : {
						"results" : results,
						"printrequests" : printReqs
					}
				}
			};
			assert.strictEqual( alerts.length, 0, "Shouldn't generate any alerts" );
			var actual = datatables.test._parse.results( context, data );
			var expected = {"aaData":[{"Has value":"<a href=\"http://localhost/wiki/Value_1\">Value 1</a>","Data":"<a href=\"http://localhost/wiki/Data/1\">Data 1</a>"},{"Has value":"<a href=\"http://localhost/wiki/Value_2\">Value 2</a>","Data":"<a href=\"http://localhost/wiki/Data/2\">Data 2</a>"}]};
			assert.deepEqual( actual, expected, 'Generated results should look right' );
		} finally {
			window.alert = oldAlert;
		}
	} );

}( jQuery, mediaWiki, semanticFormats ) );