/// <reference types="qunit" />
/// <reference types="jquery" />

import { QUnitTest } from "../../Util/QUnitTest";
import { View } from "../../../../resources/ts/Filtered/View/View";
import { Controller } from "../../../../resources/ts/Filtered/Controller";
import { Options } from "../../../../resources/ts/types";

export class ViewTest extends QUnitTest {

	// Coverage:
	// [x] public constructor( id: string, target: JQuery, c: Controller, options: Options = {} )
	// [x] public init()
	// [x] public getTargetElement(): JQuery
	// [ ] public showRows( rowIds: string[] )
	// [ ] public hideRows( rowIds: string[] )
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

}