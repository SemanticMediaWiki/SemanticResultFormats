<?php

namespace SRF\vCard;

/**
 * Represents a single email entry in an vCard entry.
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
class Email {

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $emailaddress;

	/**
	 * @param string $type
	 * @param string $emailaddress
	 */
	public function __construct( $type, $emailaddress ) {
		$this->type = $type;
		// no escape, normally not needed anyway
		$this->emailaddress = $emailaddress;
	}

	/**
	 * Creates the vCard output for a single email item.
	 */
	public function text() {
		if ( $this->type == "" ) {
			$this->type = "INTERNET";
		}

		return "EMAIL;TYPE=$this->type:$this->emailaddress\r\n";
	}

}
