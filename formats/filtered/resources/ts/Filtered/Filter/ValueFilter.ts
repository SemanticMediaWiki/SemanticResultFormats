import { Filter } from "./Filter";
import { printoutValues, printoutFormattedValues, printoutSortValues } from "../../types";
import { IdTextPair } from "select2";

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
					// no formatted value: the item is plain text and is escaped when shown
					return {
						printoutValue: item
					};
				}
			);

		} else {
			// build filter values from available values in result set
			let data = this.controller.getData();
			let sortedEntries: any[] = [];
			for ( let id in data ) {

				let slot = data[ id ].p[ this.printrequestId ];

				if ( !slot ) {
					continue;
				}

				let values: any = printoutValues( slot );
				let formattedValues: any = printoutFormattedValues( slot );
				let sortValues: any = printoutSortValues( slot );

				for ( let i in values ) {
					let formattedValue = formattedValues[ i ];

					if ( formattedValue.indexOf( '<a' ) > -1 ) {
						formattedValue = /<a.*>(.*?)<\/a>/.exec( formattedValue )[ 1 ];
					}

					distinctValues[ values[ i ] ] = formattedValue;
					distinctSortValues[ values[ i ] ] = sortValues[ i ];
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

		let filtercontrols = this.buildEmptyControl();

		filtercontrols = this.addControlForSwitches( filtercontrols );

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
			// printoutValue is raw wikitext, so it must never be put into markup; the formatted
			// value is HTML rendered by the server and is kept as is
			const optionLabel = $( '<div class="filtered-value-option-label">' );
			if ( value.formattedValue ) {
				optionLabel.html( value.formattedValue );
			} else {
				optionLabel.text( value.printoutValue );
			}

			checkboxes.append(
				$( '<div class="filtered-value-option">' ).append(
					$( '<label>' ).append( $( '<input type="checkbox">' ).val( value.printoutValue ), optionLabel )
				)
			);
		}

		// attach event handler
		checkboxes
		.on( 'change', ':checkbox', ( eventObject: JQueryEventObject ) => {
			let checkboxElement = <HTMLInputElement> eventObject.currentTarget;
			this.onFilterUpdated( checkboxElement.value, checkboxElement.checked );
		} );

		return checkboxes;
	}

	private stripHtml(str: string): string {
		// DOMParser yields an inert document: unlike innerHTML on a detached element it
		// does not load images or run event handlers while the markup is stripped
		return new DOMParser().parseFromString( str, 'text/html' ).body.textContent || '';
	}

	private getSelected2Control() {
		let select = $( '<select class="filtered-value-select" style="width: 100%;">' );

		let data: IdTextPair[] = [];

		// insert options (checkboxes and labels) and attach event handlers
		for ( let value of this.values ) {
			// Try to get label, if not fall back to value id
			const label = value.formattedValue || value.printoutValue || '';
			data.push( { id: value.printoutValue, text: this.stripHtml( label ) } );
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

	private addControlForSwitches( filtercontrols: JQuery ): JQuery {
		// insert switches
		let switches = this.options.hasOwnProperty( 'switches' ) ? this.options[ 'switches' ] : undefined;

		if ( switches !== undefined && $.inArray( 'and or', switches ) >= 0 ) {

			let switchControls = $( '<div class="filtered-value-switches">' );

			let andorControl = $( '<div class="filtered-value-andor">' );

			let orControl = this.getRadioControl( 'or', true );
			let andControl = this.getRadioControl( 'and' );

			andorControl
			.append( orControl )
			.append( andControl )
			.appendTo( switchControls );

			andorControl
			.find( 'input' )
			.on( 'change', undefined, { 'filter': this }, ( eventObject: JQueryEventObject ) =>
				eventObject.data.filter.useOr( eventObject.target.getAttribute( 'value' ) === 'or' )
			);


			filtercontrols.append( switchControls );
		}

		return filtercontrols;
	}

	private getRadioControl( type: string, isChecked: boolean = false ) {

		let checkedAttr = isChecked?'checked':'';
		let labelText = mw.message( 'srf-filtered-value-filter-' + type ).text();

		let controlText =
			`<label for="filtered-value-${type}-${this.filterId}">` +
			`<input type="radio" name="filtered-value-${this.filterId}"  class="filtered-value-${type}" id="filtered-value-${type}-${this.filterId}" value="${type}" ${checkedAttr}>` +
			`${labelText}</label>`;

		return $( controlText );
	}

	public isVisible( rowId: string ): boolean {

		if ( this.visibleValues.length === 0 ) {
			return true;
		}

		let values: string[] = printoutValues( this.controller.getData()[ rowId ].p[ this.printrequestId ] );

		if ( values.length === 0 ) {
			return super.isVisible( rowId );
		}


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
