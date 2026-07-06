'use strict';

const path = require('path');

// srf.api.results/srf.api.query — startDate().get()'s dataValues.time.parseDate
// path and the module-scope `_calendar.api = { results: new srf.api.results() }`
// construction depend on these.
require(path.resolve(__dirname, '../../resources/ext.srf.api.results.js'));
require(path.resolve(__dirname, '../../resources/ext.srf.api.query.js'));
require(path.resolve(__dirname, '../../formats/calendar/resources/ext.srf.formats.eventcalendar.js'));

// Only parse()/startDate() (self-contained data transformation) are covered
// here. The rest of ext.srf.formats.eventcalendar.tests.js — including
// update(), which uses the QUnit 1.x stop()/start() API removed in QUnit 2.x
// and cannot run in any modern QUnit runtime — stays legacy. See issue #1073
// for the broader legacy-test documentation effort.
QUnit.module('ext.srf.formats.eventcalendar', () => {

	QUnit.test('parse.api() transforms query results into calendar events', (assert) => {
		const calendar = new srf.formats.eventcalendar();

		assert.equal($.type(calendar.test._parse), 'object', 'the parse object was accessible');
		assert.equal($.type(calendar.test._parse.api), 'function', 'parse.api() was accessible');

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: '', filterType: 'filter' },
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								title: [new smw.dataItem.wikiPage('Demo 230', 'http://localhost/mw/index.php/Demo_230')],
								'Has event start': [new smw.dataItem.time('2012-01-01T00:00:00.000Z', '1325390400')],
								'Has event end': [new smw.dataItem.time('2012-01-03T00:00:00.000Z', '1325563200')],
							},
						},
						'Event/2': {
							printouts: {
								title: [new smw.dataItem.wikiPage('Demo 230', 'http://localhost/mw/index.php/Demo_230')],
								'Has event start': [new smw.dataItem.time('2011-12-31T00:00:00.000Z', '1325300400')],
								'Has event end': [],
							},
						},
					},
				},
			},
		};

		const result = calendar.test._parse.api(data);

		assert.equal($.type(result), 'object', 'api() returned an object');
		assert.deepEqual(result.dates, ['1325390400', '1325563200', '1325300400'], 'dates[] collects every parsed timestamp in encounter order');
		assert.equal(result.events.length, 2, 'both rows with a start date became events');
		assert.equal(result.events[0].title, 'Demo 230', 'event title came from the wikiPage printout');
		assert.deepEqual(result.legend, {}, 'legend is empty when no filterProperty is configured');
	});

	QUnit.test('parse.api() builds a legend when filterProperty is configured', (assert) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: 'Has event type', filterType: 'filter' },
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [new smw.dataItem.time('2012-01-01T00:00:00.000Z', '1325390400')],
								'Has event type': [new smw.dataItem.wikiPage('Meeting', 'http://localhost/mw/index.php/Meeting')],
								color: [{ getValue: () => 'green' }],
							},
						},
					},
				},
			},
		};

		const result = calendar.test._parse.api(data);

		assert.deepEqual(result.legend, { Meeting: { color: ['green'], filter: true } },
			'filterProperty match populated the legend, keyed by the filter value');
	});

	QUnit.test('parse.api() skips rows without a start date', (assert) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: { parameters: { headers: 'show', filterProperty: '', filterType: 'filter' } },
				result: {
					results: {
						'Event/1': { printouts: {} },
					},
				},
			},
		};

		const result = calendar.test._parse.api(data);

		assert.equal(result.events.length, 0, 'a row with no start date produces no event');
	});

	QUnit.test('startDate().minmax() finds the earliest and latest timestamp', (assert) => {
		const calendar = new srf.formats.eventcalendar();

		assert.equal($.type(calendar.test._startDate()), 'object', 'the object was accessible without arguments');

		const dates = ['1325390400', '1325563200', '1357304400', '1357308000'];
		assert.equal($.type(calendar.test._startDate(dates).minmax()), 'object', 'minmax() was accessible');
		assert.deepEqual(calendar.test._startDate(dates).minmax(), { max: '1357308000', min: '1325390400' },
			'minmax() found the correct min/max of 4 timestamps');

		assert.deepEqual(calendar.test._startDate(['633830400', '634176000', '1262563200']).minmax(),
			{ max: '1262563200', min: '633830400' }, 'minmax() found the correct min/max of 3 timestamps');

		// regression: min must not get stuck at the sentinel 0 when the first
		// values compared are all smaller than a later minimum
		assert.deepEqual(
			calendar.test._startDate(['1360886400', '1347753600', '1347753600', '1347926400', '1347926400']).minmax(),
			{ max: '1360886400', min: '1347753600' },
			'minmax() found the correct min/max with repeated values'
		);
	});

	QUnit.test('startDate().get() with no/unrecognized type returns the current date', (assert) => {
		const calendar = new srf.formats.eventcalendar();
		const dates = ['1325390400', '1325563200', '1357304400', '1357308000'];

		assert.equal($.type(calendar.test._startDate(dates).get()), 'date', 'get() was accessible');

		const before = new Date();
		const resultNoArg = calendar.test._startDate(dates).get();
		const resultUnknown = calendar.test._startDate(dates).get('foo');
		const after = new Date();

		assert.ok(resultNoArg.getTime() >= before.getTime() && resultNoArg.getTime() <= after.getTime(),
			'get() with no argument returned the current date');
		assert.ok(resultUnknown.getTime() >= before.getTime() && resultUnknown.getTime() <= after.getTime(),
			'get("foo") returned the current date');
	});

	QUnit.test('startDate().get("earliest"/"latest") parses the min/max timestamp via srf.api.results', (assert) => {
		const calendar = new srf.formats.eventcalendar();
		const dates = ['1325390400', '1325563200', '1357304400', '1357308000'];

		const earliest = calendar.test._startDate(dates).get('earliest');
		const latest = calendar.test._startDate(dates).get('latest');

		assert.equal(earliest.getTime(), 1325390400 * 1000, 'get("earliest") parsed the min timestamp (1325390400)');
		assert.equal(latest.getTime(), 1357308000 * 1000, 'get("latest") parsed the max timestamp (1357308000)');
	});

});
