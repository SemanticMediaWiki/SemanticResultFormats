const path = require('path');
const resetDom = createDom();
const sinon = require('sinon');

QUnit.hooks.beforeEach((assert) => {
	sinon.assert.pass = (message) => assert.pushResult({ result: true, expected: true, actual: true, message });
	sinon.assert.fail = (message) => assert.pushResult({ result: false, expected: true, actual: false, message });
});

QUnit.hooks.afterEach(() => {
	resetDom();
	resetMediaWiki();
	sinon.restore();
});

/**
 * provide a clean jsdom + jQuery environment for each test
 *
 * @return {function(): void} a function to reset the DOM between tests
 */
function createDom() {
	const { JSDOM } = require('jsdom');
	const dom = new JSDOM();
	global.window = dom.window;
	global.document = window.document;
	global.navigator = window.navigator;
	global.Node = window.Node;
	global.HTMLElement = window.HTMLElement;
	global.$ = global.jQuery = require('jquery');
	require(path.resolve(__dirname, '../../resources/jquery/jquery.blockUI.js'));
	require('jquery-ui/ui/widget.js');
	require('jquery-ui/ui/widgets/mouse.js');
	require('jquery-ui/ui/widgets/slider.js');

	// leaflet.markercluster/leaflet-providers monkey-patch new methods (e.g.
	// L.markerClusterGroup) onto the leaflet module object; NODE_PATH (set by the
	// node-qunit npm script) makes formats/filtered's own node_modules resolvable
	// here. These must load before any tsc-compiled `import * as L from 'leaflet'`
	// consumer does — tsc's esModuleInterop wraps the leaflet module in a snapshot
	// object (via __importStar) at the time it's first required, so a plugin that
	// patches leaflet afterwards would be invisible to that snapshot.
	global.L = require('leaflet');
	require('leaflet.markercluster');
	require('leaflet-providers');

	// MediaWiki's bundled jquery.ui.widget.js still sets widgetBaseClass (removed
	// upstream in modern jQuery UI, see jqueryui/jquery-ui#8155); srf widgets rely
	// on it, so restore it here to match the runtime they actually ship against.
	const widgetFactory = $.widget;
	$.widget = $.extend(function (name) {
		const constructor = widgetFactory.apply($, arguments);
		constructor.prototype.widgetBaseClass = constructor.prototype.widgetFullName;
		return constructor;
	}, widgetFactory);

	return () => {
		global.document.body.innerHTML = '';
	};
}

/**
 * minimal mw.html.element/Raw implementation, sufficient for ext.srf.util.js's
 * usage (building small trusted-attribute snippets, no real MediaWiki Sanitizer)
 */
function escapeAttribute(value) {
	return String(value)
		.replace(/&/g, '&amp;')
		.replace(/"/g, '&quot;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;');
}

function Raw(value) {
	this.value = value;
}

function htmlElement(tagName, attrs, contents) {
	attrs = attrs || {};
	const attrString = Object.keys(attrs)
		// mirror mw.html.element: name=true -> bare "name", name=false -> attribute omitted
		.filter((key) => attrs[key] !== false)
		.map((key) => ` ${key}="${escapeAttribute(attrs[key] === true ? key : attrs[key])}"`)
		.join('');
	const inner = contents instanceof Raw ? contents.value : escapeAttribute(contents == null ? '' : contents);
	return `<${tagName}${attrString}>${inner}</${tagName}>`;
}

/**
 * setup minimal MediaWiki globals (mw.loader, mw.message, mw.html, mw.storage,
 * mw.config) used by ext.srf.* resources
 *
 * @return {function(): void} a function to reset mw between tests
 */
function prepareMediaWiki() {
	return () => {
		const configValues = {
			'srf-config': { settings: { srfgScriptPath: '/srf' }, version: '1.0.0' },
			wgScriptPath: '/w',
		};
		const storageValues = {};

		global.mw = global.mediaWiki = {
			loader: {
				using: () => Promise.resolve(),
			},
			message: () => ({
				text: () => '',
			}),
			msg: () => '',
			html: {
				element: htmlElement,
				Raw: Raw,
			},
			storage: {
				get: (key) => (Object.prototype.hasOwnProperty.call(storageValues, key) ? storageValues[key] : null),
				set: (key, value) => {
					storageValues[key] = value;
				},
			},
			config: {
				get: (key) => configValues[key],
				set: (key, value) => {
					configValues[key] = value;
				},
			},
		};
	};
}

/**
 * load resources/ext.srf.js once per test run; the file's only effect is
 * assigning window.srf = window.semanticFormats = <instance>, so re-requiring
 * it would be a no-op after the first load (require caches the module) — that's
 * fine since srf itself is stateless (no per-test mutation needed).
 *
 * @return {void}
 */
function loadSrf() {
	require(path.resolve(__dirname, '../../resources/ext.srf.js'));
	global.srf = global.semanticFormats = window.srf;
	require(path.resolve(__dirname, '../../resources/ext.srf.util.js'));
}

const resetMediaWiki = prepareMediaWiki();
resetMediaWiki();
loadSrf();

QUnit.module('setup');

QUnit.test('body is cleaned up between tests: 1', (assert) => {
	$('<div>', { id: 1 }).appendTo(document.body);
	assert.equal($('div').length, 1);
});

QUnit.test('body is cleaned up between tests: 2', (assert) => {
	$('<div>', { id: 2 }).appendTo(document.body);
	assert.equal($('div').length, 1);
});

QUnit.test('jQuery UI widget factory is available', (assert) => {
	$.widget('test.smoke', {
		_create: function () {
			this.element.addClass('smoke-created');
		},
	});

	const context = $('<div>').appendTo(document.body);
	context.smoke();

	assert.ok(context.hasClass('smoke-created'), 'a custom $.widget was created and its _create() ran');

	const slider = $('<div class="slider">').appendTo(document.body);
	slider.slider({ min: 1, max: 20, value: 10 });

	assert.equal(slider.slider('value'), 10, 'the jQuery UI slider widget initialized with the given value');
});
