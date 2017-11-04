import { Options } from "../../types";
import { Controller } from "../Controller";

export abstract class Filter{

	private outerTarget: JQuery = undefined;
	protected target: JQuery = undefined;
	protected filterId: string;
	protected printrequestId: string;
	protected controller: Controller;
	protected options: Options = undefined;
	protected disabled: boolean = false;
	protected collapsed: boolean = false;

	public constructor( filterId: string, target: JQuery, printrequestId: string, controller: Controller, options?: Options ) {
		this.target = target;
		this.outerTarget = target;
		this.filterId = filterId;
		this.printrequestId = printrequestId;
		this.controller = controller;
		this.options = options || {};
	}

	public init() {};

	public isDisabled() : boolean {
		return this.disabled;
	}

	public disable() {
		this.disabled = true;

		this.outerTarget
		.removeClass( 'enabled' )
		.addClass( 'disabled' );

		this.collapse();

		this.target.promise().then( () =>	this.controller.onFilterUpdated( this.filterId ) );
	}

	public enable() {
		this.disabled = false;

		this.outerTarget
		.removeClass( 'disabled' )
		.addClass( 'enabled' );

		if ( ! this.collapsed ) {
			this.uncollapse();
		}

		this.target.promise().then( () =>	this.controller.onFilterUpdated( this.filterId ) );
	}

	private collapse( duration : number = 400 ) {

		if ( ! this.collapsed ) {

			this.outerTarget.promise()
			.then( () => {

				this.target.slideUp( duration );

				this.outerTarget.animate( {
					'padding-top': 0,
					'padding-bottom': 0,
					'margin-bottom': '2em'
				}, duration );
			} );
		}
	}

	private uncollapse() {
		this.outerTarget.promise()
		.then( () => {
			this.target.slideDown();

			let style = this.outerTarget.attr( 'style' );
			this.outerTarget.removeAttr( 'style' );
			let uncollapsedCss = this.outerTarget.css( [ 'padding-top', 'padding-bottom', 'margin-bottom' ] );
			this.outerTarget.attr( 'style', style );

			this.outerTarget.animate( uncollapsedCss );
		} );
	}

	public isVisible( rowId: string ): boolean {
		return this.options.hasOwnProperty( 'show if undefined' ) && this.options[ 'show if undefined' ] === true;
	}

	public getId() {
		return this.filterId;
	}

	protected buildEmptyControl() {

		this.target = $( '<div class="filtered-filter-container">' );

		this.outerTarget
		.append( this.target )
		.addClass( 'enabled' );

		this.addOnOffSwitch();
		this.addLabel();
		this.addControlForCollapsing();

		return this.target;
	}

	private addLabel() {
		// insert the label of the printout this filter filters on
		this.target.before( `<div class="filtered-filter-label">${this.options[ 'label' ]}</div>` );
	}

	protected addOnOffSwitch() {

		if ( this.options.hasOwnProperty( 'switches' ) ) {

			let switches = this.options[ 'switches' ];

			if ( switches.length > 0 && $.inArray( 'on off', switches ) >= 0 ) {

				let onOffControl = $( `<div class="filtered-filter-onoff on"></div>` );

				this.target.before( onOffControl );

				onOffControl.click( () => {

					if ( this.outerTarget.hasClass('enabled' ) ) {
						this.disable();
					} else {
						this.enable();
					}

				} );
			}
		}
	}

	protected addControlForCollapsing() {
		let collapsible = this.options.hasOwnProperty( 'collapsible' ) ? this.options[ 'collapsible' ] : undefined;
		if ( collapsible === 'collapsed' || collapsible === 'uncollapsed' ) {

			let collapseControl = $( '<span class="filtered-filter-collapse">' );

			this.target.before( collapseControl );

			collapseControl.click( () => {
				if ( collapseControl.hasClass( 'collapsed' ) ) {
					this.uncollapse();
					this.collapsed = false;

					collapseControl
					.removeClass( 'collapsed' )
					.addClass( 'uncollapsed' );
				} else {
					this.collapse();
					this.collapsed = true;

					collapseControl
					.removeClass( 'uncollapsed' )
					.addClass( 'collapsed' );
				}

			} );

			if ( collapsible === 'collapsed' ) {

				this.collapse( 0 );
				this.collapsed = true;
				collapseControl.addClass('collapsed');

			} else {
				collapseControl.addClass('uncollapsed');
			}
		}
	}

}