<?php

namespace SRF\Tests\Prolog;

use SMW\Tests\QueryPrinterRegistryTestCase;

class PrologTest extends QueryPrinterRegistryTestCase {

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'prolog' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRF\Prolog\PrologPrinter';
	}
}
