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
	global.Node = window.Node;
	global.HTMLElement = window.HTMLElement;
	global.$ = global.jQuery = require('jquery');

	return () => {
		global.document.body.innerHTML = '';
	};
}

/**
 * setup minimal MediaWiki globals (mw.loader, mw.message) used by ext.srf.* resources
 *
 * @return {function(): void} a function to reset mw between tests
 */
function prepareMediaWiki() {
	return () => {
		global.mw = global.mediaWiki = {
			loader: {
				using: () => Promise.resolve(),
			},
			message: () => ({
				text: () => '',
			}),
		};
	};
}

const resetMediaWiki = prepareMediaWiki();
resetMediaWiki();

QUnit.module('setup');

QUnit.test('body is cleaned up between tests: 1', (assert) => {
	$('<div>', { id: 1 }).appendTo(document.body);
	assert.equal($('div').length, 1);
});

QUnit.test('body is cleaned up between tests: 2', (assert) => {
	$('<div>', { id: 2 }).appendTo(document.body);
	assert.equal($('div').length, 1);
});
