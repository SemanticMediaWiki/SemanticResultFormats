'use strict';

require(require('path').resolve(__dirname, '../../resources/widgets/ext.srf.widgets.panel.js'));

QUnit.module('ext.srf.widgets.panel', () => {

	QUnit.test('instance', (assert) => {
		const context = $('<div class="test"></div>').appendTo(document.body);

		context.panel({
			show: true,
		});

		assert.strictEqual(context.next('.srf-panel').length, 1,
			'the srf.panel widget inserted a .srf-panel element as the context\'s next sibling');
	});

	QUnit.test('add portlet', (assert) => {
		const context = $('<div class="test"></div>').appendTo(document.body);

		context.panel({
			show: true,
		});

		const portlet = context.panel('portlet', {
			class: 'portlet',
			title: 'portlet',
			fieldset: true,
		});

		assert.ok(portlet.is('.portlet') && portlet.children('fieldset').length > 0,
			'the srf.panel widget added a portlet containing a fieldset');
	});

});
