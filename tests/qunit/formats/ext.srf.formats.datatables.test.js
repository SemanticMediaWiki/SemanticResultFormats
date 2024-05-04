/**
 * QUnit tests
 *
 * @file
 * @ingroup SRF
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it
 */
( function ( $, mw, srf ) {
	'use strict';

	QUnit.module( 'ext.srf.formats.datatables', QUnit.newMwEnvironment() );

	var pass = 'Passes because ';
	var context = $( '<div class="srf-datatable" data-collation="identity" data-nocase="1" data-column-sort="{&quot;list&quot;:[&quot;&quot;,&quot;Modification date&quot;,&quot;Assigned to&quot;,&quot;Length&quot;,&quot;Length&quot;,&quot;Weight&quot;,&quot;Weight&quot;,&quot;My link&quot;],&quot;sort&quot;:[&quot;&quot;],&quot;order&quot;:[]}" data-datatables="{&quot;autoWidth&quot;:false,&quot;deferRender&quot;:false,&quot;info&quot;:true,&quot;lengthChange&quot;:true,&quot;ordering&quot;:true,&quot;paging&quot;:true,&quot;processing&quot;:false,&quot;scrollX&quot;:false,&quot;scrollY&quot;:&quot;&quot;,&quot;searching&quot;:true,&quot;stateSave&quot;:false,&quot;displayStart&quot;:0,&quot;pagingType&quot;:&quot;full_numbers&quot;,&quot;pageLength&quot;:20,&quot;lengthMenu&quot;:&quot;10, 20, 50, 100, 200&quot;,&quot;scrollCollapse&quot;:false,&quot;scroller&quot;:false,&quot;scroller.displayBuffer&quot;:50,&quot;scroller.loadingIndicator&quot;:false,&quot;buttons&quot;:&quot;&quot;,&quot;dom&quot;:&quot;frtip&quot;,&quot;fixedHeader&quot;:false,&quot;responsive&quot;:true,&quot;keys&quot;:false,&quot;columns.type&quot;:&quot;any-number&quot;,&quot;columns.width&quot;:&quot;&quot;,&quot;searchPanes&quot;:true,&quot;searchPanes.initCollapsed&quot;:true,&quot;searchPanes.collapse&quot;:true,&quot;searchPanes.columns&quot;:&quot;1,2,3&quot;,&quot;searchPanes.threshold&quot;:1}" data-printrequests="[{&quot;label&quot;:&quot;&quot;,&quot;key&quot;:&quot;&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_wpg&quot;,&quot;mode&quot;:2,&quot;format&quot;:&quot;&quot;},{&quot;label&quot;:&quot;abc&quot;,&quot;key&quot;:&quot;_MDAT&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_dat&quot;,&quot;mode&quot;:1,&quot;format&quot;:&quot;-F[F j]&quot;},{&quot;label&quot;:&quot;Assigned to&quot;,&quot;key&quot;:&quot;Assigned_to&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_wpg&quot;,&quot;mode&quot;:1,&quot;format&quot;:&quot;&quot;},{&quot;label&quot;:&quot;Length&quot;,&quot;key&quot;:&quot;Length&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_qty&quot;,&quot;mode&quot;:1,&quot;format&quot;:&quot;&quot;},{&quot;label&quot;:&quot;Length&quot;,&quot;key&quot;:&quot;Length&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_qty&quot;,&quot;mode&quot;:1,&quot;format&quot;:&quot;-n&quot;},{&quot;label&quot;:&quot;Weight&quot;,&quot;key&quot;:&quot;Weight&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_qty&quot;,&quot;mode&quot;:1,&quot;format&quot;:&quot;&quot;},{&quot;label&quot;:&quot;Weight&quot;,&quot;key&quot;:&quot;Weight&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_qty&quot;,&quot;mode&quot;:1,&quot;format&quot;:&quot;-u&quot;},{&quot;label&quot;:&quot;Link&quot;,&quot;key&quot;:&quot;My_link&quot;,&quot;redi&quot;:&quot;&quot;,&quot;typeid&quot;:&quot;_uri&quot;,&quot;mode&quot;:1,&quot;format&quot;:&quot;&quot;}]" data-printouts="[[2,&quot;&quot;,null,&quot;&quot;,{&quot;datatables-columns.type&quot;:&quot;string&quot;,&quot;datatables-width&quot;:&quot;50px&quot;,&quot;datatables-searchPanes.show&quot;:&quot; true&quot;}],[1,&quot;Modification date&quot;,null,&quot;-F[F j]&quot;,{&quot;template&quot;:&quot;mytemplate&quot;,&quot;datatables-searchPanes.show&quot;:&quot; false&quot;}],[1,&quot;Assigned to&quot;,null,&quot;&quot;,{&quot;datatables-columns.ariaTitle&quot;:&quot;a&quot;,&quot;datatables-columns.width&quot;:&quot;50&quot;,&quot;datatables-columns.searchPanes.controls&quot;:&quot;false&quot;}],[1,&quot;Length&quot;,null,&quot;&quot;,[]],[1,&quot;Length&quot;,null,&quot;-n&quot;,{&quot;e&quot;:&quot;f&quot;,&quot;g&quot;:&quot;h&quot;}],[1,&quot;Weight&quot;,null,&quot;&quot;,{&quot;datatables-searchable&quot;:&quot; 1&quot;}],[1,&quot;Weight&quot;,null,&quot;-u&quot;,[]],[1,&quot;My link&quot;,null,&quot;&quot;,[]]]" data-count="149" data-editor="Admin"><div class="top"></div><div class="srf-loading-dots"></div><div id="smw-6410f268bedaf" class="datatables-container" style="display:none;"></div></div>', '#qunit-fixture' ),
		container = context.find( '.datatables-container' );

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
	 * Update testing
	 *
	 * @since  1.9
	 */
	QUnit.test( 'table init', function ( assert ) {
		assert.expect( 1 );
		var datatables = new srf.formats.datatables();

		var _modDateCase = "{\"query\":{\"result\":{\"printrequests\":[{\"label\":\"\",\"key\":\"\",\"redi\":\"\",\"typeid\":\"_wpg\",\"mode\":2,\"format\":false},{\"label\":\"Assigned to\",\"key\":\"Assigned_to\",\"redi\":\"\",\"typeid\":\"_wpg\",\"mode\":1,\"format\":\"\"},{\"label\":\"Boolean prop\",\"key\":\"Boolean_prop\",\"redi\":\"\",\"typeid\":\"_wpg\",\"mode\":1,\"format\":\"\"}],\"results\":{\"My page#mysubobjectb\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"My page#mysubobjectb\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/My_page#mysubobjectb\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"My page#mysubobject\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"My page#mysubobject\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/My_page#mysubobject\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"My page b#mysubobjectb\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"My page b#mysubobjectb\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/My_page_b#mysubobjectb\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"Main Page b\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:A\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:A\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"Main Page b\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/Main_Page_b\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"},\"Carousel test\":{\"printouts\":{\"Assigned to\":[{\"fulltext\":\"User:Admin\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/User:Admin\",\"namespace\":2,\"exists\":\"\",\"displaytitle\":\"\"}],\"Boolean prop\":[]},\"fulltext\":\"Carousel test\",\"fullurl\":\"http:\\/\\/127.0.0.1\\/mediawiki\\/index.php\\/Carousel_test\",\"namespace\":0,\"exists\":\"1\",\"displaytitle\":\"\"}},\"serializer\":\"SMW\\\\Serializers\\\\QueryResultSerializer\",\"version\":2,\"meta\":{\"hash\":\"6d7015d9df6e7fcc97a5335b055ff5ee\",\"count\":5,\"offset\":0,\"source\":\"\",\"time\":\"0.018219\"}},\"ask\":{\"conditions\":\"[[Assigned to::+]]\",\"parameters\":{\"limit\":5000,\"offset\":0,\"sortkeys\":{\"\":\"DESC\"},\"mainlabel\":\"\",\"querymode\":1,\"format\":\"datatables\",\"source\":\"\",\"link\":\"all\",\"headers\":\"plain\",\"intro\":\"\",\"outro\":\"\",\"searchlabel\":\"... further results\",\"default\":\"\",\"import-annotation\":false,\"class\":\"datatable\",\"theme\":\"bootstrap\",\"pagelength\":\"20\"},\"printouts\":[\"?Assigned to\",\"?Boolean prop\"]}},\"version\":\"0.2.5\"}"
		
		var data = JSON.parse(_modDateCase);

		datatables.init( context, container, data );

		assert.ok( $.fn.DataTable.isDataTable( container.find( 'table' ) ) , pass + 'table is DataTable' );
	} );


}( jQuery, mediaWiki, semanticFormats ) );
