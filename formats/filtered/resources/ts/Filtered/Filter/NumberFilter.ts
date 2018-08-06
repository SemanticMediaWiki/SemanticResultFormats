///<reference path="../../../../node_modules/@types/ion.rangeslider/index.d.ts"/>
import { Filter } from "./Filter";
import { Options } from "../../types";

declare let mw: any;

export class NumberFilter extends Filter {

	private MODE_RANGE = 0;
	private MODE_MIN = 1;
	private MODE_MAX = 2;
	private MODE_SELECT = 3;

	private filterValueUpper: number = 0;
	private filterValueLower: number = 0;
	private mode = this.MODE_RANGE;

	public init() {

		let values: number[] = this.getValues();

		let { minValue, maxValue, precision } = this.getRangeParameters( values );

		let sliderOptions: IonRangeSliderOptions = {
			prettify_enabled: false,
			force_edges: true,
			grid: true
		};

		if ( this.options.hasOwnProperty( 'values' ) ) {
			sliderOptions = this.adjustSliderOptionsFromValues( sliderOptions, values );

		} else {
			sliderOptions = this.adjustSliderOptionsFromRangeParameters( sliderOptions, minValue, maxValue, precision );
		}

		switch( this.options[ 'sliders' ] ) {

			case "min":

				this.mode = this.MODE_MIN;
				sliderOptions.type = 'single';
				break;

			case "max":

				this.mode = this.MODE_MAX;
				sliderOptions.from = sliderOptions.to;
				sliderOptions.type = 'single';
				break;

			case "select":

				this.mode = this.MODE_SELECT;
				maxValue = minValue;
				sliderOptions.type = 'single';
				break;

			default: // == case "range"

				this.mode = this.MODE_RANGE;
				sliderOptions.type = 'double';
		}

		this.buildFilterControls( sliderOptions );

		this.filterValueLower = minValue;
		this.filterValueUpper = maxValue;

		return this;
	}

	private adjustSliderOptionsFromRangeParameters( sliderOptions: IonRangeSliderOptions, minValue: number, maxValue: number, precision: number ) {

		sliderOptions.min = minValue;
		sliderOptions.max = maxValue;
		sliderOptions.step = this.getStep( precision );
		sliderOptions.grid_num = Math.min( 4, Math.round( ( maxValue - minValue ) / sliderOptions.step ) );

		sliderOptions.from = minValue;
		sliderOptions.to = maxValue;

		sliderOptions.onFinish = ( data: IonRangeSliderEvent ) => this.onFilterUpdated( data.from, data.to );

		return sliderOptions;
	}

	private adjustSliderOptionsFromValues( sliderOptions: IonRangeSliderOptions, values: number[] ) {

		sliderOptions.values = values;

		sliderOptions.from = 0;
		sliderOptions.to = values.length - 1;

		sliderOptions.onFinish = ( data: IonRangeSliderEvent ) => this.onFilterUpdated( data.from_value, data.to_value );

		return sliderOptions;
	}

	private getRangeParameters( values: number[] ) {

		let minValue = values[ 0 ];
		let maxValue = values[ values.length - 1 ];
		let precision: number = this.getPrecision( minValue, maxValue );

		if ( !this.options.hasOwnProperty( 'values' ) ) {
			minValue = this.getMinSliderValue( minValue, precision );
			maxValue = this.getMaxSliderValue( maxValue, precision );
		}

		return { minValue, maxValue, precision };
	}

	private getValues(): number[] {
		let values: number[];
		if ( this.options.hasOwnProperty( 'values' ) && this.options[ 'values' ][0] !== 'auto' ) {
			values =  this.options[ 'values' ]
		} else {
			values =  this.getSortedValues();
		}

		if ( values.length === 0 ) {
			values = [ 0, 0 ];
		} else if ( values.length === 1 ) {
			values.push( values[ 0 ] );
		}

		return values;
	}

	private buildFilterControls( sliderOptions: IonRangeSliderOptions ) {

		let filterClassNames: any = {};
		filterClassNames[ this.MODE_MIN.toString() ] = "mode-min";
		filterClassNames[ this.MODE_MAX ] = "mode-max";
		filterClassNames[ this.MODE_RANGE ] = "mode-range";
		filterClassNames[ this.MODE_SELECT ] = "mode-select";

		let filtercontrols = this.buildEmptyControl();

		let slider = $( '<input type="text" value="" />' );
		let sliderContainer = $( `<div class="filtered-number-slider ${filterClassNames[ this.mode ]}" />` ).append( slider );
		filtercontrols.append( sliderContainer );

		if ( this.options.hasOwnProperty( 'caption' ) ) {
			let caption = `<div class="filtered-number-caption">${this.options[ 'caption' ]}</div>`;
			filtercontrols.append( caption );
		}

		mw.loader.using( 'ext.srf.filtered.slider' ).then( () => slider.ionRangeSlider( sliderOptions ) );
	}

	private getMinSliderValue( minValue: number, precision: number ) {
		let requestedMin = this.options[ 'min' ];

		if ( requestedMin === undefined || isNaN( Number( requestedMin ) ) ) {
			return Math.floor( minValue / precision ) * precision;
		}

		return Math.min( requestedMin, minValue );
	}

	private getMaxSliderValue( maxValue: number, precision: number ) {
		let requestedMax = this.options[ 'max' ];

		if ( requestedMax === undefined || isNaN( Number( requestedMax ) ) ) {
			return Math.ceil( maxValue / precision ) * precision;
		}

		return Math.max( requestedMax, maxValue );
	}

	private getPrecision( minValue: number, maxValue: number ): number {
		if ( maxValue - minValue > 0 ) {
			return 10 ** ( Math.floor( Math.log( maxValue - minValue ) * Math.LOG10E ) - 1 );
		} else {
			return 1;
		}
	}

	private getStep( precision: number ): number {

		let step = this.options[ 'step' ];

		if ( step !== undefined ) {

			step = Number( step );

			if ( !isNaN( step ) ) {
				return step;
			}
		}

		return precision / 10;
	}

	private getRangeFromValues(): [ number, number ] {

		let rows = this.controller.getData();
		let min = Infinity;
		let max = -Infinity;

		for ( let rowId in rows ) {

			if ( rows[ rowId ].data.hasOwnProperty( this.filterId ) ) {
				let values: number[] = rows[ rowId ].data[ this.filterId ].values;
				min = Math.min( min, ...values );
				max = Math.max( max, ...values );
			}
		}

		return [ min, max ];
	}

	private getSortedValues(): number[] {

		let valueArray: number[] = [];
		let rows = this.controller.getData();

		for ( let rowId in rows ) {

			let cells = rows[ rowId ].data;

			if ( cells.hasOwnProperty( this.filterId ) ) {

				let values = cells[ this.filterId ].values;

				for ( let valueId in values ) {

					let value = Number( values[ valueId ] );

					if ( valueArray.indexOf( value ) === -1 ) {
						valueArray.push( value );
					}
				}
			}
		}

		return valueArray.sort( ( a: any, b: any ) => a - b );
	}

	public onFilterUpdated( from: number, to: number ) {

		switch ( this.mode ) {

			case this.MODE_MIN:

				this.filterValueLower = from;
				break;

			case this.MODE_MAX:

				this.filterValueUpper = from;
				break;

			case this.MODE_SELECT:

				this.filterValueLower = from;
				this.filterValueUpper = from;
				break;

			default: // case this.MODE_RANGE:

				this.filterValueLower = from;
				this.filterValueUpper = to;
		}

		this.controller.onFilterUpdated( this.getId() );
	}

	public isVisible( rowId: string ): boolean {
		let rowdata = this.controller.getData()[ rowId ].data;

		if ( rowdata.hasOwnProperty( this.filterId ) && rowdata[ this.filterId ].values.length > 0 ) {

			for ( let value of rowdata[ this.filterId ].values ) {
				if ( value >= this.filterValueLower && value <= this.filterValueUpper ) {
					return true;
				}
			}

			return false;
		}

		return super.isVisible( rowId );
	}

}
