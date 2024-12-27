<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\QueryPrinterRegistryTestCase;

/**
 *  Tests for the SRF\Math class.
 *
 * @file
 * @since 1.8
 *
 * @ingroup SemanticResultFormats
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class MathTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'max', 'min', 'sum', 'product', 'average', 'median' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRFMath';
	}

}
