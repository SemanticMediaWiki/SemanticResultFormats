<?php

namespace SRF\Test;

use SMW\Test\ResultPrinterTestCase;

/**
 * Tests for the SRF\DataTables class.
 *
 * @file
 * @since 1.9
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
class DataTablesTest extends ResultPrinterTestCase {

	/**
	 * @see ResultPrinterTestCase::getFormats
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function getFormats() {
		return array( 'datatables' );
	}

	/**
	 * @see ResultPrinterTestCase::getClass
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRF\DataTables';
	}

}
