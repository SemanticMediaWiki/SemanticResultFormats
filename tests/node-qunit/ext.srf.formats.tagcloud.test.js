'use strict';

require(require('path').resolve(__dirname, '../../formats/tagcloud/resources/ext.srf.formats.tagcloud.js'));

// Only init/dependencies/load (pure logic) are covered here. sphere()/wordcloud()
// need real tagcanvas + d3 canvas/SVG rendering and stay in the legacy
// browser-QUnit suite — the source itself has a longstanding comment admitting
// instability even there ("Somewhere around here QUnit dies (with a timeout)").
// See issue #1073 for the broader legacy-test documentation effort.
QUnit.module('ext.srf.formats.tagcloud', () => {

	// module-level context is reused/mutated across sub-tests in the legacy
	// suite; execution order matters, preserved here in a single test block
	const context = $(
		'<div><div id="test" class="srf-container">' +
		'<div id="test1" class="srf-tags"><ul>' +
		'<li><a href="/test1">Test1</a></li>' +
		'<li><a href="/test2">Test2</a></li>' +
		'<li>TextOnly</li>' +
		'</ul></div></div></div>'
	);

	QUnit.test('init', (assert) => {
		const tagcloud = new srf.formats.tagcloud();

		assert.equal($.type(tagcloud.defaults), 'object', '.defaults was accessible');
		assert.equal($.type(tagcloud.sphere), 'function', '.sphere() was accessible');
		assert.equal($.type(tagcloud.wordcloud), 'function', '.wordcloud() was accessible');
		assert.equal($.type(tagcloud.load), 'function', '.load() was accessible');
	});

	QUnit.test('dependencies', (assert) => {
		const util = new srf.util();

		assert.equal($.type(util.assert), 'function', 'util.assert was accessible');
		assert.equal($.type(smw.async.load), 'function', 'smw.async.load was accessible');
		assert.equal($.type(util.spinner.hide), 'function', 'util.spinner.hide was accessible');
		assert.equal($.type(util.message.set), 'function', 'util.message.set was accessible');
	});

	QUnit.test('load', (assert) => {
		const tagcloud = new srf.formats.tagcloud();
		let result;
		let options;

		context.data('version', '0.4.1');

		options = {
			context: context,
			element: 'canvas',
			module: 'ext.jquery.tagcanvas',
			method: tagcloud.sphere,
		};
		result = tagcloud.load(options);
		assert.ok(result, 'sphere was initialized');

		options = {
			context: context,
			element: 'svg',
			module: 'ext.d3.wordcloud',
			method: tagcloud.wordcloud,
		};
		result = tagcloud.load(options);
		assert.ok(result, 'wordcloud was initialized');

		// Check for a non existing element
		options = {
			context: context,
			element: 'lula',
			module: '',
			method: '',
		};
		result = tagcloud.load(options);
		assert.ok(result, 'non existing element');

		// Check invalid version
		options = {
			context: context,
			element: 'lula',
			module: '',
			method: '',
		};
		tagcloud.version = '0.4.2';
		result = tagcloud.load(options);
		assert.equal(result, false, 'wrong version');
	});

});
