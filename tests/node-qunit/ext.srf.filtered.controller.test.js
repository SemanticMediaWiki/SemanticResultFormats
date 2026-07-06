'use strict';

const { Controller } = require('./.compiled/Filtered/Controller');
const { View } = require('./.compiled/Filtered/View/View');
const { MockedFilter } = require('./ext.srf.filtered.mocked-filter');

QUnit.module('ext.srf.filtered Controller', () => {

	QUnit.test('can construct and attach data', (assert) => {
		const data = { foo: {} };

		const c = new Controller(undefined, data, {});

		assert.ok(c instanceof Controller, 'can construct Controller');
		assert.deepEqual(c.getData(), data, 'returns result data as given to constructor');
	});

	QUnit.test('attaching 3 views (foo, bar, baz) and switching between them', (assert) => {
		const c = new Controller(undefined, undefined, undefined);
		const viewIds = ['foo', 'bar', 'baz'];
		const viewsShown = [];
		const viewsHidden = [];
		const views = {};

		viewIds.forEach((viewId) => {
			const v = new View(viewId, undefined, c, {});

			v.show = () => {
				if (viewsShown.indexOf(v) === -1) {
					viewsShown.push(v);
				}
				const index = viewsHidden.indexOf(v);
				if (index >= 0) {
					viewsHidden.splice(index, 1);
				}
			};

			v.hide = () => {
				if (viewsHidden.indexOf(v) === -1) {
					viewsHidden.push(v);
				}
				const index = viewsShown.indexOf(v);
				if (index >= 0) {
					viewsShown.splice(index, 1);
				}
			};

			views[viewId] = v;

			c.attachView(viewId, v);
		});

		assert.strictEqual(viewsShown.length, 1, 'one view visible');
		assert.strictEqual(viewsHidden.length, viewIds.length - 1, 'all but one view hidden');

		for (const viewId in views) {
			assert.deepEqual(c.getView(viewId), views[viewId], `Controller knows "${viewId}" view`);
		}

		for (const viewId in views) {
			c.onViewSelected(viewId);

			assert.ok(
				viewsShown.length === 1 && viewsShown.indexOf(views[viewId]) >= 0,
				'selected view visible'
			);
			assert.strictEqual(viewsHidden.length, viewIds.length - 1, 'all other views hidden');
		}
	});

	QUnit.test('show', (assert) => {
		let targetShown = false;

		const targetElement = $();
		targetElement.children = () => {
			const targetChild = $();
			targetChild.show = () => {
				targetShown = true;
				return targetChild;
			};
			return targetChild;
		};

		new Controller(targetElement, undefined, undefined).show();

		assert.ok(targetShown, 'container made visible');
	});

	QUnit.test('attaching 3 filters (foo, bar, baz)', (assert) => {
		const data = { foo: {} };
		const controller = new Controller(undefined, data, {});
		const filterIds = ['foo', 'bar', 'baz'];

		const done = assert.async();
		const promises = [];

		filterIds.forEach((filterId) => {
			let visibilityWasQueried = false;

			const filter = new MockedFilter(filterId, undefined, undefined, controller);
			filter.isVisible = () => {
				visibilityWasQueried = true;
				return true;
			};

			const promise = controller.attachFilter(filter).then(() => {
				assert.ok(visibilityWasQueried, `filter "${filterId}" was queried after attaching`);
			});

			promises.push(promise);

			assert.deepEqual(controller.getFilter(filterId), filter, `Controller knows "${filterId}" filter`);
		});

		$.when(...promises).then(done);
	});

});
