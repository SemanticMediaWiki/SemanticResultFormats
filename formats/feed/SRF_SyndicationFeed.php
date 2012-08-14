<?php

/**
 * Result printer to export results as RSS/Atom feed
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 1.8
 *
 * @file SRF_SyndicationFeed.php
 * @ingroup SMWResultPrinter
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */
class SRFSyndicationFeed extends SMWExportPrinter {

	/**
	 * Returns human readable label for this printer
	 *
	 * @return string
	 */
	public function getName() {
		return wfMsg( 'srf-printername-feed' );
	}

	/**
	 * @see SMWIExportPrinter::getMimeType
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $queryResult
	 *
	 * @return string
	 */
	public function getMimeType( SMWQueryResult $queryResult ) {
		return $this->params['type'] === 'atom' ? 'application/atom+xml' : 'application/rss+xml';
	}

	/**
	 * @see SMWIExportPrinter::outputAsFile
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $queryResult
	 * @param array $params
	 */
	public function outputAsFile( SMWQueryResult $queryResult, array $params ) {
		return $this->getResult( $queryResult, $params, SMW_OUTPUT_FILE );
	}

	/**
	 * File exports use MODE_INSTANCES on special pages (so that instances are
	 * retrieved for the export) and MODE_NONE otherwise (displaying just a download link).
	 *
	 * @param $context
	 *
	 * @return integer
	 */
	public function getQueryMode( $context ) {
		return ( $context == SMWQueryProcessor::SPECIAL_PAGE ) ? SMWQuery::MODE_INSTANCES : SMWQuery::MODE_NONE;
	}

	/**
	 * Returns a filename that is to be sent to the caller
	 *
	 * @param SMWQueryResult $res
	 * @param $outputmode integer
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {

		if ( $outputmode == SMW_OUTPUT_FILE ) {
			if ( $res->getCount() == 0 ){
				return $results->addErrors( array( wfMsgForContent( 'smw_result_noresults' ) ) );;
			}
			$result = $this->getFeed( $res, $this->params['type'] );
		} else {
			// Points to the Feed link
			$result = $this->getLink( $res, $outputmode )->getText( $outputmode, $this->mLinker );

			$this->isHTML = $outputmode == SMW_OUTPUT_HTML;
		}
		return $result;
	}

	/**
	 * Build a feed
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult  $res
	 * @param $type
	 *
	 * @return string
	 */
	protected function getFeed( SMWQueryResult $res, $type ){
		global $wgFeedClasses;

		if( !isset( $wgFeedClasses[$type] ) ) {
			return $results->addErrors( array( wfMsgForContent( 'feed-invalid' ) ) );
		}

		// Init feed class
		$feed = new $wgFeedClasses[$type](
			$this->feedTitle(),
			$this->feedDescription(),
			$this->feedURL()
		);

		// Create feed header
		$feed->outHeader();

		// Create feed items
		while ( $row = $res->getNext() ) {
			$feed->outItem( $this->feedItem( $row ) );
		}

		// Create feed footer
		$feed->outFooter();

		return $feed;
	}

	/**
	 * Feed title
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	protected function feedTitle() {
		return $this->params['title'] !== '' ? $this->params['title'] : "{$GLOBALS['wgSitename']} - {$this->getName()} [{$GLOBALS['wgLanguageCode']}]";
	}

	/**
	 * Feed description
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	protected function feedDescription() {
		return $this->params['description'] !== '' ? wfMsg( 'smw_rss_description', $this->params['description'] ) : wfMsg( 'tagline' );
	}

	/**
	 * Feed URL
	 *
	 * @since 1.8
	 *
	 * @return uri
	 */
	protected function feedURL() {
		return $GLOBALS['wgTitle']->getFullUrl();
	}

	/**
	 * Feed item
	 *
	 * @since 1.8
	 *
	 * @parameter array $row
	 *
	 * @return array
	 */
	protected function feedItem( array $row ) {
		$rowItems = array();

		// Loop over properties
		foreach ( $row as /* SMWResultArray */ $field ) {
			$itemSegments = array();

				$subject = $field->getResultSubject()->getTitle();

				// Loop over all values for the property.
				while ( ( /* SMWDataValue */ $object = $field->getNextDataValue() ) !== false ) {
					$itemSegments[] = Sanitizer::decodeCharReferences( $object->getWikiValue() );
				}

				// Join all values into a single string, separating them with comma's.
				$rowItems[] = implode( ',', $itemSegments );
		}

		$wikiPage = WikiPage::newFromID ( $subject->getArticleID() );

		if ( $wikiPage->exists() ){
			return new FeedItem(
				$subject->getPrefixedText(),
				$this->feedItemDescription( $rowItems ),
				$subject->getFullURL(),
				$wikiPage->getTimestamp(),
				$wikiPage->getUserText(),
				$this->feedItemComments()
			);
		}
	}

	/**
	 * Feed item description and property value output manipulation
	 *
	 * @since 1.8
	 *
	 * @parameter array $items
	 *
	 * @return string
	 */
	protected function feedItemDescription( $items ) {
		return htmlspecialchars( FeedItem::stripComment( implode( ',', $items ) ) );
	}

	/**
	 * According to MW documentation, the comment field is only implemented for RSS
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	protected function feedItemComments( ) {
		return '';
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['type'] = array(
			'type' => 'string',
			'default' => 'rss',
			'message' => 'srf-paramdesc-feedtype',
			'values' => array( 'rss', 'atom' ),
		);

		$params['title'] = array(
			'message' => 'srf-paramdesc-feedtitle',
			'default' => '',
		);

		$params['description'] = array(
			'message' => 'srf-paramdesc-feeddescription',
			'default' => '',
		);

		return $params;
	}
}