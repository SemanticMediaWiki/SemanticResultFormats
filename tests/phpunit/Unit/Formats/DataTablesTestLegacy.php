<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Test\QueryPrinterRegistryTestCase;

class DataTablesLegacyTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'datatables-legacy' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRF\DataTablesLegacy';
	}

}
