import { Filter } from "./Filter";

export class ValueFilter extends Filter {

	private values: any = {};
	private visibleValues: string[] = [];

	private _useOr = true;

	public init() {
		this.values = this.getSortedValues();
		this.buildControl();
	}

	public useOr( useOr: boolean ) {
		this._useOr = useOr;
		this.controller.onFilterUpdated( this.getId() );
	}

	private getSortedValues(): any {

		/** Map of value => label distinct values */
		let distinctValues: any = {};

		if ( this.options.hasOwnProperty( 'values' ) ) {

			return this.options[ 'values' ].reduce(

				( values: { [key: string]: string }, item: string ) => {
					values[ item ] = item;
					return values;
				}, {} );

		} else {
			// build filter values from available values in result set
			let data = this.controller.getData();
			for ( let id in data ) {

				let printoutValues: any = data[ id ][ 'printouts' ][ this.printrequestId ][ 'values' ];
				let printoutFormattedValues = data[ id ][ 'printouts' ][ this.printrequestId ][ 'formatted values' ];

				for ( let i in printoutValues ) {
					let printoutFormattedValue = printoutFormattedValues[ i ];

					if ( printoutFormattedValue.indexOf( '<a' ) > -1 ) {
						printoutFormattedValue = /<a.*>(.*?)<\/a>/.exec( printoutFormattedValue )[ 1 ];
					}

					distinctValues[ printoutValues[ i ] ] = printoutFormattedValue;
				}

			}

		}

		return distinctValues;
	}

	private buildControl() {

		let filtercontrols = this.target;

		// insert the label of the printout this filter filters on
		filtercontrols.append( '<div class="filtered-value-label"><span>' + this.options[ 'label' ] + '</span></div>' );

		filtercontrols = this.addControlForCollapsing( filtercontrols );
		this.addControlForSwitches( filtercontrols );

		let height = this.options.hasOwnProperty( 'height' ) ? this.options[ 'height' ] : undefined;
		if ( height !== undefined ) {
			filtercontrols = $( '<div class="filtered-value-scrollable">' )
			.appendTo( filtercontrols );

			filtercontrols.height( height );
		}

		// insert options (checkboxes and labels) and attach event handlers
		for ( let value of Object.keys( this.values ).sort() ) {
			let option = $( '<div class="filtered-value-option">' );

			let checkbox = $( '<input type="checkbox" class="filtered-value-value" value="' + value + '"  >' );

			// attach event handler
			checkbox
			.on( 'change', undefined, { 'filter': this }, function ( eventObject: JQueryEventObject ) {
				eventObject.data.filter.onFilterUpdated( eventObject );
			} );

			// Try to get label, if not fall back to value id
			let label = this.values[ value ] || value;

			option.append( checkbox ).append( label );

			filtercontrols.append( option );

		}

	}

	private addControlForSwitches( filtercontrols: JQuery ) {
		// insert switches
		let switches = this.options.hasOwnProperty( 'switches' ) ? this.options[ 'switches' ] : undefined;
		if ( switches !== undefined && switches.length > 0 ) {

			let switchControls = $( '<div class="filtered-value-switches">' );

			if ( $.inArray( 'and or', switches ) >= 0 ) {

				let andorControl = $( '<div class="filtered-value-andor">' );

				let andControl = $( '<input type="radio" name="filtered-value-' +
					this.printrequestId + '"  class="filtered-value-and ' + this.printrequestId + '" value="and">' );

				let orControl = $( '<input type="radio" name="filtered-value-' +
					this.printrequestId + '"  class="filtered-value-or ' + this.printrequestId + '" value="or" checked>' );

				andControl
				.add( orControl )
				.on( 'change', undefined, { 'filter': this }, function ( eventObject: JQueryEventObject ) {
					eventObject.data.filter.useOr( orControl.is( ':checked' ) );
				} );

				andorControl
				.append( orControl )
				.append( ' OR ' )
				.append( andControl )
				.append( ' AND ' )
				.appendTo( switchControls );

			}

			filtercontrols.append( switchControls );
		}
	}

	public isVisible( rowId: string ): boolean {

		if ( this.visibleValues.length === 0 ) {
			return true;
		}

		let values = this.controller.getData()[ rowId ].printouts[ this.printrequestId ].values;

		if ( this._useOr ) {
			for ( let expectedValue of this.visibleValues ) {
				if ( values.indexOf( expectedValue ) >= 0 ) {
					return true;
				}
			}
			return false;
		} else {
			for ( let expectedValue of this.visibleValues ) {
				if ( values.indexOf( expectedValue ) < 0 ) {
					return false;
				}
			}
			return true;
		}
	}

	public onFilterUpdated( eventObject: JQueryEventObject ) {
		let target = $( eventObject.target );

		let value = target.val();
		let index = this.visibleValues.indexOf( value );
		let isChecked = target.is( ':checked' );

		if ( isChecked && index === -1 ) {
			this.visibleValues.push( value );
		} else if ( !isChecked && index >= 0 ) {
			this.visibleValues.splice( index, 1 );
		}

		this.controller.onFilterUpdated( this.getId() );
	}
}
