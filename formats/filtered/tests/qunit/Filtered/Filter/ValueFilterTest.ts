/// <reference types="qunit" />
/// <reference types="jquery" />

import { ValueFilter } from "../../../../resources/ts/Filtered/Filter/ValueFilter";
import { Controller } from "../../../../resources/ts/Filtered/Controller";
import { QUnitTest } from "../../Util/QUnitTest";

export class ValueFilterTest extends QUnitTest {

	// TODO:
	// 	public isVisible( rowId: string ): boolean {
	//	public onFilterUpdated( eventObject: JQueryEventObject ) {

	public runTests() {
		QUnit.test( 'ValueFilter: Can construct', this.testCanConstruct );
		QUnit.test( 'ValueFilter: Init', this.testInit );
		QUnit.test( 'ValueFilter: Update on and/or switch.', this.testUseOr );
		return true;
	};

	public testCanConstruct( assert: QUnitAssert ) {
		let controller = undefined;
		let options = {};

		let f = new ValueFilter( 'foo', $(), 'fooPR', controller, options );

		assert.ok( f instanceof ValueFilter, 'Can construct ValueFilter.' );
	};

	public testInit( assert: QUnitAssert ) {

		// Setup
		let controller = new Controller( $(), {}, {} );
		let options = {
			'switches': [
				'and or'
			],
			'values': [
				'foo',
				'bar'
			],
			'collapsible': 'uncollapsed',
			'type': 'value',
			'label': 'FooLabel'
		};
		let target = $( '<div>' );
		let f = new ValueFilter( 'foo', target, 'fooPR', controller, options );

		// Run
		f.init();

		// Assert
		assert.strictEqual( target.find( '.filtered-collapsible' ).length, 1, 'Added container for collapsable content.' );
		assert.strictEqual( target.find( '.filtered-value-andor' ).length, 1, 'Added container for and/or switch.' );

		// Assert: One input added per value
		for ( let value of options.values ) {
			assert.strictEqual( target.find( "input[value=\"" + value + "\"]" ).length, 1, "Added input for value \"" + value + "\"." );
		}
	};

	public testUseOr( assert: QUnitAssert ) {

		// Setup
		let controller = new Controller( $(), {}, {} );
		controller.onFilterUpdated = function ( filterId ) {
			// Assert
			assert.ok( true, 'Filter updated.' );
		};

		let f = new ValueFilter( 'foo', $(), 'fooPR', controller, {} );

		assert.expect( 1 );

		// Run
		f.useOr( true );
	};

}