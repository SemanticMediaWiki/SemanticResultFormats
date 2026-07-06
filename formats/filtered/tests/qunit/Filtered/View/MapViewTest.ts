/// <reference types="qunit" />
/// <reference types="jquery" />

// LEGACY: MapView's init()-level (marker/bounds-building) behaviour was ported to
// tests/node-qunit/ext.srf.filtered.mapview.test.js (issue #1068), so the inherited
// testBasics() is skipped here. testShowAndHide()/testShowHideRowsWithUnknownId()
// stay legacy — MapView.show() drives lateInit(), which needs a real Leaflet map
// render plus window.matchMedia, neither of which jsdom implements. See issue #1073
// for the broader legacy-test documentation effort.
import { ViewTest } from "./ViewTest";
import { MapView } from "../../../../resources/ts/Filtered/View/MapView";
import { Controller } from "../../../../resources/ts/Filtered/Controller";
import { Options } from "../../../../resources/ts/types";

export class MapViewTest extends ViewTest {

	public getTestObject( id: string = 'foo', target: JQuery = undefined, c: Controller = undefined, options: Options = {} ) {
		c = c || new Controller( undefined, {}, undefined );
		return new MapView( id, target, c, options );
	};

	public runTests() {
		let className = (<any> this.getTestObject().constructor)[ 'name' ];
		let that: MapViewTest = this;
		QUnit.test( `${className}: Show and Hide`, ( assert: QUnitAssert ) => { that.testShowAndHide ( assert, that ) } );
		QUnit.test( `${className}: showRows and hideRows do not throw for unknown rowId`, ( assert: QUnitAssert ) => { that.testShowHideRowsWithUnknownId( assert, that ) } );
		return true;
	};

}