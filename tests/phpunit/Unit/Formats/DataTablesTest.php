<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Test\QueryPrinterRegistryTestCase;

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
class DataTablesTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'datatables' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRF\DataTables';
	}

}
