/// <reference types="leaflet" />

import { View } from "./View";
import { Options } from "../../types"

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

		this.leafletPromise = mw.loader.using( 'ext.srf.filtered.map-view.leaflet' )
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

				if ( data[ rowId ][ 'data' ].hasOwnProperty( this.id ) ) {
					let positions: L.LatLngLiteral[] = data[ rowId ][ 'data' ][ this.id ][ 'positions' ];
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

			let vals: string[] = row[ 'printouts' ][ this.options[ 'marker icon property' ] ][ 'values' ];

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

		for ( let prId in row[ 'printouts' ] ) {
			let printrequest = (this.controller.getPrintRequests())[ prId ];

			if ( ! printrequest.hasOwnProperty('hide') || printrequest.hide === false ) {
				let printouts = row[ 'printouts' ][ prId ];

				if ( title === undefined ) {
					title = printouts[ 'values' ].join( ', ' );
					popup.push( '<b>' + printouts[ 'formatted values' ].join( ', ' ) + '</b>' );
				} else {
					popup.push( (printouts.label ? '<b>' + printouts.label + ':</b> ' : '') + printouts[ 'formatted values' ].join( ', ' ) )
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

			if ( this.options.hasOwnProperty( 'map provider' ) ) {
				L.tileLayer.provider( this.options[ 'map provider' ] ).addTo( that.map );
			}

			if ( !mapOptions.hasOwnProperty( 'zoom' ) ) {
				that.map.fitBounds( that.bounds );
			}

		} );

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
