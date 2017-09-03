/// <reference types="qunit" />
/// <reference types="jquery" />

import { ViewSelector } from "../../../resources/ts/Filtered/ViewSelector";
import { Controller } from "../../../resources/ts/Filtered/Controller";

export class ViewSelectorTest {

	public runTests() {
		QUnit.test( 'ViewSelector: Can construct', this.testCanConstruct );
		QUnit.test( 'ViewSelector: Init for 1 view', this.testInitSingleView );
		QUnit.test( 'ViewSelector: Init for 2 views', this.testInitMultipleViews );
		QUnit.test( 'ViewSelector: Selecting views when clicked (3 views: foo, bar, baz)', this.testSelectViews );
		return true;
	}

	public testCanConstruct( assert: QUnitAssert ) {
		let v = new ViewSelector( undefined, [], undefined );
		assert.ok( v instanceof ViewSelector, 'Can construct ViewSelector.' );
	}

	public testInitSingleView( assert: QUnitAssert ) {

		// Setup
		let callCount = 0;
		let viewName = 'foo';

		let target = $( '<div style="display:none">' );
		target.append( '<div class="' + viewName + '">' );
		target.on = function ( ...args: any[] ): JQuery {
			callCount++;
			return target;
		};
		target.appendTo( 'body' );

		let v = new ViewSelector( target, [ viewName ], undefined );

		// Run
		v.init();

		// Assert
		assert.strictEqual( callCount, 0, 'Registers no Click events.' );
		assert.ok( target.is( ':hidden' ), 'Target element is NOT visible.' );

		// Tear down
		target.remove();
	}

	public testInitMultipleViews( assert: QUnitAssert ) {

		// Setup
		let target: any = $( '<div style="display:none">' );
		let viewSelectors: { [index: string]: JQuery } = {};
		let viewIDs = [ 'foo', 'bar' ];
		for ( let id of viewIDs ) {
			viewSelectors[ id ] = $( '<div class="' + id + '">' );
			target.append( viewSelectors[ id ] );
		}
		let eventRegistrationCount = 0;
		target.origOn = target.on;
		target.on = function ( ...args: any[] ) {
			eventRegistrationCount++;
			return target.origOn( ...args );
		};
		target.appendTo( 'body' );
		let v = new ViewSelector( target, viewIDs, undefined );

		// Run test: Initialize ViewSelector
		v.init();

		// Assert
		assert.strictEqual( eventRegistrationCount, viewIDs.length, "Registers " + viewIDs.length + " Click events." );
		assert.ok( target.children().first().hasClass( 'selected' ), 'First view selector is marked as selected.' );
		assert.ok( target.is( ':visible' ), 'Target element is visible.' );

		// Tear down
		target.remove();
	}

	public testSelectViews( assert: QUnitAssert ) {

		// Setup
		let target = $( '<div style="display:none">' );
		let viewSelectors:{ [index: string]: JQuery } = {};

		let viewIDs = [ 'foo', 'bar', 'baz' ];
		for ( let _i = 0, viewIDs_2 = viewIDs; _i < viewIDs_2.length; _i++ ) {
			let id = viewIDs_2[ _i ];
			viewSelectors[ id ] = $( '<div class="' + id + '">' );
			target.append( viewSelectors[ id ] );
		}

		target.appendTo( 'body' );

		let c = new Controller( undefined, undefined, undefined );
		c.onViewSelected = function ( viewID ) {
			// Assert that the ViewSelector called the Controller when clicked
			assert.ok( true, "Controller was called to select view \"" + viewID + "\"." );
		};
		let v = new ViewSelector( target, viewIDs, c );
		v.init();
		// Run test: Select view
		assert.expect( 6 );
		for ( let id in viewSelectors ) {
			viewSelectors[ id ].click();
			// Assert: Only the clicked ViewController has class 'selected'
			assert.ok( viewSelectors[ id ].hasClass( 'selected' ) && !viewSelectors[ id ].siblings().hasClass( 'selected' ), "View selector \"" + id + "\" marked as selected, siblings NOT marked as selected." );
		}
		// Tear down
		target.remove();
	}

}