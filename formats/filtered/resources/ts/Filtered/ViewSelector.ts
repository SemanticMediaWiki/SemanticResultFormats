import { Controller } from "./Controller";
export class ViewSelector {

	private target: JQuery = undefined;
	private viewIDs: string[] = undefined;

	private controller: Controller = undefined;

	public constructor( target: JQuery, viewIDs: string[], controller: Controller ) {
		this.target = target;
		this.viewIDs = viewIDs;
		this.controller = controller;
	}

	public init() {
		if ( this.viewIDs.length > 1 ) {
			this.viewIDs.forEach( ( id: string) => { this.target.on( 'click', '.' + id, { 'target': id, 'controller' : this.controller }, ViewSelector.onSelectorSelected ); } );
			this.target.children().first().addClass( 'selected');
			this.target.show();
		}
	}

	private static onSelectorSelected( event: JQueryEventObject ) {

		event.data.controller.onViewSelected( event.data.target );

		$( event.target )
		.addClass( 'selected')
		.siblings().removeClass( 'selected' );

		event.stopPropagation();
		event.preventDefault();
	}

}