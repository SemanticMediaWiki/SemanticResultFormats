import { View } from "./View";

export class MapView extends View {

	private map: L.Map = undefined;
	private icon: L.Icon = undefined;
	private markers: { [key: string]: L.Marker[] } = undefined;
	private markerClusterGroup: L.MarkerClusterGroup = undefined;
	private bounds: L.LatLngBounds = undefined;
	private initialized: boolean = false;

	public init() {

		let data = this.controller.getData();
		let markers: { [rowId: string]: L.Marker[] } = {};
		let bounds: L.LatLngBounds = undefined;

		let markerClusterGroup: L.MarkerClusterGroup = L.markerClusterGroup( {
			animateAddingMarkers: true
		} );

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

	private getIcon() {
		if ( this.icon === undefined ) {

			let iconPath = this.controller.getPath() + 'css/images/';

			this.icon = new L.Icon( {
				'iconUrl': iconPath + 'marker-icon.png',
				'iconRetinaUrl': iconPath + 'marker-icon-2x.png',
				'shadowUrl': iconPath + 'marker-shadow.png',
				'iconSize':    [25, 41],
				'iconAnchor':  [12, 41],
				'popupAnchor': [1, -34],
				// 'tooltipAnchor': [16, -28],
				'shadowSize':  [41, 41]
			} );
		}

		return this.icon;
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

		marker.setIcon( this.getIcon() );
		return marker;
	}

	public lateInit() {

		if ( this.initialized ) {
			return;
		}

		this.initialized = true;

		let that = this;
		$( () => { // as soon as the document is ready
			setTimeout( () => { // let everybody else do their job
				that.map = L.map( that.getTargetElement().get( 0 ) )
				.addLayer( L.tileLayer( 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					attribution: ''
				} ) )
				.addLayer( that.markerClusterGroup )
				.fitBounds( that.bounds )
			}, 0 );
		} );

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
