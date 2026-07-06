'use strict';

QUnit.module('ext.srf', () => {

	QUnit.test('init', (assert) => {
		assert.ok(srf instanceof Object, 'srf namespace and instance was accessible');
		assert.strictEqual($.type(srf.log), 'function', '.log() was accessible');
		assert.strictEqual($.type(srf.msg), 'function', '.msg() was accessible');
		assert.strictEqual($.type(srf.settings.getList), 'function', '.settings.getList() was accessible');
		assert.strictEqual($.type(srf.settings.get), 'function', '.settings.get() was accessible');
		assert.strictEqual($.type(srf.version), 'function', '.version() was accessible');
	});

	QUnit.test('settings', (assert) => {
		assert.strictEqual($.type(srf.settings.getList()), 'object', '.getList() returned a list of objects');
		assert.strictEqual(
			$.type(srf.settings.get('srfgScriptPath')),
			'string',
			'.get( "srfgScriptPath" ) returned a value'
		);
		assert.strictEqual(srf.settings.get('lula'), undefined, '.get( "lula" ) returned undefined for an unknown key');
		assert.strictEqual(srf.settings.get(), undefined, '.get() returned undefined for an empty key');
	});

	QUnit.test('version', (assert) => {
		assert.strictEqual($.type(srf.version()), 'string', '.version() returned a string');
	});

});
