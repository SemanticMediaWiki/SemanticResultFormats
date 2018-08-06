/// <reference types="jquery" />

import { Options, ResultData } from "../types";
declare let srf: any;

import { View } from "./View/View";
import { Filter } from "./Filter/Filter";

export class Controller {
	private target: JQuery = undefined;
	private filterSpinner: JQuery = undefined;

	private views: { [key: string]: View } = {};
	private filters: { [key: string]: Filter } = {};
	private currentView: View = undefined;
	private data: ResultData;
	private printRequests: Options;

	public constructor( target: JQuery, data: ResultData, printRequests: Options ) {
		this.target = target;

		if ( this.target !== undefined ) {
			this.filterSpinner = this.target.find( 'div.filtered-filter-spinner' );
		}

		this.data = data;
		this.printRequests = printRequests;

		for ( let rowId in this.data ) {
			if ( !this.data[ rowId ].hasOwnProperty( 'visible' ) ) {
				this.data[ rowId ].visible = {};
			}
		}
	}

	public getData(): any {
		return this.data;
	}

	public getPrintRequests(): Options {
		return this.printRequests;
	}

	public getPath() {
		return srf.settings.get( 'srfgScriptPath' ) + '/formats/filtered/resources/';
	}

	public attachView( viewid: string, view: View ) {

		this.views[ viewid ] = view;

		if ( this.currentView === undefined ) {
			this.currentView = view;
			view.show();
		} else {
			view.hide();
		}

		return this;
	}

	public getView( viewId: string ): View {
		return this.views[ viewId ];
	}

	public attachFilter( filter: Filter ): JQueryPromise< void > {
		let filterId = filter.getId();

		this.filters[ filterId ] = filter;

		filter.init();

		return this.onFilterUpdated( filterId );
	}

	public getFilter( filterId: string ): Filter {
		return this.filters[ filterId ];
	}

	public show() {
		this.initializeFilters();
		this.target.children( '.filtered-spinner' ).remove();
		this.target.children().show();
		this.switchToView( this.currentView );
	}

	private switchToView( view: View ) {

		if ( this.currentView instanceof View ) {
			this.currentView.hide();
		}

		this.currentView = view;

		if ( this.currentView instanceof View ) {
			view.show();
		}

	}

	private initializeFilters() {
		let toShow: string[] = [];
		let toHide: string[] = [];

		for ( let rowId in this.data ) {
			for ( let filterId in this.filters ) {
				this.data[ rowId ].visible[ filterId ] = this.filters[ filterId ].isDisabled() || this.filters[ filterId ].isVisible( rowId );
			}
			if ( this.isVisible( rowId ) ) {
				toShow.push( rowId );
			} else {
				toHide.push( rowId );
			}
		}

		this.hideRows( toHide );
		this.showRows( toShow );
	}

	public onViewSelected( viewID: string ) {
		this.switchToView( this.views[ viewID ] );
	}

	public onFilterUpdated( filterId: string ): JQueryPromise< void > {

		return this.showSpinner()
		.then(() => {

			let toShow: string[] = [];
			let toHide: string[] = [];

			let disabled = this.filters[ filterId ].isDisabled();

			for ( let rowId in this.data ) {

				let newVisible: boolean = disabled || this.filters[ filterId ].isVisible( rowId );

				if ( this.data[ rowId ].visible[ filterId ] !== newVisible ) {

					this.data[ rowId ].visible[ filterId ] = newVisible;

					if ( newVisible && this.isVisible( rowId ) ) {
						toShow.push( rowId );
					} else {
						toHide.push( rowId );
					}
				}
			}

			this.hideRows( toHide );
			this.showRows( toShow );
		})
		.then( () => { this.hideSpinner() } );
	}

	public isVisible( rowId: any ) {
		for ( let filterId in this.data[ rowId ].visible ) {
			if ( !this.data[ rowId ].visible[ filterId ] ) {
				return false;
			}
		}
		return true;
	}

	private hideRows( rowIds: string[] ) {
		if ( rowIds.length === 0 ) {
			return;
		}
		for ( let viewId in this.views ) {
			this.views[ viewId ].hideRows( rowIds );
		}
	}

	private showRows( rowIds: string[] ) {
		if ( rowIds.length === 0 ) {
			return;
		}
		for ( let viewId in this.views ) {
			this.views[ viewId ].showRows( rowIds );
		}
	}

	private showSpinner(): JQueryPromise< void > {
		return this.animateSpinner();
	}

	private hideSpinner(): JQueryPromise< void > {
		return this.animateSpinner( false );
	}

	private animateSpinner( show: boolean = true ): JQueryPromise< void > {

		if ( this.filterSpinner === undefined ) {
			return jQuery.when();
		}

		if ( show ) {
			return this.filterSpinner.fadeIn( 200 ).promise();
		}

		return this.filterSpinner.fadeOut( 200 ).promise();
	}

}
