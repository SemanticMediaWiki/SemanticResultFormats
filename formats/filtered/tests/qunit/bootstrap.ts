/// <reference types="qunit" />

import { ViewSelectorTest } from "./Filtered/ViewSelectorTest";
import { ControllerTest } from "./Filtered/ControllerTest";
import { ValueFilterTest } from "./Filtered/Filter/ValueFilterTest";
import { QUnitTestHandler } from "./Util/QUnitTestHandler";

let testclasses = [
	ViewSelectorTest,
	ControllerTest,
	ValueFilterTest
];
let testhandler = new QUnitTestHandler('ext.srf.formats.filtered', testclasses);

testhandler.runTests();
