<?php

namespace SRF\Test;
use SMW\Tests\ResultPrinterTest;

/**
 *  Tests for the SRF\Excel class.
 *
 * @since 1.9
 *
 * @ingroup SemanticResultFormats
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @author Kim Eik
 */
class ExcelTest extends ResultPrinterTest {

	/**
	 * @see ResultPrinterTest::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return array( 'excel' );
	}

	/**
	 * @see ResultPrinterTest::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRF\SRFExcel';
	}

}
