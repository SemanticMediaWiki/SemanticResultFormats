<?php

namespace SRF\Test;

use SMW\Test\ResultPrinterTestCase;

/**
 * Tests for the SRF\Incoming class.
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
class IncomingTest extends ResultPrinterTestCase {

	/**
	 * @see ResultPrinterTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return array( 'incoming' );
	}

	/**
	 * @see ResultPrinterTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRFIncoming';
	}

}
