import { Options } from "../../types";
import { Controller } from "../Controller";

export class View {

	protected id: string = undefined;
	protected target: JQuery = undefined;
	protected controller: Controller = undefined;
	protected options: Options = undefined;
	protected visible: boolean = false;
	protected rows: { [ rowId: string ]: JQuery } = {};

	public constructor( id: string, target: JQuery, c: Controller, options: Options = {} ) {
		this.id = id;
		this.target = target;
		this.controller = c;
		this.options = options;
	}

	public init(): Promise<any>|void {

		let rowIds = Object.keys( this.controller.getData() );
		let rows = this.target.find( this.getItemClassName() );

		rows.each( ( index, elem ) => {
			let classes = elem.classList;
			for ( let i = 0; i < classes.length; i++ ) {
				if ( rowIds.indexOf( classes[ i ] ) >= 0 ) {
					this.rows[ classes[ i ] ] = $( rows[ index ] );
				}
			}
		} );
	}

	protected getItemClassName() {
		return '.filtered-item';
	}

	public getTargetElement(): JQuery {
		return this.target;
	}

	public showRows( rowIds: string[] ) {

		if ( this.visible && rowIds.length < 200 ) {

			rowIds.forEach( ( rowId: string ) => {
				this.rows[ rowId ].slideDown( 400 );
			} );

		} else {

			rowIds.forEach( ( rowId: string ) => {
				this.rows[ rowId ].css( 'display', '');
			} );

		}
	}

	public hideRows( rowIds: string[] ) {

		if ( this.visible && rowIds.length < 200 ) {

			rowIds.forEach( ( rowId: string ) => {
				this.rows[ rowId ].slideUp( 400 );
			} );

		} else {

			rowIds.forEach( ( rowId: string ) => {
				this.rows[ rowId ].css( 'display', 'none');
			} );

		}
	}

	public show() {
		this.target.show();
		this.visible = true;
	}

	public hide() {
		this.target.hide();
		this.visible = false;
	}
}
