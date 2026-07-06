'use strict';

const { ValueFilter } = require('./.compiled/Filtered/Filter/ValueFilter');
const { Controller } = require('./.compiled/Filtered/Controller');

// Covers only the checkboxes control path (options.values.length <= "max checkboxes",
// default 5) — the select2 control path is covered separately by
// ext.srf.filtered.select2.test.js. ValueFilter.isVisible()/onFilterUpdated() are not
// covered here either, matching a pre-existing gap noted in the source's own TODO.
QUnit.module('ext.srf.filtered ValueFilter', () => {

	QUnit.test('can construct', (assert) => {
		const f = new ValueFilter('foo', $(), 'fooPR', undefined, {});

		assert.ok(f instanceof ValueFilter, 'can construct ValueFilter');
	});

	QUnit.test('init', (assert) => {
		const controller = new Controller($(), {}, {});
		const options = {
			switches: ['and or'],
			values: ['foo', 'bar'],
			collapsible: 'uncollapsed',
			type: 'value',
			label: 'FooLabel',
		};
		const target = $('<div>');
		const f = new ValueFilter('foo', target, 'fooPR', controller, options);

		f.init();

		assert.strictEqual(target.find('.filtered-filter-container').length, 1, 'added container for collapsable content');
		assert.strictEqual(target.find('.filtered-value-andor').length, 1, 'added container for and/or switch');

		const done = assert.async();
		setTimeout(() => {
			options.values.forEach((value) => {
				assert.strictEqual(
					target.find(`input[value="${value}"]`).length,
					1,
					`added option for value "${value}"`
				);
			});
			done();
		}, 100);
	});

	QUnit.test('update on and/or switch', (assert) => {
		const controller = new Controller($(), {}, {});
		controller.onFilterUpdated = () => {
			assert.ok(true, 'filter updated');
			return $.Deferred().resolve().promise();
		};

		const f = new ValueFilter('foo', $(), 'fooPR', controller, {});

		assert.expect(1);

		f.useOr(true);
	});

});
