/**
 * QUnit tests
 *
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
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
	QUnit.test( 'instance', function ( assert ) {
		assert.expect( 1 );

		var datatables = new srf.formats.datatables();
		assert.ok( datatables instanceof srf.formats.datatables, pass + 'the srf.formats.datatables instance was accessible' );

	} );

	/**
	 * SMWAPI parse testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'parse', function ( assert ) {
		assert.expect( 3 );
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
	QUnit.test( 'table init and update', function ( assert ) {
		assert.expect( 3 );
		var datatables = new srf.formats.datatables();

		assert.equal( $.type( datatables.update ), 'function', pass + 'the function was accessible' );

		var _modDateCase = "{\"query\":{\"result\":{\"printrequests\":[{\"label\":\"\",\"key\":\"\",\"redi\":\"\",\"typeid\":\"_wpg\",\"mode\":2,\"format\":false},{\"label\":\"Assigned to\",\"key\":\"Assigned_to\",\"redi\":\"\",\"typeid\":\"_wpg\",\"mode\":1,\"format\":\"\"},{\"label\":\"Boolean prop\",\"key\":\"Boolean_prop\",\"redi\":\"\",\"typeid\":\"_wpg\",\"mode\":1,\"format\":\"\"}],\"results\":{\"My page#mysubobjectb\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"My page#mysubobjectb\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/My_page#mysubobjectb\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"My page#mysubobject\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"My page#mysubobject\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/My_page#mysubobject\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"My page b#mysubobjectb\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"My page b#mysubobjectb\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/My_page_b#mysubobjectb\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"Main Page b\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:A\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:A\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"Main Page b\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/Main_Page_b\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"Carousel test\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"Carousel test\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/Carousel_test\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"}},\"serializer\":\"SMW\\\\Serializers\\\\QueryResultSerializer\",\"version\":2,\"meta\":{\"hash\":\"6d7015d9df6e7fcc97a5335b055ff5ee\",\"count\":5,\"offset\":0,\"source\":\"\",\"time\":\"0.018219\"}},\"ask\":{\"conditions\":\"[[Assigned to::+]]\",\"parameters\":{\"limit\":5000,\"offset\":0,\"sortkeys\":{\"\":\"DESC\"},\"mainlabel\":\"\",\"querymode\":1,\"format\":\"datatables\",\"source\":\"\",\"link\":\"all\",\"headers\":\"plain\",\"intro\":\"\",\"outro\":\"\",\"searchlabel\":\"... further results\",\"default\":\"\",\"import-annotation\":false,\"class\":\"datatable\",\"theme\":\"bootstrap\",\"pagelength\":\"20\"},\"printouts\":[\"?Assigned to\",\"?Boolean prop\"]}},\"version\":\"0.2.5\"}"
		var smwAPI = new smw.api();
		var data = smwAPI.parse( _modDateCase );

		datatables.init( context, container, data );
		assert.ok( container.find( 'table' ) , pass + 'table was created' );
		
		datatables.test.setTestSmwApiResult(data);
		const done = assert.async();

		context.on( "srf.datatables.updateAfterParse", function() {
			assert.ok( true,  pass + 'table was updated' );
			done();
		} );

		datatables.update( context, data );

	} );

	QUnit.test( 'Issue#172: table with subject printout', function ( assert ) {
		assert.expect( 2 );
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
