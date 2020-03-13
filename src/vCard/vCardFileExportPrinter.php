<?php

namespace SRF\vCard;

use SMWExportPrinter as FileExportPrinter;
use SMWQuery as Query;
use SMWQueryProcessor as QueryProcessor;
use SMWQueryResult as QueryResult;
use SMWTimeValue as TimeValue;
use WikiPage;

/**
 * Printer class for creating vCard exports
 *
 * @see http://www.semantic-mediawiki.org/wiki/vCard
 * @see https://tools.ietf.org/html/rfc6350
 * @see https://www.w3.org/2002/12/cal/vcard-notes.html
 *
 * @license GNU GPL v2+
 * @since 1.5
 *
 * @author Markus Krötzsch
 * @author Denny Vrandecic
 * @author Frank Dengler
 * @author mwjames
 */
class vCardFileExportPrinter extends FileExportPrinter {

	/**
	 * @see FileExportPrinter::getName
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return wfMessage( 'srf_printername_vcard' )->text();
	}

	/**
	 * @see FileExportPrinter::getMimeType
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getMimeType( QueryResult $queryResult ) {
		return 'text/x-vcard';
	}

	/**
	 * @see FileExportPrinter::getFileName
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getFileName( QueryResult $queryResult ) {

		if ( $this->params['filename'] !== '' ) {

			if ( strpos( $this->params['filename'], '.vcf' ) === false ) {
				$this->params['filename'] .= '.vcf';
			}

			return str_replace( ' ', '_', $this->params['filename'] );
		} elseif ( $this->getSearchLabel( SMW_OUTPUT_WIKI ) != '' ) {
			return str_replace( ' ', '_', $this->getSearchLabel( SMW_OUTPUT_WIKI ) ) . '.vcf';
		}

		return 'vCard.vcf';
	}

	/**
	 * @see FileExportPrinter::getQueryMode
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getQueryMode( $context ) {
		return ( $context == QueryProcessor::SPECIAL_PAGE ) ? Query::MODE_INSTANCES : Query::MODE_NONE;
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['filename'] = [
			'message' => 'smw-paramdesc-filename',
			'default' => 'vCard.vcf',
		];

		return $params;
	}

	/**
	 * @see ResultPrinter::getResultText
	 */
	protected function getResultText( QueryResult $res, $outputMode ) {

		// Always return a link for when the output mode is not a file request,
		// a file request is normally only initiated when resolving the query
		// via Special:Ask
		if ( $outputMode !== SMW_OUTPUT_FILE ) {
			return $this->getVCardLink( $res, $outputMode );
		}

		return $this->getVCardContent( $res );
	}

	private function getVCardLink( QueryResult $res, $outputMode ) {

		// Can be viewed as HTML if requested, no more parsing needed
		$this->isHTML = $outputMode == SMW_OUTPUT_HTML;

		if ( $this->getSearchLabel( $outputMode ) ) {
			$label = $this->getSearchLabel( $outputMode );
		} else {
			$label = wfMessage( 'srf_vcard_link' )->inContentLanguage()->text();
		}

		$link = $res->getQueryLink( $label );
		$link->setParameter( 'vcard', 'format' );

		if ( $this->getSearchLabel( SMW_OUTPUT_WIKI ) != '' ) {
			$link->setParameter( $this->getSearchLabel( SMW_OUTPUT_WIKI ), 'searchlabel' );
		}

		if ( array_key_exists( 'limit', $this->params ) ) {
			$link->setParameter( $this->params['limit'], 'limit' );
		} else { // use a reasonable default limit
			$link->setParameter( 20, 'limit' );
		}

		return $link->getText( $outputMode, $this->mLinker );
	}

	/**
	 * @param QueryResult $res
	 *
	 * @return string
	 * @throws \MWException
	 */
	private function getVCardContent( $res ) {

		$result = '';
		$vCards = [];

		$row = $res->getNext();
		$isPublic = $this->isPublic();

		while ( $row !== false ) {
			// Subject of the Result
			$subject = $row[0]->getResultSubject();
			$title = $subject->getTitle();

			// Specifies a value that represents a persistent, globally unique
			// identifier associated with the object.
			$uri = $title->getFullURL();

			// A timestamp for the last time the vCard was updated
			$timestamp = WikiPage::factory( $title )->getTimestamp();
			$text = $title->getText();

			$vCards[] = $this->newVCard( $row, $uri, $text, $timestamp, $isPublic );
			$row = $res->getNext();
		}

		foreach ( $vCards as $vCard ) {
			$result .= $vCard->text();
		}

		return $result;
	}

	private function newVCard( $row, $uri, $text, $timestamp, $isPublic ) {

		$vCard = new vCard(
			$uri,
			$text,
			[

				// something like 'Dr.'
				'prefix' => '',

				// given name
				'firstname' => '',

				// family name
				'lastname' => '',

				// typically the "middle" name (second first name)
				'additionalname' => '',

				// things like "jun." or "sen."
				'suffix' => '',

				// the "formatted name", may be independent from
				// first/lastname & co.
				'fullname' => '',

				'tel' => [],
				'address' => [],
				'email' => [],
				// a date
				'birthday' => '',

				// organisational details
				'organization' => '',
				'department' => '',
				'title' => '',
				'role' => '',
				'category' => '',

				 // homepage, a legal URL
				'url' => '',

				// any text
				'note' => ''
			]
		);

		$tels = [];
		$emails = [];

		$addresses['work'] = new Address( 'WORK' );
		$addresses['home'] = new Address( 'HOME' );

		foreach ( $row as $field ) {
			$this->mapField( $field, $vCard, $tels, $addresses, $emails );
		}

		$vCard->set( 'tel', $tels );
		$vCard->set( 'address', $addresses );
		$vCard->set( 'email', $emails );

		$vCard->isPublic( $isPublic );
		$vCard->setTimestamp( $timestamp );

		return $vCard;
	}

	private function isPublic() {
		// heuristic for setting confidentiality level of vCard:
		global $wgGroupPermissions;

		if ( ( array_key_exists( '*', $wgGroupPermissions ) ) && ( array_key_exists(
				'read',
				$wgGroupPermissions['*']
			) ) ) {
			return $wgGroupPermissions['*']['read'];
		}

		return true;
	}

	private function mapField( $field, $vCard, &$tels, &$addresses, &$emails ) {

		$printRequest = $field->getPrintRequest();

		switch ( strtolower( $printRequest->getLabel() ) ) {
			case "name":
				$vCard->set( 'fullname', $this->getFieldValue( $field ) );
				break;
			case "prefix":
				$vCard->set( 'prefix', $this->getFieldCommaList( $field ) );
				break;
			case "suffix":
				$vCard->set( 'suffix', $this->getFieldCommaList( $field ) );
				break;
			case "firstname":
				// save only the first
				$vCard->set( 'firstname', $this->getFieldValue( $field ) );
				break;
			case "additionalname":
			case "extraname":
				$vCard->set( 'additionalname', $this->getFieldCommaList( $field ) );
				break;
			case "lastname":
				// save only the first
				$vCard->set( 'lastname', $this->getFieldValue( $field ) );
				break;
			case "note":
				$vCard->set( 'note', $this->getFieldCommaList( $field ) );
				break;
			case "category":
				$vCard->set( 'category', $this->getFieldCommaList( $field ) );
			case "email":
				while ( $value = $field->getNextDataValue() ) {
					$emails[] = new Email( 'INTERNET', $value->getShortWikiText() );
				}
				break;
			case "workphone":
				while ( $value = $field->getNextDataValue() ) {
					$tels[] = new Tel( 'WORK', $value->getShortWikiText() );
				}
				break;
			case "cellphone":
				while ( $value = $field->getNextDataValue() ) {
					$tels[] = new Tel( 'CELL', $value->getShortWikiText() );
				}
				break;
			case "homephone":
				while ( $value = $field->getNextDataValue() ) {
					$tels[] = new Tel( 'HOME', $value->getShortWikiText() );
				}
				break;
			case "organization":
				$vCard->set( 'organization', $this->getFieldValue( $field ) );
				break;
			case "workpostofficebox":
				if ( ( $workpostofficebox = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['work']->set( 'pobox', $workpostofficebox );
				}
				break;
			case "workextendedaddress":
				if ( ( $workextendedaddress = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['work']->set( 'ext', $workextendedaddress );
				}
				break;
			case "workstreet":
				if ( ( $workstreet = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['work']->set( 'street', $workstreet );
				}
				break;
			case "worklocality":
				if ( ( $worklocality = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['work']->set( 'locality', $worklocality );
				}
				break;
			case "workregion":
				if ( ( $workregion = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['work']->set( 'region', $workregion );
				}
				break;
			case "workpostalcode":
				if ( ( $workpostalcode = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['work']->set( 'code', $workpostalcode );
				}
				break;
			case "workcountry":
				if ( ( $workcountry = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['work']->set( 'country', $workcountry );
				}
				break;
			case "homepostofficebox":
				if ( ( $homepostofficebox = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['home']->set( 'pobox', $homepostofficebox );
				}
				break;
			case "homeextendedaddress":
				if ( ( $homeextendedaddress = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['home']->set( 'ext', $homeextendedaddress );
				}
				break;
			case "homestreet":
				if ( ( $homestreet = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['home']->set( 'street', $homestreet );
				}
				break;
			case "homelocality":
				if ( ( $homelocality = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['home']->set( 'locality', $homelocality );
				}
				break;
			case "homeregion":
				if ( ( $homeregion = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['home']->set( 'region', $homeregion );
				}
				break;
			case "homepostalcode":
				if ( ( $homepostalcode = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['home']->set( 'code', $homepostalcode );
				}
				break;
			case "homecountry":
				if ( ( $homecountry = $this->getFieldValue( $field ) ) !== '' ) {
					$addresses['home']->set( 'country', $homecountry );
				}
				break;
			case "birthday":
				if ( $printRequest->getTypeID() == TimeValue::TYPE_ID ) {
					$value = $field->getNextDataValue();

					if ( $value !== false ) {
						$birthday = $value->getISO8601Date();
						$vCard->set( 'birthday', $birthday );
					}
				}
				break;
			case "homepage":
				if ( $printRequest->getTypeID() == "_uri" ) {
					$value = $field->getNextDataValue();

					if ( $value !== false ) {
						$url = $value->getWikiValue();
						$vCard->set( 'url', $url );
					}
				}
				break;
		}
	}

	private function getFieldCommaList( $field ) {

		$list = '';

		while ( $value = $field->getNextDataValue() ) {
			$list .= ( $list ? ',' : '' ) . $value->getShortWikiText();
		}

		return $list;
	}

	private function getFieldValue( $field ) {

		$v = '';

		if ( ( $value = $field->getNextDataValue() ) !== false ) {
			$v = $value->getShortWikiText();
		}

		return $v;
	}

}
