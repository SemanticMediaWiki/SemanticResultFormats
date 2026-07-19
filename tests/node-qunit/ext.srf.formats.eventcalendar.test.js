'use strict';

const path = require( 'path' );
const sinon = require( 'sinon' );

// srf.api.results/srf.api.query — startDate().get()'s dataValues.time.parseDate
// path and the module-scope `_calendar.api = { results: new srf.api.results() }`
// construction depend on these.
require( path.resolve( __dirname, '../../resources/ext.srf.api.results.js' ) );
require( path.resolve( __dirname, '../../resources/ext.srf.api.query.js' ) );
require( path.resolve( __dirname, '../../formats/calendar/resources/ext.srf.formats.eventcalendar.js' ) );

// parse()/startDate() (self-contained data transformation), getTruncatedSentence(),
// defaults.set(), getID(), onDayClick() and fullCalendar(...).event() are covered
// here. The rest of ext.srf.formats.eventcalendar.tests.js — including init()/
// update(), which drive the real fullCalendar jQuery plugin (not vendored for
// node-qunit) and update(), which uses the QUnit 1.x stop()/start() API removed
// in QUnit 2.x and cannot run in any modern QUnit runtime — stays legacy. See
// issue #1073 for the broader legacy-test documentation effort.
QUnit.module( 'ext.srf.formats.eventcalendar', () => {

	QUnit.test( 'parse.api() transforms query results into calendar events', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		assert.strictEqual( $.type( calendar.test._parse ), 'object', 'the parse object was accessible' );
		assert.strictEqual( $.type( calendar.test._parse.api ), 'function', 'parse.api() was accessible' );

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: '', filterType: 'filter' }
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								title: [ new smw.dataItem.wikiPage( 'Demo 230', 'http://localhost/mw/index.php/Demo_230' ) ],
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has event end': [ new smw.dataItem.time( '2012-01-03T00:00:00.000Z', '1325563200' ) ]
							}
						},
						'Event/2': {
							printouts: {
								title: [ new smw.dataItem.wikiPage( 'Demo 230', 'http://localhost/mw/index.php/Demo_230' ) ],
								'Has event start': [ new smw.dataItem.time( '2011-12-31T00:00:00.000Z', '1325300400' ) ],
								'Has event end': []
							}
						}
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.strictEqual( $.type( result ), 'object', 'api() returned an object' );
		assert.deepEqual( result.dates, [ '1325390400', '1325563200', '1325300400' ], 'dates[] collects every parsed timestamp in encounter order' );
		assert.strictEqual( result.events.length, 2, 'both rows with a start date became events' );
		assert.strictEqual( result.events[ 0 ].title, 'Demo 230', 'event title came from the wikiPage printout' );
		assert.deepEqual( result.legend, {}, 'legend is empty when no filterProperty is configured' );
	} );

	QUnit.test( 'parse.api() builds a legend when filterProperty is configured', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: 'Has event type', filterType: 'filter' }
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has event type': [ new smw.dataItem.wikiPage( 'Meeting', 'http://localhost/mw/index.php/Meeting' ) ],
								color: [ { getValue: () => 'green' } ]
							}
						}
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.deepEqual( result.legend, { Meeting: { color: [ 'green' ], filter: true } },
			'filterProperty match populated the legend, keyed by the filter value' );
	} );

	QUnit.test( 'parse.api() skips rows without a start date', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: { parameters: { headers: 'show', filterProperty: '', filterType: 'filter' } },
				result: {
					results: {
						'Event/1': { printouts: {} }
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.strictEqual( result.events.length, 0, 'a row with no start date produces no event' );
	} );

	QUnit.test( 'startDate().minmax() finds the earliest and latest timestamp', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		assert.strictEqual( $.type( calendar.test._startDate() ), 'object', 'the object was accessible without arguments' );

		const dates = [ '1325390400', '1325563200', '1357304400', '1357308000' ];
		assert.strictEqual( $.type( calendar.test._startDate( dates ).minmax() ), 'object', 'minmax() was accessible' );
		assert.deepEqual( calendar.test._startDate( dates ).minmax(), { max: '1357308000', min: '1325390400' },
			'minmax() found the correct min/max of 4 timestamps' );

		assert.deepEqual( calendar.test._startDate( [ '633830400', '634176000', '1262563200' ] ).minmax(),
			{ max: '1262563200', min: '633830400' }, 'minmax() found the correct min/max of 3 timestamps' );

		// regression: min must not get stuck at the sentinel 0 when the first
		// values compared are all smaller than a later minimum
		assert.deepEqual(
			calendar.test._startDate( [ '1360886400', '1347753600', '1347753600', '1347926400', '1347926400' ] ).minmax(),
			{ max: '1360886400', min: '1347753600' },
			'minmax() found the correct min/max with repeated values'
		);
	} );

	QUnit.test( 'startDate().get() with no/unrecognized type returns the current date', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		const dates = [ '1325390400', '1325563200', '1357304400', '1357308000' ];

		assert.strictEqual( $.type( calendar.test._startDate( dates ).get() ), 'date', 'get() was accessible' );

		const before = new Date();
		const resultNoArg = calendar.test._startDate( dates ).get();
		const resultUnknown = calendar.test._startDate( dates ).get( 'foo' );
		const after = new Date();

		assert.true( resultNoArg.getTime() >= before.getTime() && resultNoArg.getTime() <= after.getTime(),
			'get() with no argument returned the current date' );
		assert.true( resultUnknown.getTime() >= before.getTime() && resultUnknown.getTime() <= after.getTime(),
			'get("foo") returned the current date' );
	} );

	QUnit.test( 'startDate().get("earliest"/"latest") parses the min/max timestamp via srf.api.results', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		const dates = [ '1325390400', '1325563200', '1357304400', '1357308000' ];

		const earliest = calendar.test._startDate( dates ).get( 'earliest' );
		const latest = calendar.test._startDate( dates ).get( 'latest' );

		assert.strictEqual( earliest.getTime(), 1325390400 * 1000, 'get("earliest") parsed the min timestamp (1325390400)' );
		assert.strictEqual( latest.getTime(), 1357308000 * 1000, 'get("latest") parsed the max timestamp (1357308000)' );
	} );

	QUnit.test( 'parse.api() adjusts a date-only end date by one day when includeend is set', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const endValue = new smw.dataItem.time( '2012-01-03T00:00:00.000Z', '1325563200' );
		endValue.precision = 4; // Day only, no time component (bit 8 unset)

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: '', filterType: 'filter', includeend: true }
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has event end': [ endValue ]
							}
						}
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.strictEqual( result.events[ 0 ].end, '2012-01-04T00:00:00.000Z',
			'includeend + a date-only precision pushed the end date forward by one day' );
	} );

	QUnit.test( 'parse.api() leaves a time-bearing end date untouched even when includeend is set', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const endValue = new smw.dataItem.time( '2012-01-03T14:30:00.000Z', '1325600000' );
		endValue.precision = 8; // Time component present (bit 8 set)

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: '', filterType: 'filter', includeend: true }
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has event end': [ endValue ]
							}
						}
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.strictEqual( result.events[ 0 ].end, '2012-01-03T14:30:00.000Z',
			'a precision bitmask with the time bit set was not shifted forward' );
		assert.strictEqual( result.events[ 0 ].allDay, false, 'a non-midnight end time marked the event as not allDay' );
	} );

	QUnit.test( 'parse.api() reads icon/color/iconclass from wikiPage and plain-object printouts', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: '', filterType: 'filter' }
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								icon: [ new smw.dataItem.wikiPage( 'Icon.png', 'http://localhost/mw/index.php/File:Icon.png' ) ],
								color: [ { getValue: () => '#ff0000' } ],
								iconclass: [ { getValue: () => 'fa fa-star' } ]
							}
						}
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.strictEqual( result.events[ 0 ].eventicon, 'Icon.png', 'a wikiPage printout named "icon" set eventicon' );
		assert.strictEqual( result.events[ 0 ].color, '#ff0000', 'a plain-object printout named "color" set the event color' );
		assert.strictEqual( result.events[ 0 ].eventIconClass, 'fa fa-star', 'a plain-object printout named "iconclass" set eventIconClass' );
	} );

	QUnit.test( 'parse.api() builds a legend from a plain-object filterProperty value', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: {
					parameters: { headers: 'show', filterProperty: 'Has status', filterType: 'legend' }
				},
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has status': [ { getValue: () => 'Open' } ],
								color: [ { getValue: () => 'blue' } ]
							}
						}
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.strictEqual( result.events[ 0 ].filter, 'Open', 'the plain-object filterProperty value was assigned to the event filter' );
		assert.deepEqual( result.legend, { Open: { color: [ 'blue' ], filter: false } },
			'filterType other than "filter" recorded filter:false in the legend entry' );
	} );

	QUnit.test( 'parse.api() collects remaining printouts into the description, honouring headers=hide', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const dataShown = {
			query: {
				ask: { parameters: { headers: 'show', filterProperty: '', filterType: 'filter' } },
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has note': [ { getValue: () => 'Bring snacks' } ]
							}
						}
					}
				}
			}
		};

		const resultShown = calendar.test._parse.api( dataShown );

		assert.strictEqual( resultShown.events[ 0 ].description,
			'<div class="fc-event-popup-row"><span class="fc-event-popup-label">Has note</span>: Bring snacks</div>',
			'headers=show wrapped the description value in a labelled popup row' );

		const dataHidden = {
			query: {
				ask: { parameters: { headers: 'hide', filterProperty: '', filterType: 'filter' } },
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has note': [ { getValue: () => 'Bring snacks' } ]
							}
						}
					}
				}
			}
		};

		const resultHidden = calendar.test._parse.api( dataHidden );

		assert.strictEqual( resultHidden.events[ 0 ].description, 'Bring snacks',
			'headers=hide left the description value unwrapped' );
	} );

	QUnit.test( 'parse.api() collects a wikiPage printout into the description, honouring headers=hide', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: { parameters: { headers: 'hide', filterProperty: '', filterType: 'filter' } },
				result: {
					results: {
						'Event/1': {
							printouts: {
								'Has event start': [ new smw.dataItem.time( '2012-01-01T00:00:00.000Z', '1325390400' ) ],
								'Has location': [ new smw.dataItem.wikiPage( 'Room 42', 'http://localhost/mw/index.php/Room_42' ) ]
							}
						}
					}
				}
			}
		};

		const result = calendar.test._parse.api( data );

		assert.strictEqual( result.events[ 0 ].description, 'Room 42',
			'a wikiPage printout with headers=hide contributed its plain text to the description' );
	} );

	QUnit.test( 'getTruncatedSentence() returns short strings unchanged', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		assert.strictEqual( calendar.test._getTruncatedSentence( 'Short text', 500 ), 'Short text',
			'a string shorter than maxChars was returned as-is' );
	} );

	QUnit.test( 'getTruncatedSentence() truncates at the last space and appends " ..."', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const str = 'The quick brown fox jumps over the lazy dog';
		const result = calendar.test._getTruncatedSentence( str, 12 );

		assert.strictEqual( result, 'The quick ...',
			'truncation backed up to the last space before maxChars, instead of cutting mid-word' );
	} );

	QUnit.test( 'getTruncatedSentence() appends " ..." even without a space to back up to', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const result = calendar.test._getTruncatedSentence( 'Supercalifragilisticexpialidocious', 10 );

		assert.strictEqual( result, 'Supercalif ...',
			'with no space within maxChars, the hard-truncated slice was used as-is' );
	} );

	QUnit.test( 'getID() reads the id attribute off the container element', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		const container = $( '<div>', { id: 'srf-eventcalendar-demo' } );

		assert.strictEqual( calendar.test._getID( container ), 'srf-eventcalendar-demo',
			'getID() returned the container\'s id attribute' );
	} );

	QUnit.test( 'defaults.set() maps theme=vector to the jQuery UI theme system, otherwise to fullcalendar\'s own', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const dataVector = {
			query: {
				ask: {
					parameters: { theme: 'vector', defaultview: 'tmonth', views: 'day,week,tmonth', firstday: 'Monday', start: '', gcalurl: null }
				}
			},
			dates: []
		};

		calendar.test._defaults.set( dataVector );

		assert.strictEqual( calendar.test._defaults.theme, 'ui', 'theme=vector mapped to the "ui" (jQuery UI) theme' );
		assert.strictEqual( calendar.test._defaults.themeSystem, 'jquery-ui', 'theme=vector mapped to the "jquery-ui" theme system' );

		const dataOther = {
			query: {
				ask: {
					parameters: { theme: 'monobook', defaultview: 'tmonth', views: 'day,week,tmonth', firstday: 'Monday', start: '', gcalurl: null }
				}
			},
			dates: []
		};

		calendar.test._defaults.set( dataOther );

		assert.strictEqual( calendar.test._defaults.theme, 'fc', 'any non-vector theme mapped to the "fc" (fullcalendar) theme' );
		assert.strictEqual( calendar.test._defaults.themeSystem, 'standard', 'any non-vector theme mapped to the "standard" theme system' );
	} );

	QUnit.test( 'defaults.set() replaces day/week/tmonth tokens in defaultview and views', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: {
					parameters: { theme: 'other', defaultview: 'tmonth', views: 'day,week,tmonth', firstday: 'Sunday', start: '', gcalurl: null }
				}
			},
			dates: []
		};

		calendar.test._defaults.set( data );

		assert.strictEqual( calendar.test._defaults.defaultView, 'tMonth', 'defaultview="tmonth" was capitalized to fullcalendar\'s "tMonth"' );
		assert.strictEqual( calendar.test._defaults.view, 'Day,Week,tMonth', 'every day/week/tmonth token in views was capitalized' );
		assert.strictEqual( calendar.test._defaults.firstday, 0, 'firstday="Sunday" resolved to its weekDay index (0)' );

		const data2 = {
			query: {
				ask: {
					parameters: { theme: 'other', defaultview: 'day', views: 'week', firstday: 'Saturday', start: '', gcalurl: 'http://example.org/holidays.ics' }
				}
			},
			dates: []
		};

		calendar.test._defaults.set( data2 );

		assert.strictEqual( calendar.test._defaults.defaultView, 'Day', 'defaultview="day" was capitalized to "Day"' );
		assert.strictEqual( calendar.test._defaults.view, 'Week', 'views="week" was capitalized to "Week"' );
		assert.strictEqual( calendar.test._defaults.firstday, 6, 'firstday="Saturday" resolved to its weekDay index (6)' );
		assert.strictEqual( calendar.test._defaults.holiday, 'http://example.org/holidays.ics', 'a non-null gcalurl was used as-is' );
	} );

	QUnit.test( 'defaults.set() maps a null gcalurl to an empty string', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const data = {
			query: {
				ask: {
					parameters: { theme: 'other', defaultview: 'day', views: 'day', firstday: 'Sunday', start: '', gcalurl: null }
				}
			},
			dates: []
		};

		calendar.test._defaults.set( data );

		assert.strictEqual( calendar.test._defaults.holiday, '', 'gcalurl=null was mapped to an empty string, not left as null' );
	} );

	QUnit.test( 'onDayClick() does nothing when clicktarget is "none"', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		const confirmStub = sinon.stub( global, 'confirm' );

		const date = new Date( '2012-01-01T10:00:00.000Z' );
		const data = { query: { ask: { parameters: { clicktarget: 'none' } } } };

		calendar.test._onDayClick( date, data, { popup: 'Open this day?' } );

		assert.strictEqual( confirmStub.called, false, 'clicktarget "none" skipped the confirm popup entirely' );
	} );

	QUnit.test( 'onDayClick() confirms and opens the resolved clicktarget URL', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		// wgArticlePath/wgServer come from setup.js's static mw.config defaults:
		// ext.srf.formats.eventcalendar.js captures its own `mw` reference the
		// first time it is require()'d (before any test runs), so a later
		// mw.config.set() in an individual test does not reach the module.

		const confirmStub = sinon.stub( global, 'confirm' ).returns( true );
		const openStub = sinon.stub( global.window, 'open' );

		const date = new Date( '2012-03-05T09:15:30.000Z' );
		const data = {
			query: {
				ask: {
					parameters: { clicktarget: 'Special:Foo/%clickyear%-%clickmonth%-%clickday%%clicktime%' }
				}
			}
		};

		calendar.test._onDayClick( date, data, { popup: 'Open this day?' } );

		assert.strictEqual( confirmStub.calledWith( 'Open this day?' ), true, 'confirm() was called with the clickPopup message' );
		assert.strictEqual( openStub.calledOnce, true, 'window.open() was called once the user confirmed' );
		assert.strictEqual( openStub.firstCall.args[ 0 ], 'http://localhost/wiki/Special:Foo/2012-3-5T10:15:30',
			'the clicktarget placeholders were substituted with UTC-hour-shifted date parts' );
		assert.strictEqual( openStub.firstCall.args[ 1 ], '_self', 'the URL was opened in the same window/tab' );
	} );

	QUnit.test( 'onDayClick() does not navigate when the user cancels the confirm popup', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		// wgArticlePath/wgServer come from setup.js's static mw.config defaults:
		// ext.srf.formats.eventcalendar.js captures its own `mw` reference the
		// first time it is require()'d (before any test runs), so a later
		// mw.config.set() in an individual test does not reach the module.

		sinon.stub( global, 'confirm' ).returns( false );
		const openStub = sinon.stub( global.window, 'open' );

		const date = new Date( '2012-03-05T09:15:30.000Z' );
		const data = { query: { ask: { parameters: { clicktarget: 'Special:Foo/%clickyear%' } } } };

		calendar.test._onDayClick( date, data, { popup: 'Open this day?' } );

		assert.strictEqual( openStub.called, false, 'cancelling the confirm popup skipped window.open()' );
	} );

	QUnit.test( 'onDayClick() avoids rolling into the next day when the shifted UTC hour is 24', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		// wgArticlePath/wgServer come from setup.js's static mw.config defaults:
		// ext.srf.formats.eventcalendar.js captures its own `mw` reference the
		// first time it is require()'d (before any test runs), so a later
		// mw.config.set() in an individual test does not reach the module.

		sinon.stub( global, 'confirm' ).returns( true );
		const openStub = sinon.stub( global.window, 'open' );

		// 23:xx UTC + 1 hour === 24; the code special-cases this to stay on the same day (13:xx)
		const date = new Date( '2012-03-05T23:20:10.000Z' );
		const data = { query: { ask: { parameters: { clicktarget: 'Special:Foo/%clicktime%' } } } };

		calendar.test._onDayClick( date, data, { popup: 'Open this day?' } );

		assert.strictEqual( openStub.firstCall.args[ 0 ], 'http://localhost/wiki/Special:Foo/T13:20:10',
			'hour 24 was special-cased to T13 instead of rolling into the next day' );
	} );

	QUnit.test( 'onDayClick() converts a Moment.js-like date argument via toDate()', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		// wgArticlePath/wgServer come from setup.js's static mw.config defaults:
		// ext.srf.formats.eventcalendar.js captures its own `mw` reference the
		// first time it is require()'d (before any test runs), so a later
		// mw.config.set() in an individual test does not reach the module.

		sinon.stub( global, 'confirm' ).returns( true );
		const openStub = sinon.stub( global.window, 'open' );

		const momentLike = { toDate: () => new Date( '2012-06-15T05:00:00.000Z' ) };
		const data = { query: { ask: { parameters: { clicktarget: 'Special:Foo/%clickyear%-%clickmonth%-%clickday%' } } } };

		calendar.test._onDayClick( momentLike, data, { popup: 'Open this day?' } );

		assert.strictEqual( openStub.firstCall.args[ 0 ], 'http://localhost/wiki/Special:Foo/2012-6-15',
			'a Moment.js-like object without getUTCHours was converted via toDate() first' );
	} );

	QUnit.test( 'fullCalendar(...).event().icon() inserts the icon before .fc-event-time when present', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		// srf.util.js captures its own `mw` reference at first require() time,
		// which predates (and is unaffected by) this test's mw.storage state
		// (see tests/node-qunit/setup.js's per-test resetMediaWiki()); stub the
		// getImageURL() lookup itself instead of trying to seed its cache.
		sinon.stub( srf.util.prototype, 'getImageURL' ).callsFake( ( options, callback ) => callback( 'http://localhost/images/icon.png' ) );

		const element = $( '<div><span class="fc-event-time">10:00</span><span class="fc-event-title">Title</span></div>' );
		const event = { eventicon: 'Icon.png' };

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'month' } ).icon();

		assert.strictEqual( element.find( 'img' ).length, 1, 'exactly one image was inserted' );
		assert.true( element.find( '.fc-event-time' ).prev().is( 'img' ), 'the image was inserted immediately before .fc-event-time' );
		assert.strictEqual( element.find( 'img' ).attr( 'src' ), 'http://localhost/images/icon.png',
			'the resolved image URL was used as the img src' );
	} );

	QUnit.test( 'fullCalendar(...).event().icon() falls back to .fc-event-title when .fc-event-time is absent', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		sinon.stub( srf.util.prototype, 'getImageURL' ).callsFake( ( options, callback ) => callback( 'http://localhost/images/icon.png' ) );

		const element = $( '<div><span class="fc-event-title">Title</span></div>' );
		const event = { eventicon: 'Icon.png' };

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'month' } ).icon();

		assert.true( element.find( '.fc-event-title' ).prev().is( 'img' ), 'the image was inserted immediately before .fc-event-title' );
	} );

	QUnit.test( 'fullCalendar(...).event().icon() inserts nothing when getImageURL resolves false', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();
		sinon.stub( srf.util.prototype, 'getImageURL' ).callsFake( ( options, callback ) => callback( false ) );

		const element = $( '<div><span class="fc-event-title">Title</span></div>' );
		const event = { eventicon: 'Icon.png' };

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'month' } ).icon();

		assert.strictEqual( element.find( 'img' ).length, 0, 'no image was inserted when the URL lookup resolved to false' );
	} );

	QUnit.test( 'fullCalendar(...).event().icon() does nothing when the event has no eventicon', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const element = $( '<div><span class="fc-event-title">Title</span></div>' );
		const event = {};

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'month' } ).icon();

		assert.strictEqual( element.find( 'img' ).length, 0, 'no image was inserted without an eventicon' );
	} );

	QUnit.test( 'fullCalendar(...).event().description() appends inline text for day views', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const element = $( '<div><span class="fc-event-title">Title</span></div>' );
		const event = { description: 'Bring snacks' };

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'agendaDay' } ).description();

		assert.strictEqual( element.find( 'span.srf-fc-description' ).length, 1,
			'a day view with an .fc-event-title inserted the description as an inline span' );
		assert.strictEqual( element.find( 'span.srf-fc-description' ).text(), 'Bring snacks',
			'the inline span carried the event description text' );
	} );

	QUnit.test( 'fullCalendar(...).event().description() shows a tooltip for the month view instead of inline text', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		// smw.util.tooltip's .show() is a no-op mock (see tests/node-qunit/setup.js);
		// the module constructs a single instance at load time (_calendar.tooltip,
		// exposed here as test._tooltip) — stub .show() on that shared instance.
		const showStub = sinon.stub( calendar.test._tooltip, 'show' );

		const element = $( '<div><span class="fc-event-title">Title</span></div>' );
		const event = { description: 'Bring snacks' };

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'month' } ).description();

		assert.strictEqual( element.find( 'span.srf-fc-description' ).length, 0,
			'the month view did not append an inline description span' );
		assert.strictEqual( showStub.calledOnce, true, 'the tooltip was shown once instead' );
		assert.strictEqual( showStub.firstCall.args[ 0 ].content, 'Bring snacks',
			'the tooltip content was the (short, so untruncated) event description' );
	} );

	QUnit.test( 'fullCalendar(...).event().description() shows a tooltip for non-day, non-title views', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const showStub = sinon.stub( calendar.test._tooltip, 'show' );

		// No .fc-event-title present at all (e.g. a "basicWeek" cell) — must fall
		// back to the tooltip branch regardless of the view name.
		const element = $( '<div></div>' );
		const event = { description: 'Bring snacks' };

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'basicWeek' } ).description();

		assert.strictEqual( showStub.calledOnce, true, 'missing .fc-event-title fell back to the tooltip' );
	} );

	QUnit.test( 'fullCalendar(...).event().description() does nothing when the event has no description', ( assert ) => {
		const calendar = new srf.formats.eventcalendar();

		const showStub = sinon.stub( calendar.test._tooltip, 'show' );

		const element = $( '<div><span class="fc-event-title">Title</span></div>' );
		const event = {};

		calendar.test._fullCalendarEvent( $( '<div>' ), $( '<div>' ), { events: [] }, event, element, { name: 'agendaDay' } ).description();

		assert.strictEqual( element.find( 'span.srf-fc-description' ).length, 0, 'no inline description span was added' );
		assert.strictEqual( showStub.called, false, 'no tooltip was shown' );
	} );

} );
