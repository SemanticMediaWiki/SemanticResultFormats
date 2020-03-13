<?php

namespace SRF\Tests\Gantt;

use SMW\Test\QueryPrinterRegistryTestCase;

class GanttTest extends QueryPrinterRegistryTestCase{

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'gantt' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRF\Gantt\GanttPrinter';
	}
}