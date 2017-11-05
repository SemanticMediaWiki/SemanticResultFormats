/// <reference types="qunit" />
/// <reference types="jquery" />

import { ViewTest } from "./ViewTest";
import { MapView } from "../../../../resources/ts/Filtered/View/MapView";
import { Controller } from "../../../../resources/ts/Filtered/Controller";
import { Options } from "../../../../resources/ts/types";

export class MapViewTest extends ViewTest {

	// TODO:

	public getTestObject( id: string = 'foo', target: JQuery = undefined, c: Controller = undefined, options: Options = {} ) {
		c = c || new Controller( undefined, {}, undefined );
		return new MapView( id, target, c, options );
	};

	public runTests() {
		super.runTests();
		return true;
	};

}