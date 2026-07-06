'use strict';

const { ViewSelector } = require('./.compiled/Filtered/ViewSelector');
const { Controller } = require('./.compiled/Filtered/Controller');

QUnit.module('ext.srf.filtered ViewSelector', () => {

	QUnit.test('can construct', (assert) => {
		const v = new ViewSelector(undefined, [], undefined);

		assert.ok(v instanceof ViewSelector, 'can construct ViewSelector');
	});

	QUnit.test('init for 1 view', (assert) => {
		let callCount = 0;
		const viewName = 'foo';

		const target = $('<div style="display:none">');
		target.append('<div class="' + viewName + '">');
		target.on = () => {
			callCount++;
			return target;
		};
		target.appendTo('body');

		const v = new ViewSelector(target, [viewName], undefined);

		v.init();

		assert.strictEqual(callCount, 0, 'registers no click events');
		assert.ok(target.is(':hidden'), 'target element is NOT visible');

		target.remove();
	});

	QUnit.test('init for 2 views', (assert) => {
		const target = $('<div style="display:none">');
		const viewSelectors = {};
		const viewIDs = ['foo', 'bar'];

		viewIDs.forEach((id) => {
			viewSelectors[id] = $('<div class="' + id + '">');
			target.append(viewSelectors[id]);
		});

		let eventRegistrationCount = 0;
		target.origOn = target.on;
		target.on = (...args) => {
			eventRegistrationCount++;
			return target.origOn(...args);
		};
		target.appendTo('body');

		const v = new ViewSelector(target, viewIDs, undefined);

		v.init();

		assert.strictEqual(eventRegistrationCount, viewIDs.length, `registers ${viewIDs.length} click events`);
		assert.ok(target.children().first().hasClass('selected'), 'first view selector is marked as selected');
		// jsdom has no real layout engine, so jQuery's :visible (offsetWidth/Height-based)
		// is always false here regardless of display — assert the actual effect instead.
		assert.notStrictEqual(target.css('display'), 'none', 'target element is visible');

		target.remove();
	});

	QUnit.test('selecting views when clicked (3 views: foo, bar, baz)', (assert) => {
		const target = $('<div style="display:none">');
		const viewSelectors = {};
		const viewIDs = ['foo', 'bar', 'baz'];

		viewIDs.forEach((id) => {
			viewSelectors[id] = $('<div class="' + id + '">');
			target.append(viewSelectors[id]);
		});

		target.appendTo('body');

		const c = new Controller(undefined, undefined, undefined);
		c.onViewSelected = (viewID) => {
			assert.ok(true, `Controller was called to select view "${viewID}"`);
		};

		const v = new ViewSelector(target, viewIDs, c);
		v.init();

		assert.expect(6);
		for (const id in viewSelectors) {
			viewSelectors[id].click();

			assert.ok(
				viewSelectors[id].hasClass('selected') && !viewSelectors[id].siblings().hasClass('selected'),
				`view selector "${id}" marked as selected, siblings NOT marked as selected`
			);
		}

		target.remove();
	});

});
