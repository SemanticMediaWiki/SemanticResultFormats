<?php

namespace SRF\Tests\Mermaid;

use SMW\Test\QueryPrinterRegistryTestCase;

class MermaidTest extends QueryPrinterRegistryTestCase{

	/**
	 * @see QueryPrinterRegistryTestCase::getFormats
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function getFormats() {
		return [ 'mermaid' ];
	}

	/**
	 * @see QueryPrinterRegistryTestCase::getClass
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SRF\Mermaid\Mermaid';
	}
}