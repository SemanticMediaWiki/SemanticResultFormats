'use strict';

require(require('path').resolve(__dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js'));
require(require('path').resolve(__dirname, '../../formats/gallery/resources/ext.srf.gallery.redirect.js'));

const mockUtil = function () {
	return {
		spinner: {
			create: function () {},
			replace: function () {},
		},
		getTitleURL: function (params, callback) {
			if (params.title === 'Valid_Title') {
				callback('/wiki/Valid_Title');
			} else {
				callback(false);
			}
		},
	};
};

let originalUtil;

QUnit.module('ext.srf.formats.gallery.redirect', {
	beforeEach: () => {
		originalUtil = srf.util;
		srf.util = mockUtil;
	},
	afterEach: () => {
		srf.util = originalUtil;
	},
}, () => {

	QUnit.test('redirect handles mw-file-description selector', (assert) => {
		const mockHtml = $(
			'<div class="srf-redirect" data-redirect-type="_uri">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description" href="/wiki/File:Test.jpg">' +
								'<img alt="http://example.com/redirect-target" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$(document.body).append(mockHtml);
		const context = mockHtml;
		const gallery = new srf.formats.gallery();

		gallery.redirect(context);

		const imageLink = context.find('a.mw-file-description');
		const redirectIcon = context.find('.redirecticon');

		assert.ok(imageLink.length > 0, 'Found image link with mw-file-description class');
		assert.equal(imageLink.attr('href'), 'http://example.com/redirect-target', 'Image href updated to redirect URL from alt attribute');
		assert.notEqual(redirectIcon.css('display'), 'none', 'Redirect icon is visible for URI type redirect');
	});

	QUnit.test('redirect resolves article titles', (assert) => {
		const done = assert.async();

		const mockHtml = $(
			'<div class="srf-redirect" data-redirect-type="article">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description" href="/wiki/File:Test.jpg">' +
								'<img alt="Valid_Title" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$(document.body).append(mockHtml);
		const context = mockHtml;
		const gallery = new srf.formats.gallery();

		gallery.redirect(context);

		setTimeout(() => {
			const imageLink = context.find('a.mw-file-description');
			const redirectIcon = context.find('.redirecticon');

			assert.ok(imageLink.length > 0, 'Found image link with mw-file-description class');
			assert.equal(imageLink.attr('href'), '/wiki/Valid_Title', 'Image href updated to resolved article URL');
			assert.notEqual(redirectIcon.css('display'), 'none', 'Redirect icon is visible after successful title resolution');
			done();
		}, 100);
	});

	QUnit.test('redirect handles missing href gracefully', (assert) => {
		const mockHtml = $(
			'<div class="srf-redirect" data-redirect-type="_uri">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description">' +
								'<img alt="http://example.com/redirect" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$(document.body).append(mockHtml);
		const context = mockHtml;
		const gallery = new srf.formats.gallery();

		gallery.redirect(context);

		const galleryBox = context.find('.gallerybox');
		assert.ok(galleryBox.find('.error').length > 0, 'Error message displayed for missing href');
		assert.ok(galleryBox.html().indexOf('srf-gallery-image-url-error') > -1, 'Correct error message key used');
	});

	QUnit.test('redirect handles failed title resolution', (assert) => {
		const done = assert.async();

		const mockHtml = $(
			'<div class="srf-redirect" data-redirect-type="article">' +
				'<ul class="gallery mw-gallery-traditional">' +
					'<li class="gallerybox">' +
						'<div>' +
							'<a class="mw-file-description" href="/wiki/File:Test.jpg">' +
								'<img alt="Invalid_Title" src="/thumb/Test.jpg/120px-Test.jpg" />' +
							'</a>' +
						'</div>' +
					'</li>' +
				'</ul>' +
			'</div>'
		);

		$(document.body).append(mockHtml);
		const context = mockHtml;
		const gallery = new srf.formats.gallery();

		gallery.redirect(context);

		setTimeout(() => {
			const imageLink = context.find('a.mw-file-description');
			const redirectIcon = context.find('.redirecticon');

			assert.equal(imageLink.attr('href'), '', 'Image href cleared on failed title resolution');
			assert.ok(redirectIcon.is(':hidden'), 'Redirect icon hidden on failed title resolution');
			done();
		}, 100);
	});

});
