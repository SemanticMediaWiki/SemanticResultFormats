<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\QueryPrinterRegistryTestCase;

/**
 * Tests for the SRF\jqPlotChart class.
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
 * @author mwjames
 */
class jqPlotChartTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'jqplotchart' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRFjqPlotChart';
	}

}
