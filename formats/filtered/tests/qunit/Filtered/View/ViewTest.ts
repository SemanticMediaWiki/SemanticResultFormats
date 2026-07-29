/// <reference types="qunit" />
/// <reference types="jquery" />

// This class's own tests were ported to tests/node-qunit/ext.srf.filtered.view.test.js
// (issue #1067) and removed from bootstrap.ts; it remains here only because
// MapViewTest.ts extends it (its show()/lateInit()-exercising tests are left
// legacy, see issue #1068).
import { QUnitTest } from "../../Util/QUnitTest";
import { View } from "../../../../resources/ts/Filtered/View/View";
import { Controller } from "../../../../resources/ts/Filtered/Controller";
import { Options } from "../../../../resources/ts/types";

export class ViewTest extends QUnitTest {

	// Coverage:
	// [x] public constructor( id: string, target: JQuery, c: Controller, options: Options = {} )
	// [x] public init()
	// [x] public getTargetElement(): JQuery
	// [x] public showRows( rowIds: string[] )
	// [x] public hideRows( rowIds: string[] )
	// [x] public show()
	// [x] public hide()

	public getTestObject( id: string = 'foo', target: JQuery = undefined, c: Controller = undefined, options: Options = {} ) {
		c = c || new Controller( undefined, {}, undefined );
		return new View( id, target, c, options );
	};

	public runTests() {
		let className = (<any> this.getTestObject().constructor)[ 'name' ];
		let that: ViewTest = this;
		QUnit.test( `${className}: Can construct, init and knows target element`, ( assert: QUnitAssert ) => { that.testBasics( assert, that ) } );
		QUnit.test( `${className}: Show and Hide`, ( assert: QUnitAssert ) => { that.testShowAndHide ( assert, that ) } );
		QUnit.test( `${className}: showRows and hideRows do not throw for unknown rowId`, ( assert: QUnitAssert ) => { that.testShowHideRowsWithUnknownId( assert, that ) } );
		return true;
	};

	public testBasics( assert: QUnitAssert, that: ViewTest ) {

		//Setup
		let target = $( '<div>' );

		// Run
		let v = that.getTestObject( 'foo', target );
		let ret : Promise<any>|void = v.init();

		if ( ret !== undefined ) {
			let done = assert.async();

			(<Promise<any>>ret).then( () => {
				assert.ok( v instanceof View, 'Can construct View. (P)' );
				assert.strictEqual( v.getTargetElement(), target, 'View retains target element. (P)' );
				done();
			} );

		} else {
			// Assert
			assert.ok( v instanceof View, 'Can construct View.' );
			assert.strictEqual( v.getTargetElement(), target, 'View retains target element.' );
		}
	};

	public testShowAndHide( assert: QUnitAssert, that: ViewTest ) {

		// Setup
		let target = $( '<div>' );

		target.show = () => { assert.ok( true, 'Target element shown.'); return target; };
		target.hide = () => { assert.ok( true, 'Target element hidden.'); return target; };

		let v = that.getTestObject( 'foo', target );
		v.init();

		v.show();
		v.hide();

		assert.expect( 2 );
	};

	public testShowHideRowsWithUnknownId( assert: QUnitAssert, that: ViewTest ) {

		// Setup: View with no registered rows
		let target = $( '<div>' );
		let v = that.getTestObject( 'foo', target );
		v.init();
		v.show();

		// Assert: calling showRows/hideRows with an id that has no DOM element
		// must not throw (issue #394: "Cannot read property 'slideDown' of undefined")
		let showRowsThrew = false;
		try {
			v.showRows( [ 'nonexistent-row' ] );
		} catch ( e ) {
			showRowsThrew = true;
		}
		assert.ok( !showRowsThrew, 'showRows does not throw for unknown rowId.' );

		v.hide();

		let hideRowsThrew = false;
		try {
			v.hideRows( [ 'nonexistent-row' ] );
		} catch ( e ) {
			hideRowsThrew = true;
		}
		assert.ok( !hideRowsThrew, 'hideRows does not throw for unknown rowId.' );

		assert.expect( 2 );
	};

}