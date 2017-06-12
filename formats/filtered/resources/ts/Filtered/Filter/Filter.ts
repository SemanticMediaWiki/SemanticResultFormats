import { Options } from "../../types";
import { Controller } from "../Controller";

export abstract class Filter{

	protected target: JQuery = undefined;
	protected filterId: string;
	protected printrequestId: string;
	protected controller: Controller;
	protected options: Options = undefined;

	public constructor( filterId: string, target: JQuery, printrequestId: string, controller: Controller, options?: Options ) {
		this.target = target;
		this.filterId = filterId;
		this.printrequestId = printrequestId;
		this.controller = controller;
		this.options = options || {};
	}

	public init() {};

	public isVisible( rowId: string ): boolean {
		return true;
	}

	public getId() {
		return this.filterId;
	}

	protected addControlForCollapsing( filtercontrols: JQuery ): JQuery {
		let collapsible = this.options.hasOwnProperty( 'collapsible' ) ? this.options[ 'collapsible' ] : undefined;
		if ( collapsible === 'collapsed' || collapsible === 'uncollapsed' ) {

			let showControl = $( '<span class="filtered-show">' );
			let hideControl = $( '<span class="filtered-hide">' );

			filtercontrols
			.prepend( showControl )
			.prepend( hideControl );

			filtercontrols = $( '<div class="filtered-collapsible">' )
			.appendTo( filtercontrols );

			let outercontrols = filtercontrols;

			showControl.click( function () {
				outercontrols.slideDown();
				showControl.hide();
				hideControl.show();
			} );

			hideControl.click( function () {
				outercontrols.slideUp();
				showControl.show();
				hideControl.hide();
			} );

			if ( collapsible === 'collapsed' ) {
				hideControl.hide();
				outercontrols.slideUp( 0 );
			} else {
				showControl.hide();
			}
		}
		return filtercontrols;
	}

}