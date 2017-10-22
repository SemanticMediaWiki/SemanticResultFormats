import { Options } from "../types";
import { Controller } from "./Controller";
import { ViewSelector } from "./ViewSelector";
import { View } from "./View/View";
import { ListView } from "./View/ListView";
import { TableView } from "./View/TableView";
import { MapView } from "./View/MapView";
import { CalendarView } from "./View/CalendarView";
import { Filter } from "./Filter/Filter";
import { ValueFilter } from "./Filter/ValueFilter";
import { DistanceFilter } from "./Filter/DistanceFilter";
import { NumberFilter } from "./Filter/NumberFilter";

/**
 * Central Filtered class
 *
 * Factory to setup everyhting else
 */
export class Filtered {

	private config: any;
	private target: JQuery;

	private viewTypes: { [key: string]: new( id: string, target: JQuery, controller: Controller, options?: any ) => View } = {
		table: TableView,
		list: ListView,
		map: MapView,
		calendar: CalendarView
	};

	private filterTypes: { [key: string]: new( id: string, target: JQuery, printrequestId: string, controller: Controller, options?: Options ) => Filter } = {
		value: ValueFilter,
		distance: DistanceFilter,
		number: NumberFilter
	};

	/**
	 *
	 * @param target
	 * @param config
	 */
	public constructor( target: JQuery, config: any ) {
		this.config = config;
		this.target = target;
	}

	public run() {

		let controller = new Controller( this.target, this.config.data, this.config.printrequests );

		this.attachFilters( controller, this.target.children( 'div.filtered-filters' ) );
		this.attachViewSelector( controller, this.target.find( 'div.filtered-views-selectors-container' ) );
		this.attachViews( controller, this.target.find( 'div.filtered-views-container' ) );

		// lift-off
		controller.show();

	}

	private attachFilters( controller: Controller, filtersContainer: JQuery ) {

		for ( let prId in this.config.printrequests ) {

			let pr = this.config.printrequests[ prId ];

			if ( pr.hasOwnProperty( 'filters' ) ) {

				for ( let filterid in pr.filters ) {

					if ( pr.filters.hasOwnProperty( filterid ) &&
						pr.filters[ filterid ].hasOwnProperty( 'type' ) &&
						this.filterTypes.hasOwnProperty( pr.filters[ filterid ].type ) ) {

						//  target: JQuery, printrequest: string,
						// controller: Controller, options?: Options
						let filter: Filter = new this.filterTypes[ pr.filters[ filterid ].type ]( filterid, filtersContainer.children( '#' + filterid ), prId, controller, pr.filters[ filterid ] );

						controller.attachFilter( filter );

					}
				}
			}

		}
	}

	private attachViewSelector( controller: Controller, viewSelectorContainer: JQuery ) {
		let viewSelector = new ViewSelector( viewSelectorContainer, Object.keys( this.config.views ), controller );
		viewSelector.init();
	}

	private attachViews( controller: Controller, viewsContainer: JQuery ) {

		// attach views
		for ( let viewid in this.config.views ) {

			let viewtype = this.config.views[ viewid ][ 'type' ];
			let viewHandlerClass = this.viewTypes.hasOwnProperty( viewtype ) ? this.viewTypes[ viewtype ] : View;

			let view: View = new viewHandlerClass( viewid, viewsContainer.children( '#' + viewid ), controller, this.config.views[ viewid ] );

			view.init();

			controller.attachView( viewid, view );

		}
	}
}
