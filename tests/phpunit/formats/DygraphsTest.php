<?php

namespace SRF\Test;

use SMW\Test\ResultPrinterTestCase;

/**
 * Tests for the SRF\Dygraphs class.
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
 * @licence GNU GPL v2+
 * @author mwjames
 */
class DygraphsTest extends ResultPrinterTestCase {

	/**
	 * @see ResultPrinterTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return array( 'dygraphs' );
	}

	/**
	 * @see ResultPrinterTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRFDygraphs';
	}

}
