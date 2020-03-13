<?php

namespace SRF\Outline;

/**
 * Represents a single item, or page, in the outline - contains both the
 * SMWResultArray and an array of some of its values, for easier aggregation
 *
 * @license GNU GPL v2+
 * @since 3.1
 */
class OutlineItem {

	/**
	 * @var [type]
	 */
	public $row;

	/**
	 * @var []
	 */
	private $vals;

	/**
	 * @since 3.1
	 *
	 * @param $row
	 */
	public function __construct( $row ) {
		$this->row = $row;
		$this->vals = [];
	}

	/**
	 * @since 3.1
	 *
	 * @param $name
	 * @param $value
	 */
	public function addFieldValue( $key, $value ) {
		if ( array_key_exists( $key, $this->vals ) ) {
			$this->vals[$key][] = $value;
		} else {
			$this->vals[$key] = [ $value ];
		}
	}

	/**
	 * @since 3.1
	 *
	 * @param $row
	 */
	public function getFieldValues( $key ) {

		if ( array_key_exists( $key, $this->vals ) ) {
			return $this->vals[$key];
		}

		return [ wfMessage( 'srf_outline_novalue' )->text() ];
	}

}
