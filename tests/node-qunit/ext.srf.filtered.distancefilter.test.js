'use strict';

const { DistanceFilter } = require('./.compiled/Filtered/Filter/DistanceFilter');
const { Controller } = require('./.compiled/Filtered/Controller');

QUnit.module('ext.srf.filtered DistanceFilter', () => {

	const origin = { lat: 0, lng: 0 };

	QUnit.test('init builds the distance slider', (assert) => {
		assert.expect(1);
		const data = {
			row1: { data: { foo: { positions: [{ lat: 0, lng: 1 }] } } },
		};
		const controller = new Controller($(), data, {});
		const target = $('<div>');
		const f = new DistanceFilter('foo', target, 'fooPR', controller, { origin: origin });

		f.init();

		const done = assert.async();
		setTimeout(() => {
			assert.ok(target.find('.filtered-distance-slider')[0].hasChildNodes(), 'Added distance slider.');
			done();
		}, 100);
	});

	QUnit.test('init without a usable origin detaches the target instead of throwing', (assert) => {
		const data = { row1: { data: { foo: { positions: [{ lat: 0, lng: 1 }] } } } };
		const controller = new Controller($(), data, {});
		const target = $('<div>').appendTo(document.body);
		const f = new DistanceFilter('foo', target, 'fooPR', controller, {});

		f.init();

		assert.strictEqual($.contains(document.body, target[0]), false, 'target was detached when no origin was configured');
	});

	QUnit.test('haversine distance: rows are filtered by distance from origin via isVisible()', (assert) => {
		// row1 is ~111 km from origin (1 degree of longitude at the equator),
		// row2 is at the origin itself (distance 0)
		const data = {
			row1: { data: { foo: { positions: [{ lat: 0, lng: 1 }] } } },
			row2: { data: { foo: { positions: [{ lat: 0, lng: 0 }] } } },
		};
		const controller = new Controller($(), data, {});
		const target = $('<div>');
		const f = new DistanceFilter('foo', target, 'fooPR', controller, { origin: origin, 'initial value': 50 });

		f.init();

		assert.strictEqual(f.isVisible('row1'), false, 'a row ~111 km away is not visible with a 50 km filter value');
		assert.strictEqual(f.isVisible('row2'), true, 'a row at the origin (0 km away) is visible with a 50 km filter value');
	});

	QUnit.test('haversine distance: nearest of multiple positions is used', (assert) => {
		// one position far away (~111 km), one at the origin -> nearest (0 km) should win
		const data = {
			row1: { data: { foo: { positions: [{ lat: 0, lng: 1 }, { lat: 0, lng: 0 }] } } },
		};
		const controller = new Controller($(), data, {});
		const target = $('<div>');
		const f = new DistanceFilter('foo', target, 'fooPR', controller, { origin: origin, 'initial value': 1 });

		f.init();

		assert.strictEqual(f.isVisible('row1'), true, 'row is visible because its nearest position is at the origin');
	});

	QUnit.test('rows without data for the filtered property do not throw and are treated as infinitely far', (assert) => {
		const data = {
			row1: { data: { foo: { positions: [{ lat: 0, lng: 1 }] } } },
			row2: { data: {} },
		};
		const controller = new Controller($(), data, {});
		const target = $('<div>');
		const f = new DistanceFilter('foo', target, 'fooPR', controller, { origin: origin, 'initial value': 1000000 });

		let threw = false;
		try {
			f.init();
		} catch (e) {
			threw = true;
		}
		assert.ok(!threw, 'init() does not throw when a row lacks data for the filtered property');

		assert.strictEqual(f.isVisible('row2'), false, 'a row without data for the filtered property is never visible, regardless of filter value');
	});

});
