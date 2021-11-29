<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\QueryPrinterRegistryTestCase;
use SRFValueRank;

/**
 * Tests for the SRFValueRank class.
 *
 * @file
 * @since 3.2
 *
 * @ingroup SemanticResultFormats
 * @ingroup Test
 *
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 *
 * @licence GNU GPL v2+
 * @author Sebastian Schmid < sebastian.schmid@geinn.it >
 */
class ValueRankTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'valuerank' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public function getClass() {
		return 'SRFValueRank';
	}

	public function testGetName() {

		$instance = new SRFValueRank(
			'valuerank'
		);

		$this->assertInternalType('string', $instance->getName());

	}

}
