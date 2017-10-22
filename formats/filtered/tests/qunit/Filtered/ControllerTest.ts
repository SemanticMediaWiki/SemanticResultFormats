/// <reference types="qunit" />

import { Controller } from "../../../resources/ts/Filtered/Controller";
import { MockedFilter } from "../Util/MockedFilter";
import { View } from "../../../resources/ts/Filtered/View/View";

export class ControllerTest {

	public runTests() {
		QUnit.test( 'Controller: Can construct and attach data', this.testConstructAndAttachData );
		QUnit.test( 'Controller: Attaching 3 views (foo, bar, baz) and switch between them', this.testAttachViewsAndSwitchToViews );
		QUnit.test( 'Controller: Show', this.testShow );
		QUnit.test( 'Controller: Attaching 3 filters (foo, bar, baz)', this.testAttachFilter );
		return true;
	}

	/**
	 * @covers Controller.constructor
	 * @covers Controller.getData
	 */
	public testConstructAndAttachData( assert: QUnitAssert ) {

		// Setup
		let data = { 'foo': {} };

		// Run
		let c = new Controller( undefined, data, {} );

		// Assert: Can construct
		assert.ok( c instanceof Controller, 'Can construct Controller.' );

		// Assert: Data correctly attached and retained
		assert.deepEqual( c.getData(), data, 'Returns result data as given to constructor.' );
	}

	/**
	 * @covers Controller.attachView
	 * @covers Controller.getView
	 * @covers Controller.onViewSelected
	 */
	public testAttachViewsAndSwitchToViews( assert: QUnitAssert ) {

		// Setup
		let c = new Controller( undefined, undefined, undefined );
		let viewIds = [ 'foo', 'bar', 'baz' ];
		let viewsShown: View[] = [];
		let viewsHidden: View[] = [];
		let views: { [ id: string ]: View } = {};

		viewIds.forEach( ( viewId ) => {

			let v = new View( viewId, undefined, c, {} );

			v.show = () => {

				if ( viewsShown.indexOf( v ) === -1 ) {
					viewsShown.push( v );
				}

				let index = viewsHidden.indexOf( v );
				if ( index >= 0 ) {
					viewsHidden.splice( index, 1 );
				}
			};

			v.hide = () => {

				if ( viewsHidden.indexOf( v ) === -1 ) {
					viewsHidden.push( v );
				}

				let index = viewsShown.indexOf( v );
				if ( index >= 0 ) {
					viewsShown.splice( index, 1 );
				}
			};

			views[ viewId ] = v;

			// Run
			c.attachView( viewId, v );
		} );

		// Assert: One view visible, all others hidden, i.e. none has undefined
		// visibility
		assert.strictEqual( viewsShown.length, 1, 'One view visible.' );
		assert.strictEqual( viewsHidden.length, viewIds.length - 1, 'All but one view hidden.' );

		for ( let viewId in views ) {
			// Assert: View correctly attached and retained
			assert.deepEqual( c.getView( viewId ), views[ viewId ], `Controller knows "${viewId}" view.` );
		}

		for ( let viewId in views ) {
			// Run: Select view
			c.onViewSelected( viewId );

			// Assert: Only selected view visible, all others hidden, i.e. none
			// has undefined visibility
			assert.ok( viewsShown.length === 1 && viewsShown.indexOf( views[ viewId ] ) >= 0, 'Selected view visible.' );
			assert.strictEqual( viewsHidden.length, viewIds.length - 1, 'All other views hidden.' );
		}
	}

	/**
	 * @covers Controller.show
	 */
	public testShow( assert: QUnitAssert ) {

		// Setup
		let targetElement = $();
		let targetShown = false;

		targetElement.children = function( selector?: string ) {

			let targetChild = $();

			targetChild.show = function () {
				targetShown = true;
				return targetChild;
			};

			return targetChild;
		};


		// Run
		new Controller( targetElement, undefined, undefined ).show();

		// Assert
		assert.ok( targetShown, 'Container made visible.' );
	}

	/**
	 * @covers Controller.attachFilter
	 * @covers Controller.getFilter
	 */
	public testAttachFilter( assert: QUnitAssert ) {

		// Setup
		let data = { 'foo': {} };
		let controller = new Controller( undefined, data, {} );
		let filterIds = [ 'foo', 'bar', 'baz' ];

		let done = assert.async();

		let promises: JQueryPromise< void >[] = [];

		filterIds.forEach( ( filterId ) => {

			let visibilityWasQueried = false;

			let filter = new MockedFilter( filterId, undefined, undefined, controller );

			filter.isVisible = function ( rowId ) {
				visibilityWasQueried = true;
				return true;
			};

			// Run

			let promise = controller.attachFilter( filter )
			.then( () => {
				// Assert: Filter was queried for the visibility of result items
				assert.ok( visibilityWasQueried, `Filter "${filterId}" was queried after attaching.` );
			} );

			promises.push( promise );

			// Assert: Filter correctly attached and retained.
			assert.deepEqual( controller.getFilter( filterId ), filter, `Controller knows "${filterId}" filter.` );
		} );

		jQuery.when( ...promises ).then( done );
	}

}