# Legacy QUnit tests

The tests under this directory (`tests/qunit/`) run only in a real browser,
via `Special:JavaScriptTest/qunit` on a MediaWiki installation — they are
**not** wired into CI. Automated coverage now lives under
[`tests/node-qunit/`](../node-qunit/), which runs on every push via
`npm run test`.

Most of this legacy suite has been ported over (see the commit history for
issues #1066–#1072). What remains is left here deliberately: porting it would
require heavy mocking (a real Leaflet map render, canvas/d3 rendering, `smw.*`
globals belonging to the separate SemanticMediaWiki extension, or a
calendar-specific jQuery UI widget factory) relative to the coverage it buys.
Each file/sub-test below has a `// LEGACY:` comment at its definition
cross-referencing this rationale.

| File | Sub-test(s) | Why it's legacy-only |
| --- | --- | --- |
| `widgets/ext.srf.widgets.eventcalendar.tests.js` | all (calendarpane, calendarbutton, calendarparameters) | Calendar-specific jQuery UI widgets; would need their own widget-factory mocking on top of the shared shim ([#1069](https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1069)) for low-value coverage |
| `formats/ext.srf.formats.eventcalendar.tests.js` | `update` | Uses the QUnit 1.x global `stop()`/`start()` API, removed in QUnit 2.x — cannot run in any modern QUnit runtime (browser or node) as written; a rewrite of its async pattern would be a separate issue |
| `formats/ext.srf.formats.filtered.test.js` (compiled from `formats/filtered/tests/qunit/Filtered/View/MapViewTest.ts`) | `Show and Hide`, `showRows and hideRows do not throw for unknown rowId` | `MapView.show()` drives `lateInit()`, which needs a real Leaflet map render plus `window.matchMedia`, neither of which jsdom implements |
| `formats/ext.srf.formats.tagcloud.test.js` | `sphere`, `wordcloud` | Need real tagcanvas + d3 canvas/SVG rendering; `wordcloud()` also currently calls the removed `d3.layout.cloud()` API against the installed d3 major version (a pre-existing, separately-tracked bug) |

`extension.json`'s `QUnitTestModule` entries for these files are intentionally
kept — it's their only remaining execution path. An entry is only removed once
its file is fully ported and deleted, as part of that port's own issue.
