<?php

namespace SRF\vCard;

/**
 * Represents a single telephone entry in an vCard entry.
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
class Tel {

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $telnumber;

	/**
	 * @param string $type
	 * @param string $telnumber
	 */
	public function __construct( $type, $telnumber ) {
		$this->type = $type;  // may be a vCard value list using ",", no escaping
		$this->telnumber = vCard::escape( $telnumber ); // escape to be sure
	}

	/**
	 * Creates the vCard output for a single telephone item.
	 */
	public function text() {

		if ( $this->type == "" ) {
			$this->type = "WORK";
		}

		return "TEL;TYPE=$this->type:$this->telnumber\r\n";
	}

}

