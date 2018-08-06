import { Filter } from "./Filter";

declare let mw: any;

export class DistanceFilter extends Filter {

	private static readonly earthRadius: { [key: string]: number } = {
		m: 6371008.8,
		km: 6371.0088,
		mi: 3958.7613,
		nm: 3440.0695,
		Å: 63710088000000000
	};

	private earthRadiusValue: number = DistanceFilter.earthRadius.km;
	private filterValue: number = 0;

	public init() {

		let values = this.controller.getData();

		let origin = this.options[ 'origin' ];

		if ( !( origin !== undefined && origin.hasOwnProperty( 'lat' ) && origin.hasOwnProperty( 'lng' ) ) ) {
			this.target.detach();
			return;
		}

		let unit = 'km';

		if ( this.options[ 'unit' ] && DistanceFilter.earthRadius[ this.options[ 'unit' ] ] ) {
			unit = this.options[ 'unit' ];
		}

		this.earthRadiusValue = DistanceFilter.earthRadius[ unit ];

		let maxValue: number = this.updateDistances( origin );

		let precision = 10 ** ( Math.floor( Math.log( maxValue ) * Math.LOG10E ) - 1);

		if ( this.options[ 'max' ] !== undefined && this.options[ 'max' ] > maxValue ) {
			maxValue = this.options[ 'max' ];
		} else {
			maxValue = Math.ceil( maxValue / precision ) * precision;
		}

		this.filterValue = this.options[ 'initial value' ] ? Math.min( this.options[ 'initial value' ], maxValue ) : maxValue;

		// build filter controls
		let filtercontrols = this.buildEmptyControl();

		let readout = $( '<div class="filtered-distance-readout">' + this.filterValue + '</div>' );

		let table = $( '<table class="filtered-distance-table"><tbody><tr><td class="filtered-distance-min-cell">0</td>' +
			'<td class="filtered-distance-slider-cell"><div class="filtered-distance-slider"></div></td>' +
			'<td class="filtered-distance-max-cell">' + maxValue + '</td></tr>' +
			'<tr><td colspan=3 class="filtered-distance-unit-cell">' + unit + '</td></tr></tbody></table>' );

		filtercontrols.append( table );

		let that = this;
		mw.loader.using( 'jquery.ui.slider' ).then( function () {

			table.find( '.filtered-distance-slider' )
			.slider( {
				animate: true,
				max: maxValue,
				value: that.filterValue,
				step: precision / 100
			} )
			.on( 'slidechange', undefined, { 'filter': that }, function ( eventObject: JQueryEventObject, ui: any ) {
				eventObject.data.ui = ui;
				eventObject.data.filter.onFilterUpdated( eventObject );
			} )
			.on( 'slide', undefined, { 'filter': that }, function ( eventObject: JQueryEventObject, ui: any ) {
				readout.text( ui.value );
			} )
			.find( '.ui-slider-handle' )
			.append( readout );

		} );

		return this;
	}

	private updateDistances( origin: L.LatLngLiteral ): number {

		let values = this.controller.getData();
		let max = 1;

		let prId = this.printrequestId;

		for ( let rowId in values ) {

			if ( values[ rowId ].data.hasOwnProperty( this.filterId ) ) {
				let distances: number[] = values[ rowId ].data[ this.filterId ].positions.map( ( pos: L.LatLngLiteral ) => this.distance( origin, pos ) );
				let dist = Math.min( ...distances );

				values[ rowId ].data[ this.filterId ].distance = dist;
				max = Math.max( max, dist );
			} else {
				values[ rowId ].data[ this.filterId ].distance = Infinity;
			}
		}

		return max;
	}

	public onFilterUpdated( eventObject: JQueryEventObject ) {
		this.filterValue = eventObject.data.ui.value;
		this.controller.onFilterUpdated( this.getId() );
	}

	private distance( a: L.LatLngLiteral, b: L.LatLngLiteral ) {

		const DEG2RAD = Math.PI / 180.0;

		function squared( x: number ) {
			return x * x
		}

		let f =
			squared( Math.sin( ( b.lat - a.lat ) * DEG2RAD / 2.0 ) ) +
			Math.cos( a.lat * DEG2RAD ) * Math.cos( b.lat * DEG2RAD ) *
			squared( Math.sin( ( b.lng - a.lng ) * DEG2RAD / 2.0 ) );

		return this.earthRadiusValue * 2 * Math.atan2( Math.sqrt( f ), Math.sqrt( 1 - f ) );
	}

	public isVisible( rowId: string ): boolean {

		let rowdata = this.controller.getData()[ rowId ].data;

		if ( rowdata.hasOwnProperty( this.filterId ) ) {
			return rowdata[ this.filterId ].distance <= this.filterValue;
		}

		return super.isVisible( rowId );

	}

}
