/// <reference types="qunit" />

// ViewSelectorTest, ControllerTest, ValueFilterTest (checkboxes path), and
// ViewTest were ported to tests/node-qunit/ (issue #1067) and removed here.
// DistanceFilterTest was fully ported to tests/node-qunit/ (issue #1068) and
// removed here; MapViewTest stays for its show()/lateInit()-exercising tests
// (its init()-level test was also ported, see MapViewTest.ts).
import { QUnitTestHandler } from "./Util/QUnitTestHandler";
import { MapViewTest } from "./Filtered/View/MapViewTest";

let testclasses = [
	MapViewTest,
];
let testhandler = new QUnitTestHandler('ext.srf.formats.filtered', testclasses);

testhandler.runTests();
