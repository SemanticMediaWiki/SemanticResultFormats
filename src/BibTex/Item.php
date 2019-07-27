<?php

namespace SRF\BibTex;

use SMWDataValue as DataValue;

/**
 * @see http://www.semantic-mediawiki.org/wiki/BibTex
 *
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class Item {

	/**
	 * @see https://en.wikipedia.org/wiki/BibTeX
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * @var []
	 */
	protected $fields = [
		'address' => '',
		'annote' => '',
		'author' => [],
		'booktitle' => '',
		'chapter' => '',
		'crossref' => '',
		'doi' => '',
		'edition' => '',
		'editor' => [],
		'eprint' => '',
		'howpublished' => '',
		'institution' => '',
		'journal' => '',
		'key' => '',
		'month' => '',
		'note' => '',
		'number' => '',
		'organization' => '',
		'pages' => '',
		'publisher' => '',
		'school' => '',
		'series' => '',
		'title' => '',
		'url' => '',
		'volume' => '',
		'year' => ''
	];

	/**
	 * @var callable
	 */
	private $formatterCallback;

	/**
	 * @since 3.1
	 */
	public function __construct() {
		$this->type = 'Book';
	}

	/**
	 * @since 3.1
	 *
	 * @param callable $compoundLabelCallback
	 */
	public function setFormatterCallback( callable $formatterCallback ) {
		$this->formatterCallback = $formatterCallback;
	}

	/**
	 * @since 3.1
	 *
	 * @param $key
	 * @param string $text
	 *
	 * @return string
	 */
	public function replace( $key, $text ) {

		if ( $key === 'uri' ) {
			$text = str_replace(
				[ "Ä", "ä", "Ö", "ö", "Ü", "ü", "ß" ],
				[ 'Ae', 'ae', 'Oe', 'oe', 'Ue', 'ue', 'ss' ],
				$text
			);
			$text = preg_replace("/[^a-zA-Z0-9]+/", "", $text );
		}

		return $text;
	}

	/**
	 * @since 3.1
	 *
	 * @param $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {

		$key = strtolower( $key );

		if ( $key === 'type' ) {
			$this->type = ucfirst( $value );
		}

		if ( isset( $this->fields[$key] ) ) {
			$this->fields[$key] = $value;
		}
	}

	/**
	 * @since 3.1
	 *
	 * @return string
	 */
	public function text() {

		$formatterCallback = $this->formatterCallback;

		$text = '@' . $this->type . '{' . $this->buildURI() . ",\r\n";

		foreach ( $this->fields as $key => $value ) {

			if ( ( $key === 'author' || $key === 'editor' ) && is_array( $value ) ) {
				if ( is_callable( $formatterCallback ) ) {
					$value = $formatterCallback( $key, $value );
				} else {
					$value = implode( ', ', $value );
				}
			}

			if ( $value === '' ) {
				continue;
			}

			$text .= '  ' . $key . ' = "' . $value . '", ' . "\r\n";
		}

		$text .= "}";

		return $text;
	}

	/**
	 * Consist of `author last name` + `year` + `first word of title`
	 *
	 * @return string
	 */
	protected function buildURI() {

		$uri = '';

		if ( isset( $this->fields['author'] ) ) {
			foreach ( $this->fields['author'] as $key => $author ) {
				$elements = explode( ' ', $author );
				$uri .= array_pop( $elements );
				break;
			}
		}

		if ( isset( $this->fields['year'] ) ) {
			$uri .= $this->fields['year'];
		}

		if ( isset( $this->fields['title'] ) ) {
			foreach ( explode( ' ', $this->fields['title'] ) as $titleWord ) {
				$charsTitleWord = preg_split( '//', $titleWord, -1, PREG_SPLIT_NO_EMPTY );

				if ( !empty( $charsTitleWord ) ) {
					$uri .= $charsTitleWord[0];
				}
			}
		}

		return strtolower( $this->replace( 'uri', $uri ) );
	}

}
