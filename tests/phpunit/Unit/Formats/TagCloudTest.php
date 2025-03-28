<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\QueryPrinterRegistryTestCase;

/**
 * Tests for the SRF\TagCloud class.
 *
 * @since 1.8
 *
 * @file
 *
 * @ingroup SRF
 * @ingroup Test
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

/**
 * Tests for the SRF\TagCloud class.
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 */
class TagCloudTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'tagcloud' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRF\TagCloud';
	}

}
