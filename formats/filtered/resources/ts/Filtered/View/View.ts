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

	public init() {
		for ( let rowId in this.controller.getData() ) {
			this.rows[ rowId ] = this.target.find( '.' + rowId );
		}
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
