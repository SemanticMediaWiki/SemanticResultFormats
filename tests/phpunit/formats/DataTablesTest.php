<?php

namespace SRF\Test;
use SMW\Tests\ResultPrinterTest;

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
class DataTablesTest extends ResultPrinterTest {

	/**
	 * @see ResultPrinterTest::getFormats
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function getFormats() {
		return array( 'datatables' );
	}

	/**
	 * @see ResultPrinterTest::getClass
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRF\DataTables';
	}

}
