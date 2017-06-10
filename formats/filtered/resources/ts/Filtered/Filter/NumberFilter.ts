import { Filter } from "./Filter";

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

		let [ minValue, maxValue ] : [ number, number ] = this.getRange();

		let precision = 10 ** ( Math.floor( Math.log( maxValue - minValue ) * Math.LOG10E ) - 1 );

		let requestedMax = this.options[ 'max' ];
		if ( requestedMax !== undefined && !isNaN( Number( requestedMax ) ) ) {
			maxValue = Math.max( requestedMax, maxValue );
		} else {
			maxValue = Math.ceil( maxValue / precision ) * precision;
		}

		let requestedMin = this.options[ 'min' ];
		if ( requestedMin !== undefined && !isNaN( Number( requestedMin ) ) ) {
			minValue = Math.min( requestedMin, minValue );
		} else {
			minValue = Math.floor( minValue / precision ) * precision;
		}

		let step = this.options[ 'step' ];
		if ( step === undefined || isNaN( Number( step ) ) ) {
			step = precision / 10;
		}

		this.filterValueUpper = maxValue;
		this.filterValueLower = minValue;

		// build filter controls
		let filtercontrols = this.target;

		filtercontrols
		.append( '<div class="filtered-number-label"><span>' + this.options[ 'label' ] + '</span></div>' );

		filtercontrols = this.addControlForCollapsing( filtercontrols );

		let readoutLeft = $( '<div class="filtered-number-readout">' );
		let readoutRight = $( '<div class="filtered-number-readout">' );

		let caption = '';

		if ( this.options[ 'caption' ] ) {
			caption = '<tr><td colspan=3 class="filtered-number-caption-cell">' + this.options[ 'caption' ] + '</td></tr>';
		}

		let table = $( '<table class="filtered-number-table"><tbody><tr>' +
			'<td class="filtered-number-min-cell">' + minValue + '</td>' +
			'<td class="filtered-number-slider-cell"></td>' +
			'<td class="filtered-number-max-cell">' + maxValue + '</td></tr>' +
			caption +
			'</tbody></table>' );

		let sliderContainer = $( '<div class="filtered-number-slider">' );
		let lowerHandle = $( '<div class="ui-slider-handle ui-slider-handle-lower">' );
		let upperHandle = $( '<div class="ui-slider-handle ui-slider-handle-upper">' );
		let selectHandle = $( '<div class="ui-slider-handle ui-slider-handle-select">' );

		let slideroptions: JQueryUI.SliderOptions = {
			animate: true,
			min: minValue,
			max: maxValue,
			step: step
		};

		switch ( this.options[ 'sliders' ] ) {
			case 'max':
				this.mode = this.MODE_MAX;
				slideroptions.range = 'min';
				slideroptions.value = maxValue;

				readoutLeft.text( maxValue );
				upperHandle.append( readoutLeft );
				sliderContainer.append( upperHandle );

				break;
			case 'min':
				this.mode = this.MODE_MIN;
				slideroptions.range = 'max';
				slideroptions.value = minValue;

				readoutLeft.text( minValue );
				lowerHandle.append( readoutLeft );
				sliderContainer.append( lowerHandle );
				break;
			case 'select':
				this.mode = this.MODE_SELECT;
				slideroptions.value = maxValue;

				readoutLeft.text( maxValue );
				selectHandle.append( readoutLeft );
				sliderContainer.append( selectHandle );

				this.filterValueUpper = maxValue;
				this.filterValueLower = maxValue;

				break;
			default:
				this.mode = this.MODE_RANGE;
				slideroptions.range = true;
				slideroptions.values = [ minValue, maxValue ];

				readoutLeft.text( minValue );
				lowerHandle.append( readoutLeft );

				readoutRight.text( maxValue );
				upperHandle.append( readoutRight );

				sliderContainer.append( lowerHandle ).append( upperHandle );

		}

		filtercontrols.append( table );

		table
		.find( '.filtered-number-slider-cell' )
		.append( sliderContainer );

		let that: NumberFilter = this;

		mw.loader.using( 'jquery.ui.slider' ).then( function () {
			sliderContainer.slider( slideroptions )
			.on( 'slidechange', undefined, { 'filter': that }, function ( eventObject: JQueryEventObject, ui: any ) {
				eventObject.data.ui = ui;
				eventObject.data.filter.onFilterUpdated( eventObject );
			} )
			.on( 'slide', undefined, { 'filter': that }, function ( eventObject: JQueryEventObject, ui: { handle: HTMLElement; value: number } ) {
				ui.handle.firstElementChild.innerHTML = ui.value.toString();
			} );
		} );

		return this;
	}

	private getRange(): [ number, number ] {

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

	public onFilterUpdated( eventObject: JQueryEventObject ) {
		switch ( this.mode ) {
			case this.MODE_RANGE:
				this.filterValueLower = eventObject.data.ui.values[ 0 ];
				this.filterValueUpper = eventObject.data.ui.values[ 1 ];
				break;
			case this.MODE_MIN:
				this.filterValueLower = eventObject.data.ui.value;
				break;
			case this.MODE_MAX:
				this.filterValueUpper = eventObject.data.ui.value;
				break;
			case this.MODE_SELECT:
				this.filterValueLower = eventObject.data.ui.value;
				this.filterValueUpper = eventObject.data.ui.value;
				break;
		}
		this.controller.onFilterUpdated( this.getId() );
	}

	public isVisible( rowId: string ): boolean {
		let rowdata = this.controller.getData()[ rowId ].data;

		if ( rowdata.hasOwnProperty( this.filterId ) ) {

			for ( let value of rowdata[ this.filterId ].values ) {
				if ( value >= this.filterValueLower && value <= this.filterValueUpper ) {
					return true;
				}
			}
		}

		return false;
	}

}
