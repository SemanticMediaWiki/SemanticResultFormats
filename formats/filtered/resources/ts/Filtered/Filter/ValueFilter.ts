///<reference path="../../../../node_modules/@types/select2/index.d.ts"/>

import { Filter } from "./Filter";

declare let mw: any;

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
		/** Map of value => sort value distinct values */
		let distinctSortValues: any = {};

		if ( this.options.hasOwnProperty( 'values' ) ) {

			return this.options[ 'values' ].map(
				( item: string ) => {
					return {
						printoutValue: item,
						formattedValue: item
					};
				}
			);

		} else {
			// build filter values from available values in result set
			let data = this.controller.getData();
			let sortedEntries: any[] = [];
			for ( let id in data ) {

				let printoutValues: any = data[ id ][ 'printouts' ][ this.printrequestId ][ 'values' ];
				let printoutFormattedValues = data[ id ][ 'printouts' ][ this.printrequestId ][ 'formatted values' ];
				let printoutSortValues: any = data[ id ][ 'printouts' ][ this.printrequestId ][ 'sort values' ];

				for ( let i in printoutValues ) {
					let printoutFormattedValue = printoutFormattedValues[ i ];

					if ( printoutFormattedValue.indexOf( '<a' ) > -1 ) {
						printoutFormattedValue = /<a.*>(.*?)<\/a>/.exec( printoutFormattedValue )[ 1 ];
					}

					distinctValues[ printoutValues[ i ] ] = printoutFormattedValue;
					distinctSortValues[ printoutValues[ i ] ] = printoutSortValues[ i ];
				}

			}

			for ( let printoutValue in distinctSortValues ) {
				sortedEntries.push( {
					printoutValue: printoutValue,
					sortValue: distinctSortValues[ printoutValue ],
					formattedValue: distinctValues[ printoutValue ]
				} );
			}

			sortedEntries.sort(
				( a: any, b: any ) => {
					return a.sortValue.localeCompare( b.sortValue );
				} );
			return sortedEntries;

		}

	}

	private buildControl() {

		let filtercontrols = this.target;

		// insert the label of the printout this filter filters on
		filtercontrols.append( `<div class="filtered-value-label"><span>${this.options[ 'label' ]}</span></div>` );

		filtercontrols = this.addControlForCollapsing( filtercontrols );
		this.addControlForSwitches( filtercontrols );

		let maxCheckboxes = this.options.hasOwnProperty( 'max checkboxes' ) ? this.options[ 'max checkboxes' ] : 5;

		if ( this.values.length > maxCheckboxes ) {
			filtercontrols.append( this.getSelected2Control() );
		} else {
			filtercontrols.append( this.getCheckboxesControl() );
		}

	}

	private getCheckboxesControl() {

		let checkboxes = $( '<div class="filtered-value-checkboxes" style="width: 100%;">' );

		// insert options (checkboxes and labels)
		for ( let value of this.values ) {
			checkboxes.append( `<div class="filtered-value-option"><input type="checkbox" class="filtered-value-value" value="${value.printoutValue}" ><label>${value.formattedValue || value.printoutValue}</label></div>` );
		}

		// attach event handler
		checkboxes
		.on( 'change', ':checkbox', ( eventObject: JQueryEventObject ) => {
			let checkboxElement = <HTMLInputElement> eventObject.currentTarget;
			this.onFilterUpdated( checkboxElement.value, checkboxElement.checked );
		} );

		return checkboxes;
	}

	private getSelected2Control() {

		let select = $( '<select class="filtered-value-select" style="width: 100%;">' );

		let data: IdTextPair[] = [];

		// insert options (checkboxes and labels) and attach event handlers
		for ( let value of this.values ) {
			// Try to get label, if not fall back to value id
			let label = value.formattedValue || value.printoutValue;
			data.push( { id: value.printoutValue, text: label } );

		}

		mw.loader.using( 'ext.srf.filtered.value-filter.select' ).then( () => {

			select.select2( {
				multiple: true,
				placeholder: mw.message( 'srf-filtered-value-filter-placeholder' ).text(),
				data: data
			} );

			select.on( "select2:select", ( e: any ) => {
				this.onFilterUpdated( e.params.data.id, true );
			} );

			select.on( "select2:unselect", ( e: any ) => {
				this.onFilterUpdated( e.params.data.id, false );
			} );

		} );

		return select;
	}

	private addControlForSwitches( filtercontrols: JQuery ) {
		// insert switches
		let switches = this.options.hasOwnProperty( 'switches' ) ? this.options[ 'switches' ] : undefined;
		if ( switches !== undefined && switches.length > 0 ) {

			let switchControls = $( '<div class="filtered-value-switches">' );

			if ( $.inArray( 'and or', switches ) >= 0 ) {

				let andorControl = $( '<div class="filtered-value-andor">' );

				let orControl = $( `<input type="radio" name="filtered-value-${this.printrequestId}"  class="filtered-value-or" id="filtered-value-or-${this.printrequestId}" value="or" checked>` );
				let andControl = $( `<input type="radio" name="filtered-value-${this.printrequestId}" class="filtered-value-and" id="filtered-value-and-${this.printrequestId}" value="and">` );

				andControl
				.add( orControl )
				.on( 'change', undefined, { 'filter': this }, function ( eventObject: JQueryEventObject ) {
					eventObject.data.filter.useOr( orControl.is( ':checked' ) );
				} );

				andorControl
				.append( orControl )
				.append( `<label for="filtered-value-or-${this.printrequestId}">${mw.message( 'srf-filtered-value-filter-or' ).text()}</label>` )
				.append( andControl )
				.append( `<label for="filtered-value-and-${this.printrequestId}">${mw.message( 'srf-filtered-value-filter-and' ).text()}</label>` )
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

	public onFilterUpdated( value: string, isChecked: boolean ) {
		let index = this.visibleValues.indexOf( value );

		if ( isChecked && index === -1 ) {
			this.visibleValues.push( value );
		} else if ( !isChecked && index >= 0 ) {
			this.visibleValues.splice( index, 1 );
		}

		this.controller.onFilterUpdated( this.getId() );
	}
}
