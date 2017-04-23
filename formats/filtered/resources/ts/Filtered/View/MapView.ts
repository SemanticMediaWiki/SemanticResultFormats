///<reference types="jquery"/>

import { View } from "./View";

export class MapView extends View {

	private map: L.Map = undefined;
	private markers: { [key: string]: L.Marker[] } = undefined;
	private markerClusterGroup: L.MarkerClusterGroup = undefined;
	private bounds: L.LatLngBounds = undefined;
	private initialized: boolean = false;

	public init() {

		let data = this.controller.getData();
		let markers: { [key: string]: L.Marker[] } = {};
		let bounds: L.LatLngBounds = undefined;

		let markerClusterGroup: L.MarkerClusterGroup = L.markerClusterGroup({
			animateAddingMarkers: true,
		});

		for ( let rowId in data ) {

			let positions: L.LatLngLiteral[] = data[ rowId ][ 'data' ][ this.id ][ 'positions' ];
			markers[ rowId ] = [];

			for ( let pos of positions ) {

				bounds = ( bounds === undefined ) ? new L.LatLngBounds( pos, pos ) : bounds.extend( pos );

				let marker = this.getMarker( pos, data[ rowId ] );
				markers[ rowId ].push( marker );
				markerClusterGroup.addLayer( marker );
			}
		}

		this.markerClusterGroup = markerClusterGroup;
		this.markers = markers;
		this.bounds = bounds;

		return super.init();
	}

	private getMarker( latLng: L.LatLngExpression, row: any ) {
		let title = undefined;
		let popup = [];

		// TODO: Use <div> instead of <b> and do CSS styling

		for ( let prId in row[ 'printouts' ] ) {
			let pr = row[ 'printouts' ][ prId ];
			if ( title === undefined ) {
				title = pr.values.join( ', ' );
				popup.push( '<b>' + title + '</b>' );
			} else {
				popup.push( (pr.label ? '<b>' + pr.label + ':</b> ' : '') + pr.values.join( ', ' ) )

			}
		}

		let marker = L.marker( latLng, { title: title } );
		marker.bindPopup( popup.join( '<br>' ) );
		return marker;
	}

	private lateInit() {

		if ( this.initialized ) {
			return;
		}

		this.initialized = true;

		this.map = L.map( this.getTargetElement().get( 0 ) )
		.addLayer( L.tileLayer( 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: ''
		} ) )
		.addLayer( this.markerClusterGroup )
		.fitBounds( this.bounds );
	}

	public showRows( rowIds: string[] ) {
		let markers: L.Layer[][] = rowIds.map( ( rowId: string ) => this.markers[ rowId ] );
		this.markerClusterGroup.addLayers( markers.reduce( ( result: L.Layer[], layers: L.Layer[] ) => result.concat( layers ) ) );
	}

	public hideRows( rowIds: string[] ) {
		let markers: L.Layer[][] = rowIds.map( ( rowId: string ) => this.markers[ rowId ] );
		this.markerClusterGroup.removeLayers( markers.reduce( ( result: L.Layer[], layers: L.Layer[] ) => result.concat( layers ) ) );
	}

	public show() {
		super.show();
		this.lateInit();
	}

}
