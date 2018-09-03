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

		if ( $this->getSearchLabel( SMW_OUTPUT_WIKI ) != '' ) {
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
	 * @see ResultPrinter::getResultText
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {

		$result = '';

		if ( $outputmode == SMW_OUTPUT_FILE ) { // make vCard file
			$result = $this->getVCardContent( $res );
		} else { // just make link to vcard

			if ( $this->getSearchLabel( $outputmode ) ) {
				$label = $this->getSearchLabel( $outputmode );
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

			$result .= $link->getText( $outputmode, $this->mLinker );

			// yes, our code can be viewed as HTML if requested, no more parsing needed
			$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML );
		}

		return $result;
	}

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

		// name
		$prefix = ''; // something like 'Dr.'
		$firstname = ''; // given name
		$additionalname = ''; // typically the "middle" name (second first name)
		$lastname = ''; // family name
		$suffix = ''; // things like "jun." or "sen."
		$fullname = ''; // the "formatted name", may be independent from first/lastname & co.
		// contacts
		$emails = [];
		$tels = [];
		$addresses = [];
		// organisational details:
		$organization = ''; // any string
		$jobtitle = '';
		$role = '';
		$department = '';
		// other stuff
		$category = '';
		$birthday = ''; // a date
		$url = ''; // homepage, a legal URL
		$note = ''; // any text
		$workaddress = false;
		$homeaddress = false;

		$workpostofficebox = '';
		$workextendedaddress = '';
		$workstreet = '';
		$worklocality = '';
		$workregion = '';
		$workpostalcode = '';
		$workcountry = '';

		$homepostofficebox = '';
		$homeextendedaddress = '';
		$homestreet = '';
		$homelocality = '';
		$homeregion = '';
		$homepostalcode = '';
		$homecountry = '';

		foreach ( $row as $field ) {
			// later we may add more things like a generic
			// mechanism to add non-standard vCard properties as well
			// (could include funny things like geo, description etc.)
			$req = $field->getPrintRequest();

			switch ( strtolower( $req->getLabel() ) ) {
				case "name":
					$fullname = $this->getFieldValue( $field );
					break;
				case "prefix":
					$prefix = $this->getFieldCommaList( $field );
					break;
				case "suffix":
					$suffix = $this->getFieldCommaList( $field );
					break;
				case "firstname":
					// save only the first
					$firstname = $this->getFieldValue( $field );
					break;
				case "extraname":
					$additionalname = $this->getFieldCommaList( $field );
					break;
				case "lastname":
					// save only the first
					$lastname = $this->getFieldValue( $field );
					break;
				case "note":
					$note = $this->getFieldCommaList( $field );
					break;
				case "category":
					$category = $this->getFieldCommaList( $field );
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
					$organization = $this->getFieldValue( $field );
					break;
				case "workpostofficebox":
					if ( ( $workpostofficebox = $this->getFieldValue( $field ) ) !== '' ) {
						$workaddress = true;
					}
					break;
				case "workextendedaddress":
					if ( ( $workextendedaddress = $this->getFieldValue( $field ) ) !== '' ) {
						$workaddress = true;
					}
					break;
				case "workstreet":
					if ( ( $workstreet = $this->getFieldValue( $field ) ) !== '' ) {
						$workaddress = true;
					}
					break;
				case "worklocality":
					if ( ( $worklocality = $this->getFieldValue( $field ) ) !== '' ) {
						$workaddress = true;
					}
					break;
				case "workregion":
					if ( ( $workregion = $this->getFieldValue( $field ) ) !== '' ) {
						$workaddress = true;
					}
					break;
				case "workpostalcode":
					if ( ( $workpostalcode = $this->getFieldValue( $field ) ) !== '' ) {
						$workaddress = true;
					}
					break;
				case "workcountry":
					if ( ( $workcountry = $this->getFieldValue( $field ) ) !== '' ) {
						$workaddress = true;
					}
					break;
				case "homepostofficebox":
					if ( ( $homepostofficebox = $this->getFieldValue( $field ) ) !== '' ) {
						$homeaddress = true;
					}
					break;
				case "homeextendedaddress":
					if ( ( $homeextendedaddress = $this->getFieldValue( $field ) ) !== '' ) {
						$homeaddress = true;
					}
					break;
				case "homestreet":
					if ( ( $homestreet = $this->getFieldValue( $field ) ) !== '' ) {
						$homeaddress = true;
					}
					break;
				case "homelocality":
					if ( ( $homelocality = $this->getFieldValue( $field ) ) !== '' ) {
						$homeaddress = true;
					}
					break;
				case "homeregion":
					if ( ( $homeregion = $this->getFieldValue( $field ) ) !== '' ) {
						$homeaddress = true;
					}
					break;
				case "homepostalcode":
					if ( ( $homepostalcode = $this->getFieldValue( $field ) ) !== '' ) {
						$homeaddress = true;
					}
					break;
				case "homecountry":
					if ( ( $homecountry = $this->getFieldValue( $field ) ) !== '' ) {
						$homeaddress = true;
					}
					break;
				case "birthday":
					if ( $req->getTypeID() == TimeValue::TYPE_ID ) {
						$value = $field->getNextDataValue();
						if ( $value !== false ) {
							$birthday = $value->getISO8601Date();
						}
					}
					break;
				case "homepage":
					if ( $req->getTypeID() == "_uri" ) {
						$value = $field->getNextDataValue();
						if ( $value !== false ) {
							$url = $value->getWikiValue();
						}
					}
					break;
			}
		}

		if ( $workaddress ) {
			$addresses[] = new Address (
				'WORK',
				[
					'pobox' => $workpostofficebox,
					'ext' => $workextendedaddress,
					'street' => $workstreet,
					'locality' => $worklocality,
					'region' => $workregion,
					'code' => $workpostalcode,
					'country' => $workcountry
				]
			);
		}

		if ( $homeaddress ) {
			$addresses[] = new Address (
				'HOME',
				[
					'pobox' => $homepostofficebox,
					'ext' => $homeextendedaddress,
					'street' => $homestreet,
					'locality' => $homelocality,
					'region' => $homeregion,
					'code' => $homepostalcode,
					'country' => $homecountry
				]
			);
		}

		$vCard = new vCard(
			$uri,
			$text,
			[
				'prefix' => $prefix,
				'firstname' => $firstname,
				'lastname' => $lastname,
				'additionalname' => $additionalname,
				'suffix' => $suffix,
				'fullname' => $fullname,
				'tel' => $tels,
				'address' => $addresses,
				'email' => $emails,
				'birthday' => $birthday,
				'title' => $jobtitle,
				'role' => $role,
				'organization' => $organization,
				'department' => $department,
				'category' => $category,
				'url' => $url,
				'note' => $note
			]
		);

		$vCard->isPublic( $isPublic );
		$vCard->setTimestamp( $timestamp );

		return $vCard;
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

}
