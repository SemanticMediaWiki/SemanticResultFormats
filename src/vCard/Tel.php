<?php

namespace SRF\vCard;

/**
 * Represents a single telephone entry in an vCard entry.
 *
 * @see http://www.semantic-mediawiki.org/wiki/vCard
 *
 * @license GPL-2.0-or-later
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
		// may be a vCard value list using ",", no escaping
		$this->type = $type;
		// escape to be sure
		$this->telnumber = vCard::escape( $telnumber );
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
