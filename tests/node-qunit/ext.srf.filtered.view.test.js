'use strict';

const { View } = require('./.compiled/Filtered/View/View');
const { Controller } = require('./.compiled/Filtered/Controller');

function getTestObject(id = 'foo', target, c, options = {}) {
	c = c || new Controller(undefined, {}, undefined);
	return new View(id, target, c, options);
}

QUnit.module('ext.srf.filtered View', () => {

	QUnit.test('can construct, init and knows target element', (assert) => {
		const target = $('<div>');

		const v = getTestObject('foo', target);
		const ret = v.init();

		if (ret !== undefined) {
			const done = assert.async();

			ret.then(() => {
				assert.ok(v instanceof View, 'can construct View (P)');
				assert.strictEqual(v.getTargetElement(), target, 'View retains target element (P)');
				done();
			});
		} else {
			assert.ok(v instanceof View, 'can construct View');
			assert.strictEqual(v.getTargetElement(), target, 'View retains target element');
		}
	});

	QUnit.test('show and hide', (assert) => {
		const target = $('<div>');

		target.show = () => {
			assert.ok(true, 'target element shown');
			return target;
		};
		target.hide = () => {
			assert.ok(true, 'target element hidden');
			return target;
		};

		const v = getTestObject('foo', target);
		v.init();

		v.show();
		v.hide();

		assert.expect(2);
	});

	QUnit.test('showRows and hideRows do not throw for unknown rowId', (assert) => {
		// Regression test for #394: "Cannot read property 'slideDown' of undefined"
		// — showRows/hideRows must guard against rowIds with no corresponding DOM element.
		const target = $('<div>');
		const v = getTestObject('foo', target);
		v.init();
		v.show();

		let showRowsThrew = false;
		try {
			v.showRows(['nonexistent-row']);
		} catch (e) {
			showRowsThrew = true;
		}
		assert.ok(!showRowsThrew, 'showRows does not throw for unknown rowId');

		v.hide();

		let hideRowsThrew = false;
		try {
			v.hideRows(['nonexistent-row']);
		} catch (e) {
			hideRowsThrew = true;
		}
		assert.ok(!hideRowsThrew, 'hideRows does not throw for unknown rowId');

		assert.expect(2);
	});

});
