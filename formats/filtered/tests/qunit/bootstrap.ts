/// <reference types="qunit" />

// ViewSelectorTest, ControllerTest, ValueFilterTest (checkboxes path), and
// ViewTest were ported to tests/node-qunit/ (issue #1067) and removed here.
import { DistanceFilterTest } from "./Filtered/Filter/DistanceFilterTest";
import { QUnitTestHandler } from "./Util/QUnitTestHandler";
import { MapViewTest } from "./Filtered/View/MapViewTest";

let testclasses = [
	DistanceFilterTest,
	MapViewTest,
];
let testhandler = new QUnitTestHandler('ext.srf.formats.filtered', testclasses);

testhandler.runTests();
