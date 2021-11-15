/// <reference types="qunit" />
/// <reference types="jquery" />

import { DistanceFilter } from "../../../../resources/ts/Filtered/Filter/DistanceFilter";
import { Controller } from "../../../../resources/ts/Filtered/Controller";
import { QUnitTest } from "../../Util/QUnitTest";

export class DistanceFilterTest extends QUnitTest {

	public runTests() {
		QUnit.test( 'DistanceFilter: Init', this.testInit );
		return true;
	};

	public testInit( assert: QUnitAssert ) {
		assert.expect(1);

		// Setup
		let controller = new Controller( $(), {}, {} );
		let options = {
			origin: { lat: 0, lng: 0}
		};
		let target = $( '<div>' );
		let f = new DistanceFilter( 'foo', target, 'fooPR', controller, options );

		// Run
		f.init();

		// Assert
		let done = assert.async();
		setTimeout( () => {
			assert.ok( target.find('.filtered-distance-slider')[0].hasChildNodes(), 'Added distance slider.' );
			done();
		}, 100);
	};
}
