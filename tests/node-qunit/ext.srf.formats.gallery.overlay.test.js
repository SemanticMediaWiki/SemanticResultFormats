'use strict';

require(require('path').resolve(__dirname, '../../formats/gallery/resources/ext.srf.formats.gallery.js'));
require(require('path').resolve(__dirname, '../../formats/gallery/resources/ext.srf.gallery.overlay.js'));

const sinon = require('sinon');

QUnit.module('ext.srf.formats.gallery.overlay', {
	// overlay() unconditionally calls .fancybox() on the matched anchors, even in
	// the "empty gallery"/"missing href" cases below — stub it as a no-op plugin.
	beforeEach: () => {
		$.fn.fancybox = $.fn.fancybox || function () { return this; };
		sinon.stub($.fn, 'fancybox').returnsThis();
	},
	afterEach: () => {
		$.fn.fancybox.restore();
	},
}, () => {

	QUnit.test('overlay handles mw-file-description selector', (assert) => {
		const mockHtml = $(
			'<ul class="gallery mw-gallery-traditional" id="test-gallery">' +
				'<li class="gallerybox" style="width: 155px">' +
					'<div style="width: 155px">' +
						'<div class="thumb" style="width: 150px;">' +
							'<div style="margin:15px auto;">' +
								'<a class="mw-file-description" href="/wiki/File:Test.jpg" title="Test image">' +
									'<img alt="Test image" src="/thumb/Test.jpg/120px-Test.jpg" width="120" height="90" />' +
								'</a>' +
							'</div>' +
						'</div>' +
						'<div class="gallerytext">' +
							'<p>Test image description</p>' +
						'</div>' +
					'</div>' +
				'</li>' +
			'</ul>'
		);

		$(document.body).append(mockHtml);
		const context = $('#test-gallery');
		const gallery = new srf.formats.gallery();

		gallery.overlay(context, 'File');

		const imageLink = context.find('a.mw-file-description');
		assert.ok(imageLink.length > 0, 'Found image link with mw-file-description class');
		assert.equal(imageLink.attr('rel'), 'test-gallery', 'Image link has correct rel attribute for grouping');
		assert.equal(imageLink.attr('title'), 'Test image description', 'Image link has correct title from gallery text');
		assert.ok(imageLink.attr('href').indexOf('File:Test.jpg') > -1, 'Image link href points to correct file');
	});

	QUnit.test('overlay handles empty gallery gracefully', (assert) => {
		const emptyGallery = $('<ul class="gallery mw-gallery-traditional" id="empty-gallery"></ul>');
		$(document.body).append(emptyGallery);

		const gallery = new srf.formats.gallery();

		gallery.overlay(emptyGallery, 'File');

		assert.ok(true, 'Overlay handles empty gallery without errors');
	});

	QUnit.test('overlay handles missing href gracefully', (assert) => {
		const mockHtml = $(
			'<ul class="gallery mw-gallery-traditional" id="test-gallery-no-href">' +
				'<li class="gallerybox">' +
					'<div>' +
						'<a class="mw-file-description" title="Test image">' +
							'<img alt="Test image" src="/thumb/Test.jpg/120px-Test.jpg" />' +
						'</a>' +
					'</div>' +
					'<div class="gallerytext"><p>Test description</p></div>' +
				'</li>' +
			'</ul>'
		);

		$(document.body).append(mockHtml);
		const context = $('#test-gallery-no-href');
		const gallery = new srf.formats.gallery();

		gallery.overlay(context, 'File');

		const galleryBox = context.find('.gallerybox');
		assert.ok(galleryBox.find('.error').length > 0, 'Error message displayed for missing href');
		assert.ok(galleryBox.html().indexOf('srf-gallery-image-url-error') > -1, 'Correct error message key used');
	});

});
