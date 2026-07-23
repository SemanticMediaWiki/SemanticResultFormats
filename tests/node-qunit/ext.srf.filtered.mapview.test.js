'use strict';

// Only MapView's init() (marker/bounds-building) is covered here. show()/lateInit()
// need a real Leaflet map render plus window.matchMedia, which jsdom does not
// implement; those paths are left in the legacy browser-QUnit suite, see issue #1073.
// The base-layer resolution (buildBaseLayers/escapeHtml) is a pure helper and is
// covered separately below without a real map.

const sinon = require( 'sinon' );
const L = require( 'leaflet' );
const { MapView, buildBaseLayers, buildGeoJsonLayer, buildLayerControl, escapeHtml } = require( './.compiled/Filtered/View/MapView.js' );
const { Controller } = require( './.compiled/Filtered/Controller.js' );

QUnit.module( 'ext.srf.filtered MapView', () => {

	// The geographic printout is at index 0; the map view is configured with
	// position: 0, so markers are built from the shared per-printout values p[0].v.
	function makeRow( positions ) {
		return { p: [ { v: positions } ] };
	}

	function newMapView( data, options ) {
		const controller = new Controller( $(), data, [ {} ] );
		return new MapView( 'foo', $( '<div>' ), controller, options );
	}

	QUnit.test( 'init builds one marker per position and extends the bounds', ( assert ) => {
		const data = {
			row1: makeRow( [ { lat: 1, lng: 2 } ] ),
			row2: makeRow( [ { lat: 3, lng: 4 }, { lat: 5, lng: 6 } ] )
		};
		const mapView = newMapView( data, { position: 0 } );

		const done = assert.async();
		mapView.init().then( () => {
			assert.strictEqual( mapView.markers.row1.length, 1, 'row1 (1 position) got 1 marker' );
			assert.strictEqual( mapView.markers.row2.length, 2, 'row2 (2 positions) got 2 markers' );
			assert.true( mapView.bounds !== undefined, 'bounds were computed from the marker positions' );
			done();
		} ).catch( ( e ) => {
			assert.true( false, `init() rejected: ${ e.message }` );
			done();
		} );
	} );

	QUnit.test( 'init skips rows whose position printout is empty', ( assert ) => {
		const data = {
			row1: makeRow( [ { lat: 1, lng: 2 } ] ),
			row2: { p: [ null ] }
		};
		const mapView = newMapView( data, { position: 0 } );

		const done = assert.async();
		mapView.init().then( () => {
			assert.true( Object.prototype.hasOwnProperty.call( mapView.markers, 'row1' ), 'row1 (has coordinates) got an entry' );
			assert.false( Object.prototype.hasOwnProperty.call( mapView.markers, 'row2' ), 'row2 (empty position printout) got no entry' );
			done();
		} ).catch( ( e ) => {
			assert.true( false, `init() rejected: ${ e.message }` );
			done();
		} );
	} );

	QUnit.test( 'init reads text-property positions from the per-row fallback data', ( assert ) => {
		// Coordinates stored in a text property are parsed server-side and provided per
		// row under d[viewId].positions; no position index is configured for the view.
		const data = { row1: { p: [ null ], d: { foo: { positions: [ { lat: 7, lng: 8 } ] } } } };
		const mapView = newMapView( data, {} );

		const done = assert.async();
		mapView.init().then( () => {
			assert.strictEqual( mapView.markers.row1.length, 1, 'row1 got a marker from the fallback positions' );
			done();
		} ).catch( ( e ) => {
			assert.true( false, `init() rejected: ${ e.message }` );
			done();
		} );
	} );

	QUnit.test( 'init with no rows at all falls back to a world-spanning bounds', ( assert ) => {
		const mapView = newMapView( {}, {} );

		const done = assert.async();
		mapView.init().then( () => {
			const sw = mapView.bounds.getSouthWest();
			const ne = mapView.bounds.getNorthEast();
			assert.strictEqual( `${ sw.lat },${ sw.lng }`, '-180,-90', 'fallback bounds south-west corner' );
			assert.strictEqual( `${ ne.lat },${ ne.lng }`, '180,90', 'fallback bounds north-east corner' );
			done();
		} ).catch( ( e ) => {
			assert.true( false, `init() rejected: ${ e.message }` );
			done();
		} );
	} );

} );

QUnit.module( 'ext.srf.filtered MapView base layers', () => {

	// buildBaseLayers returns an ordered array of { label, layer }; the labels alone capture both
	// the escaping and the ordering the switcher relies on.
	function labelsOf( bases ) {
		return bases.map( ( base ) => base.label );
	}

	QUnit.test( 'a non-wms definition becomes a plain tile layer using its url and options', ( assert ) => {
		const bases = buildBaseLayers(
			[ 'Historic' ],
			{ Historic: { url: 'https://example.org/{z}/{x}/{y}.png', options: { maxZoom: 19 }, wms: false } }
		);

		assert.strictEqual( bases.length, 1, 'one base layer built' );
		assert.strictEqual( bases[ 0 ].label, 'Historic', 'labelled by its name' );
		assert.true( bases[ 0 ].layer instanceof L.TileLayer, 'built an L.TileLayer' );
		assert.false( bases[ 0 ].layer instanceof L.TileLayer.WMS, 'and not a WMS layer' );
		assert.strictEqual( bases[ 0 ].layer._url, 'https://example.org/{z}/{x}/{y}.png', 'used the definition url' );
		assert.strictEqual( bases[ 0 ].layer.options.maxZoom, 19, 'passed the definition options through' );
	} );

	QUnit.test( 'a wms definition becomes a WMS tile layer', ( assert ) => {
		const bases = buildBaseLayers(
			[ 'Terrestris' ],
			{ Terrestris: { url: 'https://ows.example.org/service', options: { layers: 'OSM-WMS' }, wms: true } }
		);

		assert.true( bases[ 0 ].layer instanceof L.TileLayer.WMS, 'built an L.TileLayer.WMS' );
		assert.strictEqual( bases[ 0 ].layer.wmsParams.layers, 'OSM-WMS', 'passed the wms layers option through' );
	} );

	QUnit.test( 'a name without a definition falls back to a leaflet-providers provider', ( assert ) => {
		const bases = buildBaseLayers( [ 'OpenStreetMap.Mapnik' ], {} );

		assert.true( bases[ 0 ].layer instanceof L.TileLayer, 'built a provider tile layer' );
		assert.false( bases[ 0 ].layer instanceof L.TileLayer.WMS, 'and not a WMS layer' );
	} );

	QUnit.test( 'a definition is preferred over a provider of the same name', ( assert ) => {
		const bases = buildBaseLayers(
			[ 'OpenStreetMap.Mapnik' ],
			{ 'OpenStreetMap.Mapnik': { url: 'https://custom.example.org/{z}/{x}/{y}.png', options: {} } }
		);

		assert.strictEqual( bases[ 0 ].layer._url, 'https://custom.example.org/{z}/{x}/{y}.png', 'used the definition, not the provider' );
	} );

	QUnit.test( 'an unknown provider name is skipped with a warning while the rest are built', ( assert ) => {
		const warn = sinon.stub( console, 'warn' );

		const bases = buildBaseLayers( [ 'No.Such.Provider', 'OpenStreetMap.Mapnik' ], {} );

		assert.deepEqual(
			labelsOf( bases ),
			[ 'OpenStreetMap.Mapnik' ],
			'the unknown provider was skipped and the following valid provider kept'
		);
		assert.true( warn.calledOnce, 'a warning was emitted for the unknown provider' );

		warn.restore();
	} );

	QUnit.test( 'a skipped first name leaves the following valid names in order', ( assert ) => {
		const warn = sinon.stub( console, 'warn' );
		const definition = { url: 'https://example.org/{z}/{x}/{y}.png', options: {} };

		const bases = buildBaseLayers(
			[ 'No.Such.Provider', 'First', 'Second' ],
			{ First: definition, Second: definition }
		);

		assert.deepEqual(
			labelsOf( bases ),
			[ 'First', 'Second' ],
			'the valid names following the skipped one keep their order'
		);

		warn.restore();
	} );

	QUnit.test( 'numeric layer names keep their given order instead of sorting ascending', ( assert ) => {
		const definition = { url: 'https://example.org/{z}/{x}/{y}.png', options: {} };

		const bases = buildBaseLayers(
			[ 'Modern', '1920', '1890' ],
			{ Modern: definition, 1920: definition, 1890: definition }
		);

		assert.deepEqual(
			labelsOf( bases ),
			[ 'Modern', '1920', '1890' ],
			'purely-numeric names are not reordered ahead of the rest'
		);
	} );

	QUnit.test( 'an all-invalid name list builds no base layers', ( assert ) => {
		const warn = sinon.stub( console, 'warn' );

		const bases = buildBaseLayers( [ 'No.Such.Provider', 'Also.Missing' ], {} );

		assert.deepEqual( bases, [], 'nothing is built when every name is invalid' );

		warn.restore();
	} );

	QUnit.test( 'an empty name list builds no base layers', ( assert ) => {
		assert.deepEqual( buildBaseLayers( [], {} ), [], 'nothing is built for an empty list' );
	} );

	QUnit.test( 'base layers keep the given order so the first name is the initially active one', ( assert ) => {
		const bases = buildBaseLayers(
			[ 'OpenStreetMap.DE', 'OpenStreetMap.Mapnik', 'OpenStreetMap.HOT' ],
			{}
		);

		assert.deepEqual(
			labelsOf( bases ),
			[ 'OpenStreetMap.DE', 'OpenStreetMap.Mapnik', 'OpenStreetMap.HOT' ],
			'the base layers are in the requested order'
		);
	} );

	QUnit.test( 'layer names are html-escaped for use as switcher labels', ( assert ) => {
		const name = 'Evil <img src=x> & "friends"';
		const bases = buildBaseLayers(
			[ name ],
			{ [ name ]: { url: 'https://example.org/{z}/{x}/{y}.png', options: {} } }
		);

		assert.strictEqual(
			bases[ 0 ].label,
			'Evil &lt;img src=x&gt; &amp; &quot;friends&quot;',
			'the switcher label is html-escaped'
		);
	} );

	QUnit.test( 'escapeHtml escapes the html-significant characters like mw.html.escape', ( assert ) => {
		assert.strictEqual( escapeHtml( '<a>&"\'' ), '&lt;a&gt;&amp;&quot;&#039;' );
	} );

} );

QUnit.module( 'ext.srf.filtered MapView geojson overlay', () => {

	const line = { type: 'LineString', coordinates: [ [ 16.3, 48.2 ], [ 16.4, 48.25 ] ] };
	const polygon = { type: 'Polygon', coordinates: [ [ [ 16.3, 48.2 ], [ 16.4, 48.2 ], [ 16.4, 48.25 ], [ 16.3, 48.2 ] ] ] };
	const point = { type: 'Point', coordinates: [ 16.37, 48.21 ] };

	function feature( geometry, properties ) {
		return { type: 'FeatureCollection', features: [ { type: 'Feature', properties: properties, geometry: geometry } ] };
	}

	function onlyLayerOf( geojson ) {
		return buildGeoJsonLayer( geojson ).getLayers()[ 0 ];
	}

	function newMapView( options ) {
		return new MapView( 'foo', $( '<div>' ), new Controller( $(), {}, [ {} ] ), options );
	}

	QUnit.test( 'a LineString is styled per the simplestyle spec', ( assert ) => {
		const layer = onlyLayerOf( feature( line, { stroke: '#ff0000', 'stroke-width': 4, 'stroke-opacity': 0.5 } ) );

		assert.strictEqual( layer.options.color, '#ff0000', 'stroke maps to color' );
		assert.strictEqual( layer.options.weight, 4, 'stroke-width maps to weight' );
		assert.strictEqual( layer.options.opacity, 0.5, 'stroke-opacity maps to opacity' );
	} );

	QUnit.test( 'a Polygon is filled per the simplestyle spec', ( assert ) => {
		const layer = onlyLayerOf( feature( polygon, { fill: '#00ff00', 'fill-opacity': 0.3 } ) );

		assert.strictEqual( layer.options.fillColor, '#00ff00', 'fill maps to fillColor' );
		assert.strictEqual( layer.options.fillOpacity, 0.3, 'fill-opacity maps to fillOpacity' );
	} );

	QUnit.test( 'a feature without style properties keeps the leaflet default path style', ( assert ) => {
		const layer = onlyLayerOf( feature( line, {} ) );

		assert.strictEqual( layer.options.color, '#3388ff', 'the leaflet default path colour is kept' );
	} );

	QUnit.test( 'title and description become an escaped popup', ( assert ) => {
		const layer = onlyLayerOf( feature( point, { title: 'Karlskirche', description: 'A <b>baroque</b> church' } ) );
		const content = layer.getPopup().getContent();

		assert.true( content.includes( 'Karlskirche' ), 'the popup contains the title' );
		assert.true( content.includes( 'A ' ), 'the popup contains the description' );
		assert.false( content.includes( '<b>baroque' ), 'raw html in the description is not injected' );
		assert.true( content.includes( '&lt;b&gt;baroque' ), 'html in the description is escaped' );
	} );

	QUnit.test( 'a feature without title or description gets no popup', ( assert ) => {
		const layer = onlyLayerOf( feature( point, { 'marker-color': '#ff0000' } ) );

		assert.strictEqual( layer.getPopup(), undefined, 'no popup is bound' );
	} );

	QUnit.test( 'a Point feature becomes a default marker', ( assert ) => {
		const layer = onlyLayerOf( feature( point, {} ) );

		assert.true( layer instanceof L.Marker, 'the point is rendered as a default marker' );
	} );

	QUnit.test( 'point features use the provided marker icon instead of the pathless Leaflet default', ( assert ) => {
		const icon = new L.Icon( { iconUrl: 'https://example.org/pin.png' } );

		const layer = buildGeoJsonLayer( feature( point, {} ), icon ).getLayers()[ 0 ];

		assert.strictEqual( layer.options.icon, icon, 'the marker was created with the given icon' );
	} );

	QUnit.test( 'a non-string title is coerced to its string form for the popup', ( assert ) => {
		const layer = onlyLayerOf( feature( point, { title: 2024 } ) );

		assert.strictEqual( layer.getPopup().getContent(), '2024', 'the numeric title becomes its string form' );
	} );

	QUnit.test( 'a malformed geojson overlay is skipped instead of throwing', ( assert ) => {
		const warn = sinon.stub( console, 'warn' );
		// The overlay build throws before the map is touched, so no rendered map is needed here.
		const mapView = newMapView( { geojson: { foo: 'bar' } } );

		try {
			const result = mapView.addGeoJsonOverlay( null );

			assert.strictEqual( result, null, 'the invalid overlay degrades to no layer' );
			assert.true( warn.calledOnce, 'a warning was emitted for the invalid overlay' );
		} finally {
			warn.restore();
		}
	} );

} );

QUnit.module( 'ext.srf.filtered MapView layer control', () => {

	function tile() {
		return L.tileLayer( 'https://example.org/{z}/{x}/{y}.png' );
	}

	function base( label ) {
		return { label: label, layer: tile() };
	}

	// Leaflet's layers control stores registered layers as { layer, name, overlay } entries;
	// the base entries (overlay falsy), in registration order, give the switcher ordering.
	function baseNamesOf( control ) {
		return control._layers.filter( ( entry ) => !entry.overlay ).map( ( entry ) => entry.name );
	}

	QUnit.test( 'two base layers and no overlay get a control listing both bases in order', ( assert ) => {
		const control = buildLayerControl( [ base( 'A' ), base( 'B' ) ], {} );

		assert.notStrictEqual( control, null, 'a control is built for two base layers' );
		assert.deepEqual( baseNamesOf( control ), [ 'A', 'B' ], 'both bases are registered in the given order' );
	} );

	QUnit.test( 'a single base layer and no overlay get no control', ( assert ) => {
		const control = buildLayerControl( [ base( 'A' ) ], {} );

		assert.strictEqual( control, null, 'no control is built for a single base and no overlay' );
	} );

	QUnit.test( 'a single base layer and one overlay get a control listing both', ( assert ) => {
		const control = buildLayerControl( [ base( 'A' ) ], { Overlay: tile() } );

		assert.notStrictEqual( control, null, 'a control is built when an overlay is present' );
		assert.strictEqual( control._layers.length, 2, 'the base and the overlay are registered' );
	} );

	QUnit.test( 'an overlay with no base layer still gets a control', ( assert ) => {
		const control = buildLayerControl( [], { Overlay: tile() } );

		assert.notStrictEqual( control, null, 'a control is built for an overlay even without base layers' );
	} );

} );
