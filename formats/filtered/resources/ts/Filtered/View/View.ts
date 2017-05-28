import { Options } from "../../types";
import { Controller } from "../Controller";

export class View {

	protected id: string = undefined;
	protected target: JQuery = undefined;
	protected controller: Controller = undefined;
	protected options: Options = undefined;

	public constructor( id: string, target: JQuery, c: Controller, options: Options = {} ) {
		this.id = id;
		this.target = target;
		this.controller = c;
		this.options = options;
	}

	public init() {}

	public getTargetElement(): JQuery {
		return this.target;
	}

	public showRows( rowIds: string[] ) {
		rowIds.forEach( ( rowId: string ) => {
			this.target.find( '.' + rowId ).slideDown( 400 );
		} );
	}

	public hideRows( rowIds: string[] ) {
		rowIds.forEach( ( rowId: string ) => {
			this.target.find( '.' + rowId ).slideUp( 400 );
		} );
	}

	public show() {
		this.target.show();
	}

	public hide() {
		this.target.hide();
	}
}
