<?php

namespace SRF\vCard;

use Article;
use Title;

/**
 * Represents a single entry in an vCard
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
class vCard {

	/**
	 * @var string
	 */
	private $uri;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @var array
	 */
	private $vcard = [];

	/**
	 * @var boolean
	 */
	private $isPublic = true;

	/**
	 * @var integer
	 */
	private $timestamp;

	/**
	 * @since 3.0
	 *
	 * @param string $uri
	 * @param string $text
	 * @param array $vcard
	 */
	public function __construct( $uri, $text, array $vcard ) {
		$this->uri = $uri;
		$this->text = $text;

		$default = [
			'prefix' => '',
			'firstname' => '',
			'lastname' => '',
			'additionalname' => '',
			'suffix' => '',
			'fullname' => '',
			'tel' => [],
			'address' => [],
			'email' => [],
			'birthday' => '',
			'title' => '',
			'role' => '',
			'organization' => '',
			'department' => '',
			'category' => '',
			'url' => '',
			'note' => ''
		];

		$this->vcard = $vcard + $default;
	}

	/**
	 * @since 3.1
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set( $key, $value ) {
		$this->vcard[$key] = $value;
	}

	/**
	 * @since 3.0
	 *
	 * @param boolean $isPublic
	 */
	public function isPublic( $isPublic ) {
		$this->isPublic = $isPublic;
	}

	/**
	 * @since 3.0
	 *
	 * @param integer $timestamp
	 */
	public function setTimestamp( $timestamp ) {
		$this->timestamp = $timestamp;
	}

	/**
	 * Creates the vCard output for a single item.
	 *
	 * @return string
	 */
	public function text() {

		$vcard = $this->prepareCard( $this->vcard );

		$text = "BEGIN:VCARD\r\n";
		$text .= "VERSION:3.0\r\n";

		// N and FN are required properties in vCard 3.0, we need to write something there
		$text .= "N;CHARSET=UTF-8:" .
			$vcard['lastname'] . ';' .
			$vcard['firstname'] . ';' .
			$vcard['additionalname'] . ';' .
			$vcard['prefix'] . ';' .
			$vcard['suffix'] . "\r\n";

		$text .= "FN;CHARSET=UTF-8:" .
			$vcard['label'] . "\r\n";

		$text .= ( $this->isPublic ? 'CLASS:PUBLIC' : 'CLASS:CONFIDENTIAL' ) . "\r\n";

		if ( $vcard['birthday'] !== "" ) {
			$text .= "BDAY:" . $vcard['birthday'] . "\r\n";
		}

		if ( $vcard['title'] !== "" ) {
			$text .= "TITLE;CHARSET=UTF-8:" . $vcard['title'] . "\r\n";
		}

		if ( $vcard['role'] !== "" ) {
			$text .= "ROLE;CHARSET=UTF-8:" . $vcard['role'] . "\r\n";
		}

		if ( $vcard['organization'] !== "" ) {
			$text .= "ORG;CHARSET=UTF-8:" . $vcard['organization'] . ';' . $vcard['department'] . "\r\n";
		}

		if ( $vcard['category'] !== "" ) {
			$text .= "CATEGORIES;CHARSET=UTF-8:" . $vcard['category'] . "\r\n";
		}

		foreach ( $vcard['email'] as $e ) {
			$text .= $e->text();
		}

		foreach ( $vcard['address'] as $a ) {
			if ( $a->hasAddress() ) {
				$text .= $a->text();
			}
		}

		foreach ( $vcard['tel'] as $t ) {
			$text .= $t->text();
		}

		if ( $vcard['note'] !== "" ) {
			$text .= "NOTE;CHARSET=UTF-8:" . $vcard['note'] . "\r\n";
		}

		$text .= "SOURCE;CHARSET=UTF-8:$this->uri\r\n";

		// The identifier for the product that created the vCard object
		$text .= "PRODID:-////Semantic MediaWiki\r\n";

		// A timestamp for the last time the vCard was updated
		$text .= "REV:$this->timestamp\r\n";

		// A URL pointing to a website that represents the person in some way
		$text .= "URL:" . ( $vcard['url'] !== "" ? $vcard['url'] : $this->uri ) . "\r\n";

		// Specifies a value that represents a persistent, globally unique
		// identifier associated with the object.
		$text .= "UID:$this->uri\r\n";
		$text .= "END:VCARD\r\n";

		return $text;
	}

	public static function escape( $text ) {
		return str_replace( [ '\\', ',', ':', ';' ], [ '\\\\', '\,', '\:', '\;' ], $text );
	}

	private function prepareCard( $vcard ) {

		$vcard['label'] = '';

		$additionalname = $vcard['additionalname'];

		// Read fullname or guess it in a simple way from other names that are
		// given
		if ( $vcard['fullname'] != '' ) {
			$vcard['label'] = $vcard['fullname'];
		} elseif ( $vcard['firstname'] . $vcard['lastname'] != '' ) {
			$vcard['label'] = $vcard['firstname'] . ( ( ( $vcard['firstname'] != '' ) && ( $vcard['lastname'] != '' ) ) ? ' ' : '' ) . $vcard['lastname'];
		} else {
			$vcard['label'] = $this->text;
		}

		$vcard['label'] = self::escape( $vcard['label'] );

		// read firstname and lastname, or guess it from other names that are given
		if ( $vcard['firstname'] . $vcard['lastname'] == '' ) { // guessing needed
			$nameparts = explode( ' ', $vcard['label'] );
			// Accepted forms for guessing:
			// "Lastname"
			// "Firstname Lastname"
			// "Firstname <Additionalnames> Lastname"
			$vcard['lastname'] = self::escape( array_pop( $nameparts ) );

			if ( count( $nameparts ) > 0 ) {
				$vcard['firstname'] = self::escape( array_shift( $nameparts ) );
			}

			foreach ( $nameparts as $name ) {
				$vcard['additionalname'] .= ( $vcard['additionalname'] != '' ? ',' : '' ) . self::escape( $name );
			}
		} else {
			$vcard['firstname'] = self::escape( $vcard['firstname'] );
			$vcard['lastname'] = self::escape( $vcard['lastname'] );
		}

		// no escape, can be a value list
		if ( $additionalname != '' ) {
			$vcard['additionalname'] = $additionalname;
		}

		$vcard['prefix'] = self::escape( $vcard['prefix'] );
		$vcard['suffix'] = self::escape( $vcard['suffix'] );
		$vcard['title'] = self::escape( $vcard['title'] );
		$vcard['role'] = self::escape( $vcard['role'] );
		$vcard['organization'] = self::escape( $vcard['organization'] );
		$vcard['department'] = self::escape( $vcard['department'] );
		$vcard['note'] = self::escape( $vcard['note'] );

		return $vcard;
	}

}
