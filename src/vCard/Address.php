<?php

namespace SRF\vCard;

/**
 * Represents a single address entry in an vCard
 *
 * @see http://www.semantic-mediawiki.org/wiki/vCard
 *
 * @license GNU GPL v2+
 * @since 1.5
 *
 * @author Markus Krötzsch
 * @author Denny Vrandecic
 * @author Frank Dengler
 */
class Address {

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $adr = [];

	/**
	 * @param string $type
	 * @param array $adr
	 */
	public function __construct( $type, array $adr = [] ) {
		$this->type = $type;
		$this->adr = $adr;
	}

	/**
	 * @since 3.1
	 *
	 * @return boolean
	 */
	public function hasAddress() {
		return $this->adr !== [];
	}

	/**
	 * @since 3.1
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set( $key, $value ) {
		$this->adr[$key] = $value;
	}

	/**
	 * @return string
	 */
	public function text() {

		if ( $this->type == "" ) {
			$this->type = "WORK";
		}

		$adr = [];

		// Expected sequence as defined by
		// https://tools.ietf.org/html/rfc6350#section-6.3.1
		$map = [
			'pobox',
			'ext',
			'street',
			'locality',
			'region',
			'code',
			'country'
		];

		foreach ( $map as $k ) {
			$adr[] = isset( $this->adr[$k] ) ? vCard::escape( $this->adr[$k] ) : '';
		}

		return "ADR;TYPE=$this->type;CHARSET=UTF-8:" . implode( ';', $adr ) . "\r\n";
	}

}
