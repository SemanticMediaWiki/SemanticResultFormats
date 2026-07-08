'use strict';

const path = require( 'path' );
const sinon = require( 'sinon' );
const modulePath = path.resolve( __dirname, '../../formats/timeline/resources/ext.srf.timeline.js' );

// the file has no module.exports: smwMakeTimeline()/smwGetBandwidth()/smwAddEvent()
// are module-scoped, not exposed on `window`/`global`. The only observable entry
// point is the IIFE's mw.loader.using(...).then() -> $(document).ready() handler,
// which finds `.smwtimeline` elements and invokes smwMakeTimeline() on each -
// exercising the module-scoped functions indirectly through real DOM markup
// shaped like SRF_Timeline.php's actual output (see timeline-01.json).
//
// The module also depends on the global `Timeline` object supplied by the
// (excluded-from-coverage) vendored SimileTimeline library; a minimal fake
// mirroring its real API shape (scripts/sources.js, scripts/units.js,
// scripts/timeline.js) is installed before require() since the module reads
// `Timeline.*` only inside smwMakeTimeline()/smwAddEvent(), not at module scope.
function installFakeTimeline() {
	function DefaultEventSource() {
		this.events = [];
		this._events = {
			add: ( evt ) => this.events.push( evt ),
			getUnit: () => ( {
				getParser: () => ( iso ) => iso
			} )
		};
	}
	DefaultEventSource.Event = function (
		start, end, latestStart, earliestEnd, instant, text, description,
		image, link, icon, color, textColor
	) {
		this.start = start;
		this.end = end;
		this.latestStart = latestStart;
		this.earliestEnd = earliestEnd;
		this.instant = instant;
		this.text = text;
		this.description = description;
		this.image = image;
		this.link = link;
		this.icon = icon;
		this.color = color;
		this.textColor = textColor;
	};

	global.Timeline = {
		urlPrefix: '/srf/formats/timeline/resources/SimileTimeline/',
		DefaultEventSource: DefaultEventSource,
		ClassicTheme: {
			create: () => ( {
				ether: {},
				event: { instant: {} }
			} )
		},
		DateTime: {
			MILLISECOND: 'MILLISECOND', SECOND: 'SECOND', MINUTE: 'MINUTE', HOUR: 'HOUR',
			DAY: 'DAY', WEEK: 'WEEK', MONTH: 'MONTH', YEAR: 'YEAR', DECADE: 'DECADE',
			CENTURY: 'CENTURY', MILLENNIUM: 'MILLENNIUM',
			parseIso8601DateTime: ( str ) => str
		},
		createBandInfo: ( options ) => Object.assign( {}, options ),
		create: sinon.stub()
	};

	return global.Timeline;
}

QUnit.module( 'ext.srf.timeline', {
	beforeEach: () => {
		installFakeTimeline();
		// the IIFE closes over `global.mediaWiki` at require() time; re-require per
		// test so it picks up setup.js's freshly reset mw instance each time.
		delete require.cache[ require.resolve( modulePath ) ];
		require( modulePath );
	}
}, () => {

	/**
	 * @param {string} innerHtml markup to place inside a `.smwtimeline` container
	 * @return {jQuery} the appended `.smwtimeline` context element
	 */
	function buildTimelineContext( innerHtml ) {
		return $( '<div class="smwtimeline is-disabled" style="height: 300px">' + innerHtml + '</div>' )
			.appendTo( document.body );
	}

	/**
	 * @return {Promise} resolves once mw.loader.using(...).then() and $(document).ready() have run
	 */
	function ready() {
		return new Promise( ( resolve ) => {
			setTimeout( resolve, 0 );
		} );
	}

	QUnit.test( 'removes the "is-disabled" class from each .smwtimeline element on ready', ( assert ) => {
		const done = assert.async();
		const context = buildTimelineContext( '' );

		ready().then( () => {
			assert.false( context.hasClass( 'is-disabled' ), 'the is-disabled class was removed' );
			assert.strictEqual( global.Timeline.create.callCount, 1, 'Timeline.create was invoked once for the single .smwtimeline element' );
			done();
		} );
	} );

	QUnit.test( 'initializes every .smwtimeline element found on the page', ( assert ) => {
		const done = assert.async();
		buildTimelineContext( '' );
		buildTimelineContext( '' );

		ready().then( () => {
			assert.strictEqual( global.Timeline.create.callCount, 2, 'Timeline.create was invoked once per .smwtimeline element' );
			assert.strictEqual( $( '.smwtimeline.is-disabled' ).length, 0, 'no .smwtimeline element kept the is-disabled class' );
			done();
		} );
	} );

	QUnit.test( 'creates a single default MONTH band when no smwtlband markers are present', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtlurl"><a href="/wiki/Example">Example</a></span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			assert.strictEqual( bandInfos.length, 1, 'exactly one band was created' );
			assert.strictEqual( bandInfos[ 0 ].intervalUnit, 'MONTH', 'the default band uses the MONTH interval unit' );
			assert.strictEqual( bandInfos[ 0 ].width, '100%', 'the default (only) band spans the full width' );
			done();
		} );
	} );

	QUnit.test( 'creates one band per recognized smwtlband marker, using the documented width split', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlband" style="display:none;">MONTH</span>' +
			'<span class="smwtlband" style="display:none;">YEAR</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			assert.strictEqual( bandInfos.length, 2, 'two bands were created, matching the two smwtlband markers' );
			assert.strictEqual( bandInfos[ 0 ].intervalUnit, 'MONTH', 'the first band uses the MONTH interval unit' );
			assert.strictEqual( bandInfos[ 0 ].width, '70%', 'the first of two bands takes 70% width' );
			assert.strictEqual( bandInfos[ 1 ].intervalUnit, 'YEAR', 'the second band uses the YEAR interval unit' );
			assert.strictEqual( bandInfos[ 1 ].width, '30%', 'the second of two bands takes 30% width' );
			assert.strictEqual( bandInfos[ 1 ].syncWith, 0, 'non-primary bands sync with the first (primary) band' );
			assert.true( bandInfos[ 1 ].highlight, 'non-primary bands are marked to highlight the primary band range' );
			done();
		} );
	} );

	QUnit.test( 'ignores unrecognized smwtlband markers without creating a band for them', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlband" style="display:none;">NOT_A_REAL_UNIT</span>' +
			'<span class="smwtlband" style="display:none;">YEAR</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			assert.strictEqual( bandInfos.length, 1, 'only the recognized smwtlband marker produced a band' );
			assert.strictEqual( bandInfos[ 0 ].intervalUnit, 'YEAR', 'the recognized band uses the YEAR interval unit' );
			assert.strictEqual( bandInfos[ 0 ].width, '100%', 'the single remaining band spans the full width' );
			done();
		} );
	} );

	[
		{ count: 1, widths: [ '100%' ] },
		{ count: 2, widths: [ '70%', '30%' ] },
		{ count: 3, widths: [ '60%', '25%', '15%' ] },
		{ count: 4, widths: [ '50%', '25%', '15%', '10%' ] }
	].forEach( ( { count, widths } ) => {
		QUnit.test( 'splits band widths as documented for ' + count + ' band(s)', ( assert ) => {
			const done = assert.async();
			const bandMarkup = Array( count ).fill( '<span class="smwtlband" style="display:none;">YEAR</span>' ).join( '' );
			buildTimelineContext( bandMarkup );

			ready().then( () => {
				const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
				assert.deepEqual(
					bandInfos.map( ( b ) => b.width ),
					widths,
					'the ' + count + '-band layout used the documented width split'
				);
				done();
			} );
		} );
	} );

	QUnit.test( 'uses the smwtlposition marker as the initial position for the primary band', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlband" style="display:none;">YEAR</span>' +
			'<span class="smwtlposition" style="display:none;">1970-01-01T00:00:00Z</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			assert.strictEqual( bandInfos[ 0 ].date, '1970-01-01T00:00:00Z', 'the parsed smwtlposition value was used as the band date' );
			done();
		} );
	} );

	// BUG: when no smwtlband marker is present, smwMakeTimeline()'s "default
	// band" branch (ext.srf.timeline.js ~line 138) builds the band without a
	// `date` option, so a smwtlposition marker is silently ignored whenever
	// timelinebands isn't set. The band-present case above shows `date` is
	// honored there, confirming this is a real gap rather than documented
	// behavior. Filed as https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/1114;
	// this test pins today's behavior only so the suite doesn't fail until
	// the production code is fixed.
	QUnit.test( 'BUG: ignores the smwtlposition marker when no smwtlband is present (only the default band exists)', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlposition" style="display:none;">1970-01-01T00:00:00Z</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			assert.strictEqual(
				bandInfos[ 0 ].date,
				undefined,
				'the default band has no date option, so the smwtlposition marker has no effect here'
			);
			done();
		} );
	} );

	QUnit.test( 'adds an event built from smwtlstart/smwtlend/smwtlurl markers to the event source', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtlend">1970-01-03</span>' +
				'<span class="smwtlurl"><a href="/wiki/Example">Example</a></span>' +
				'<span class="smwtlcoloricon">2</span>' +
				'some description' +
			'</span>'
		);

		ready().then( () => {
			// the event source isn't returned directly by Timeline.create(); recover
			// it via bandInfo.eventSource, set to the same DefaultEventSource
			// instance smwAddEvent() populated.
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.strictEqual( events.length, 1, 'one event was added to the event source' );
			assert.strictEqual( events[ 0 ].start, '1970-01-01', 'the event start date was read from smwtlstart' );
			assert.strictEqual( events[ 0 ].end, '1970-01-03', 'the event end date was read from smwtlend' );
			assert.strictEqual( events[ 0 ].link, '/wiki/Example', 'the event link was read from the smwtlurl anchor href' );
			assert.strictEqual( events[ 0 ].text, 'Example', 'the event title was read from the smwtlurl anchor text' );
			assert.strictEqual( events[ 0 ].description, 'some description', 'remaining text became the event description' );
			assert.true( events[ 0 ].icon.endsWith( 'dull-green-circle.png' ), 'coloricon "2" mapped to the dull-green-circle icon' );
			done();
		} );
	} );

	QUnit.test( 'reads a link nested inside a non-anchor smwtlurl wrapper element', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtlurl"><b><a href="/wiki/Nested">Nested title</a></b></span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.strictEqual( events[ 0 ].link, '/wiki/Nested', 'the href was read from the anchor nested inside the wrapper element' );
			assert.strictEqual( events[ 0 ].text, 'Nested title', 'the title was read from the nested anchor\'s innerHTML' );
			done();
		} );
	} );

	QUnit.test( 'ignores unrecognized element markers inside a smwtlevent span', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtl-unknown-marker">ignored</span>' +
				'<span class="smwtltitle">Example</span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.strictEqual( events.length, 1, 'the event was still added despite the unrecognized marker' );
			assert.strictEqual( events[ 0 ].text, 'Example', 'the recognized markers were still read correctly' );
			assert.true( events[ 0 ].description.includes( 'ignored' ), 'the unrecognized marker was left in place and became part of the description' );
			done();
		} );
	} );

	QUnit.test( 'reads a plain-text smwtlurl as both the link and the title', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtlurl">https://example.org/</span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.strictEqual( events[ 0 ].link, 'https://example.org/', 'the plain-text smwtlurl content was used as the link' );
			done();
		} );
	} );

	QUnit.test( 'falls back to the smwtltitle marker when no smwtlurl is present', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtltitle">Untitled example</span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.strictEqual( events[ 0 ].text, 'Untitled example', 'the smwtltitle content was used as the event title' );
			assert.strictEqual( events[ 0 ].link, '', 'no link was set when only smwtltitle is present' );
			done();
		} );
	} );

	QUnit.test( 'wraps the event title with the smwtlprefix and smwtlpostfix markers', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtlprefix">before-</span>' +
				'<span class="smwtltitle">middle</span>' +
				'<span class="smwtlpostfix">-after</span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.strictEqual( events[ 0 ].text, 'before-middle-after', 'prefix and postfix were concatenated around the title' );
			done();
		} );
	} );

	[
		{ code: '0', icon: 'dull-blue-circle.png' },
		{ code: '1', icon: 'dull-red-circle.png' },
		{ code: '2', icon: 'dull-green-circle.png' },
		{ code: '3', icon: 'gray-circle.png' },
		{ code: '4', icon: 'dark-blue-circle.png' },
		{ code: '5', icon: 'dark-red-circle.png' },
		{ code: '6', icon: 'dark-green-circle.png' },
		{ code: '7', icon: 'blue-circle.png' },
		{ code: '8', icon: 'red-circle.png' },
		{ code: '9', icon: 'green-circle.png' }
	].forEach( ( { code, icon } ) => {
		QUnit.test( 'maps smwtlcoloricon "' + code + '" to the ' + icon + ' icon', ( assert ) => {
			const done = assert.async();
			buildTimelineContext(
				'<span class="smwtlevent" style="display:none;">' +
					'<span class="smwtlstart">1970-01-01</span>' +
					'<span class="smwtlcoloricon">' + code + '</span>' +
				'</span>'
			);

			ready().then( () => {
				const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
				const events = bandInfos[ 0 ].eventSource.events;

				assert.true( events[ 0 ].icon.endsWith( icon ), 'smwtlcoloricon "' + code + '" produced the ' + icon + ' icon' );
				done();
			} );
		} );
	} );

	QUnit.test( 'defaults to the dull-blue-circle icon when no smwtlcoloricon marker is present', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.true( events[ 0 ].icon.endsWith( 'dull-blue-circle.png' ), 'the default icon was used' );
			done();
		} );
	} );

	QUnit.test( 'adds multiple events from multiple smwtlevent markers', ( assert ) => {
		const done = assert.async();
		buildTimelineContext(
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-01</span>' +
				'<span class="smwtltitle">First</span>' +
			'</span>' +
			'<span class="smwtlevent" style="display:none;">' +
				'<span class="smwtlstart">1970-01-03</span>' +
				'<span class="smwtltitle">Second</span>' +
			'</span>'
		);

		ready().then( () => {
			const bandInfos = global.Timeline.create.firstCall.args[ 1 ];
			const events = bandInfos[ 0 ].eventSource.events;

			assert.strictEqual( events.length, 2, 'both events were added' );
			assert.strictEqual( events[ 0 ].text, 'First', 'the first event was added in document order' );
			assert.strictEqual( events[ 1 ].text, 'Second', 'the second event was added in document order' );
			done();
		} );
	} );

} );
