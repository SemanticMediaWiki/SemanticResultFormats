<?php

namespace SRF\Tests\Unit\Formats;

use SMW\Tests\PHPUnitCompat;
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
 * @license GPL-2.0-or-later
 * @author Sebastian Schmid < sebastian.schmid@geinn.it >
 */
class ValueRankTest extends QueryPrinterRegistryTestCase {

	use PHPUnitCompat;

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

	/**
	 * @covers ValueRank
	 */
	public function testGetName() {
		$instance = new SRFValueRank(
			'valuerank'
		);

		$this->assertIsString( $instance->getName() );
	}

}
