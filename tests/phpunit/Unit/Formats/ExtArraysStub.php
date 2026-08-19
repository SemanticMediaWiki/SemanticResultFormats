<?php

/**
 * Minimal stand-in for the real Arrays extension's global ExtArrays class,
 * matching its actual API (get(), instance createArray()). Loaded only when
 * the real extension isn't installed, so tests exercise the same global,
 * unqualified class reference that production code resolves against.
 */
class ExtArrays {

	private static $instance;

	public $created = [];

	public static function &get( $parser ) {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function createArray( $arrayId, array $array = [] ) {
		$this->created[$arrayId] = $array;
	}

}
