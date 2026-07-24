/// <reference types="leaflet" />
import * as L from 'leaflet';
import 'leaflet.markercluster';
import 'leaflet-providers';

import { View } from "./View";
import { Options, printoutValues, printoutFormattedValues } from "../../types"

declare let mw: any;

export class MapView extends View {

	private map: L.Map = undefined;
	private icon: { [key: string]: L.Icon } = undefined;
	private markers: { [key: string]: L.Marker[] } = undefined;
	private markerClusterGroup: L.MarkerClusterGroup = undefined;
	private bounds: L.LatLngBounds = undefined;
	private initialized: boolean = false;

	private zoom: number = -1;
	private minZoom: number = -1;
	private maxZoom: number = -1;

	private leafletPromise: Promise<any> = undefined;

	public init(): Promise<any> {

		let data = this.controller.getData();
		let markers: { [rowId: string]: L.Marker[] } = {};

		if ( this.options.hasOwnProperty( 'height' ) ) {
			this.target.height( this.options.height );
		}

		this.leafletPromise = mw.loader.using( 'ext.srf.filtered.map-leaflet.style' )
		.then( () => {

			let bounds: L.LatLngBounds = undefined;
			let disableClusteringAtZoom = this.getZoomForUnclustering();

			let clusterOptions: Options = {
				animateAddingMarkers: true,
				disableClusteringAtZoom: disableClusteringAtZoom,
				spiderfyOnMaxZoom: disableClusteringAtZoom === null
			};

			clusterOptions = this.getOptions( [ 'maxClusterRadius', 'zoomToBoundsOnClick' ], clusterOptions );

			let markerClusterGroup: L.MarkerClusterGroup = L.markerClusterGroup( clusterOptions );

			for ( let rowId in data ) {

				let positions = this.getPositions( data[ rowId ] );

				if ( positions !== undefined && positions.length > 0 ) {
					markers[ rowId ] = [];

					for ( let pos of positions ) {

						bounds = ( bounds === undefined ) ? new L.LatLngBounds( pos, pos ) : bounds.extend( pos );

						let marker = this.getMarker( pos, data[ rowId ] );
						markers[ rowId ].push( marker );
						markerClusterGroup.addLayer( marker );
					}
				}
			}

			this.markerClusterGroup = markerClusterGroup;
			this.markers = markers;
			this.bounds = ( bounds === undefined ) ? new L.LatLngBounds( [ -180, -90 ], [ 180, 90 ] ) : bounds;
		} );

		return this.leafletPromise;
	}

	// Geographic coordinates are read from the shared per-printout values
	// (p[position].v). Coordinates stored in a text property are parsed
	// server-side and provided per row (d[viewId].positions) instead.
	private getPositions( row: any ): L.LatLngLiteral[] | undefined {
		if ( row.d && row.d[ this.id ] && row.d[ this.id ].positions ) {
			return row.d[ this.id ].positions;
		}

		if ( this.options.hasOwnProperty( 'position' ) ) {
			let slot = row.p[ this.options[ 'position' ] ];
			return slot ? slot.v : undefined;
		}

		return undefined;
	}

	/**
	 * Detects if user uses dark theme
	 * @returns {boolean}
	 */
	private isUserUsesDarkMode() {
		return window.matchMedia("(prefers-color-scheme: dark)").matches;
	}

	private getZoomForUnclustering() {

		if ( this.options.hasOwnProperty( 'marker cluster' ) && this.options[ 'marker cluster' ] === false ) {
			return 0;
		}

		if ( this.options.hasOwnProperty( 'marker cluster max zoom' ) ) {
			return this.options[ 'marker cluster max zoom' ] + 1;
		}

		return null;
	}

	private getIcon( row: any ) {

		if ( this.icon === undefined ) {
			this.buildIconList();
		}

		if ( this.options.hasOwnProperty( 'marker icon property' ) ) {

			let vals: string[] = printoutValues( row.p[ this.options[ 'marker icon property' ] ] );

			if ( vals.length > 0 && this.icon.hasOwnProperty( vals[ 0 ] ) ) {
				return this.icon[ vals[ 0 ] ];
			}
		}

		return this.icon[ 'default' ];
	}

	private buildIconList() {
		this.icon = {};

		let iconPath = this.controller.getPath() + 'css/images/';

		this.icon[ 'default' ] = new L.Icon( {
			'iconUrl': iconPath + 'marker-icon.png',
			'iconRetinaUrl': iconPath + 'marker-icon-2x.png',
			'shadowUrl': iconPath + 'marker-shadow.png',
			'iconSize': [ 25, 41 ],
			'iconAnchor': [ 12, 41 ],
			'popupAnchor': [ 1, -34 ],
			// 'tooltipAnchor': [16, -28],
			'shadowSize': [ 41, 41 ]
		} );

		if ( this.options.hasOwnProperty( 'marker icons' ) ) {

			for ( let value in this.options[ 'marker icons' ] ) {
				this.icon[ value ] = new L.Icon( {
					'iconUrl': this.options[ 'marker icons' ][ value ],
					// 'iconRetinaUrl': iconPath + 'marker-icon-2x.png',
					'shadowUrl': iconPath + 'marker-shadow.png',
					'iconSize': [ 32, 32 ],
					'iconAnchor': [ 16, 32 ],
					'popupAnchor': [ 1, -30 ],
					// 'tooltipAnchor': [16, -28],
					'shadowSize': [ 41, 41 ],
					'shadowAnchor': [ 12, 41 ]
				} );
			}
		}
	}

	private getMarker( latLng: L.LatLngExpression, row: any ) {
		let title = undefined;
		let popup = [];

		// TODO: Use <div> instead of <b> and do CSS styling

		for ( let prId in row.p ) {
			let printrequest = (this.controller.getPrintRequests())[ prId ];

			if ( ! printrequest.hasOwnProperty('hide') || printrequest.hide === false ) {
				let slot = row.p[ prId ];
				let formatted = printoutFormattedValues( slot );

				if ( title === undefined ) {
					title = printoutValues( slot ).join( ', ' );
					popup.push( '<b>' + formatted.join( ', ' ) + '</b>' );
				} else {
					popup.push( (slot && slot.label ? '<b>' + slot.label + ':</b> ' : '') + formatted.join( ', ' ) )
				}
			}
		}

		let marker = L.marker( latLng, { title: title, alt: title } );
		marker.bindPopup( popup.join( '<br>' ) );

		marker.setIcon( this.getIcon( row ) );
		return marker;
	}

	public lateInit() {

		if ( this.initialized ) {
			return;
		}

		this.initialized = true;

		let that = this;

		this.leafletPromise.then( () => {

			let mapOptions: Options = {
				center: this.bounds !== undefined ? this.bounds.getCenter() : [ 0, 0 ]
			};

			mapOptions = that.getOptions( [ 'zoom', 'minZoom', 'maxZoom' ], mapOptions );

			// TODO: Limit zoom values to map max zoom

			that.map = L.map( <HTMLElement> that.getTargetElement().get( 0 ), mapOptions );
			that.map.addLayer( that.markerClusterGroup );

			let geoJsonLayer = that.addGeoJsonOverlay( that.map );

			let overlays: { [ label: string ]: L.Layer } = {};
			if ( geoJsonLayer !== null ) {
				overlays[ escapeHtml( that.options[ 'geojson source' ] || '' ) ] = geoJsonLayer;
			}

			that.addBaseLayers( that.map, overlays );

			if ( !mapOptions.hasOwnProperty( 'zoom' ) ) {
				that.map.fitBounds( that.getFitBounds( geoJsonLayer ) );
			}

		} );

	}

	// Builds the GeoJSON overlay from the 'geojson' option (if present) and adds it to the map as a
	// standalone layer — deliberately not part of the marker cluster group — so that the marker
	// filtering (showRows/hideRows, which add/remove from the cluster group) cannot touch it. It is
	// added after the cluster group so its paths render above the base tiles. Returns the layer, or
	// null when no GeoJSON is configured.
	private addGeoJsonOverlay( map: L.Map ): L.GeoJSON | null {
		if ( !this.options.hasOwnProperty( 'geojson' ) || !this.options[ 'geojson' ] ) {
			return null;
		}

		try {
			let layer = buildGeoJsonLayer( this.options[ 'geojson' ], this.icon[ 'default' ] );
			layer.addTo( map );
			return layer;
		} catch ( error ) {
			if ( typeof console !== 'undefined' && console.warn ) {
				console.warn( 'srf.filtered.map: skipping invalid GeoJSON overlay', error );
			}

			return null;
		}
	}

	// The map fits the marker bounds; when a GeoJSON overlay is present, its bounds are unioned in
	// so the overlay is visible on load. When there are no markers at all, this.bounds is the
	// world-spanning fallback, so the overlay bounds are used on their own instead.
	private getFitBounds( geoJsonLayer: L.GeoJSON | null ): L.LatLngBounds {
		if ( geoJsonLayer === null ) {
			return this.bounds;
		}

		let geoBounds = geoJsonLayer.getBounds();

		if ( !geoBounds.isValid() ) {
			return this.bounds;
		}

		if ( this.hasMarkers() ) {
			return L.latLngBounds( this.bounds.getSouthWest(), this.bounds.getNorthEast() ).extend( geoBounds );
		}

		return geoBounds;
	}

	private hasMarkers(): boolean {
		return Object.keys( this.markers ).length > 0;
	}

	// Adds the map's base tile layer(s) and registers them, together with any overlays, into a
	// single layer control. When the 'layers' option is set, its names become the base layers
	// (first initially active); otherwise a single provider layer is used, honouring the dark-mode
	// provider override. The control is shown when there is more than one base to switch between, or
	// at least one overlay to toggle.
	private addBaseLayers( map: L.Map, overlays: { [ label: string ]: L.Layer } ) {
		let bases = this.buildBases();

		if ( bases.length > 0 ) {
			bases[ 0 ].layer.addTo( map );
		} else if ( this.options.hasOwnProperty( 'layers' ) && this.options[ 'layers' ].length > 0 ) {
			if ( typeof console !== 'undefined' && console.warn ) {
				console.warn( 'srf.filtered.map: no configured base layer could be built' );
			}
		}

		let control = buildLayerControl( bases, overlays );

		if ( control !== null ) {
			control.addTo( map );
		}
	}

	private buildBases(): BaseLayer[] {
		if ( this.options.hasOwnProperty( 'layers' ) && this.options[ 'layers' ].length > 0 ) {
			return buildBaseLayers( this.options[ 'layers' ], this.options[ 'layer definitions' ] || {} );
		}

		return this.buildProviderBase();
	}

	private buildProviderBase(): BaseLayer[] {
		let mapProvider = null;

		if ( this.options.hasOwnProperty( 'map provider' ) ) {
			mapProvider = this.options[ 'map provider' ];
		}

		if ( this.isUserUsesDarkMode() && this.options[ 'map provider dark' ] ) {
			mapProvider = this.options[ 'map provider dark' ];
		}

		if ( !mapProvider ) {
			return [];
		}

		return [ { label: escapeHtml( mapProvider ), layer: L.tileLayer.provider( mapProvider ) } ];
	}

	public getOptions( keys: string[], defaults: Options = {} ) {

		for ( let key of keys ) {
			if ( this.options.hasOwnProperty( key ) ) {
				defaults[ key ] = this.options[ key ];
			}
		}

		return defaults;
	}

	public showRows( rowIds: string[] ) {
		this.leafletPromise.then( () => {
			this.manipulateLayers( rowIds, ( layers: L.Layer[] ) => {
				this.markerClusterGroup.addLayers( layers )
			} )
		} );
	}

	public hideRows( rowIds: string[] ) {
		this.leafletPromise.then( () => {
			this.manipulateLayers( rowIds, ( layers: L.Layer[] ) => {
				this.markerClusterGroup.removeLayers( layers )
			} )
		} );
	}

	private manipulateLayers( rowIds: string[], cb: ( layers: L.Layer[] ) => void ) {

		let layersFromRowIds = this.getLayersFromRowIds( rowIds );

		if ( layersFromRowIds.length > 0 ) {
			cb( layersFromRowIds );
		}

	}

	private getLayersFromRowIds( rowIds: string[] ) {
		return this.flatten( this.getLayersFromRowIdsRaw( rowIds ) );
	}

	private getLayersFromRowIdsRaw( rowIds: string[] ) {
		return rowIds.map( ( rowId: string ) => this.markers[ rowId ] ? this.markers[ rowId ] : [] );
	}

	private flatten( markers: L.Layer[][] ): L.Layer[] {
		return markers.reduce( ( result: L.Layer[], layers: L.Layer[] ) => result.concat( layers ), [] );
	}

	public show() {
		super.show();
		this.lateInit();
	}

}

export interface LayerDefinition {
	url: string;
	options?: any;
	wms?: boolean;
}

// A base layer paired with its already-HTML-escaped switcher label (Leaflet injects the
// layer-control labels as HTML, and the names come from wiki editors via the query parameter).
export interface BaseLayer {
	label: string;
	layer: L.Layer;
}

// Builds the map's base layers in order. Names with a custom definition become tile/wms layers; the
// rest are treated as leaflet-providers provider strings. Names that resolve to neither are skipped
// so one bad name cannot break the map. The result is an ordered array rather than an object keyed
// by name so that purely-numeric names (e.g. historic layers "1890"/"1920") keep their given order —
// object enumeration would surface integer-like keys first in ascending order — which matters because
// the first base layer is the initially-active one and the array sets the switcher order.
export function buildBaseLayers(
	names: string[],
	definitions: { [ name: string ]: LayerDefinition }
): BaseLayer[] {
	let bases: BaseLayer[] = [];

	for ( let name of names ) {
		let layer = buildBaseLayer( name, definitions );

		if ( layer !== null ) {
			bases.push( { label: escapeHtml( name ), layer: layer } );
		}
	}

	return bases;
}

// Builds the map's layer control from its base layers and overlays, or returns null when a control
// would serve no purpose: it is shown when there is more than one base layer to switch between, or
// at least one overlay to toggle. The bases are registered in array order; the overlays are keyed by
// an already-HTML-escaped label.
export function buildLayerControl(
	bases: BaseLayer[],
	overlays: { [ label: string ]: L.Layer }
): L.Control.Layers | null {
	if ( bases.length > 1 || Object.keys( overlays ).length > 0 ) {
		let control = L.control.layers();

		for ( let base of bases ) {
			control.addBaseLayer( base.layer, base.label );
		}

		for ( let label of Object.keys( overlays ) ) {
			control.addOverlay( overlays[ label ], label );
		}

		return control;
	}

	return null;
}

// Builds a Leaflet GeoJSON overlay from a parsed GeoJSON object, honouring the mapbox
// simplestyle-spec properties (stroke/fill on paths) and rendering title/description feature
// properties as popups. Ported minimally from the Maps extension's resources/leaflet/GeoJson.js;
// point features use Leaflet's default marker (no marker-colour icon machinery).
export function buildGeoJsonLayer( geojson: any, markerIcon?: L.Icon ): L.GeoJSON {
	return L.geoJSON( geojson, {
		// The bundled Leaflet cannot auto-detect its default icon path, so point features get the
		// same explicitly-pathed icon as the query-result markers.
		pointToLayer: ( feature: any, latlng: L.LatLng ) =>
			L.marker( latlng, markerIcon ? { icon: markerIcon } : {} ),
		style: ( feature: any ) => simpleStyleToPathOptions( feature && feature.properties ),
		onEachFeature: ( feature: any, layer: L.Layer ) => {
			let popup = popupContentFromProperties( feature && feature.properties );

			if ( popup !== '' ) {
				layer.bindPopup( popup );
			}
		}
	} );
}

// https://github.com/mapbox/simplestyle-spec/tree/master/1.1.0 -> https://leafletjs.com/reference.html#path
const simpleStyleToLeaflet: { [ key: string ]: string } = {
	'stroke': 'color',
	'stroke-width': 'weight',
	'stroke-opacity': 'opacity',
	'fill': 'fillColor',
	'fill-opacity': 'fillOpacity'
};

function simpleStyleToPathOptions( properties: any ): any {
	let options: { [ key: string ]: any } = {};

	if ( !properties ) {
		return options;
	}

	for ( let key of Object.keys( simpleStyleToLeaflet ) ) {
		if ( properties[ key ] !== undefined && properties[ key ] !== null ) {
			options[ simpleStyleToLeaflet[ key ] ] = properties[ key ];
		}
	}

	return options;
}

function popupContentFromProperties( properties: any ): string {
	if ( !properties || ( !properties.title && !properties.description ) ) {
		return '';
	}

	if ( !properties.description ) {
		return escapeHtml( properties.title );
	}

	if ( !properties.title ) {
		return escapeHtml( properties.description );
	}

	return '<strong>' + escapeHtml( properties.title ) + '</strong><br>' + escapeHtml( properties.description );
}

function buildBaseLayer(
	name: string,
	definitions: { [ name: string ]: LayerDefinition }
): L.Layer | null {
	if ( Object.prototype.hasOwnProperty.call( definitions, name ) ) {
		let definition = definitions[ name ];

		return definition.wms ?
			L.tileLayer.wms( definition.url, definition.options ) :
			L.tileLayer( definition.url, definition.options );
	}

	try {
		return L.tileLayer.provider( name );
	} catch ( error ) {
		if ( typeof console !== 'undefined' && console.warn ) {
			console.warn( 'srf.filtered.map: skipping unknown base layer "' + name + '"', error );
		}

		return null;
	}
}

// Mirrors mw.html.escape; implemented locally because the node-qunit test environment has mw but
// not the mw.html.escape helper. Non-string input is coerced with String() the way assigning to a
// DOM element's innerText would, giving Maps-parity for numeric GeoJSON feature titles.
export function escapeHtml( text: any ): string {
	return String( text )
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' )
		.replace( /"/g, '&quot;' )
		.replace( /'/g, '&#039;' );
}
