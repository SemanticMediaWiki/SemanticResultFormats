<?php

namespace SRF;
use SMWResultPrinter, SMWQueryResult, SMWDataItem, SMWDataValue, SMWOutputs, SRFUtils, FormatJson, Skin, Html, Title, File;

/**
 * HTML5 Audio / Video media query printer
 *
 * This printer integrates jPlayer which is a HTML5 Audio / Video
 * Javascript libray under GPL/MIT license.
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
 * @see http://www.semantic-mediawiki.org/wiki/Help:Media format
 *
 * @file
 * @ingroup SemanticResultFormats
 *
 * @since 1.9
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */

/**
 * This printer integrates jPlayer which is a HTML5 Audio / Video
 * Javascript libray under GPL/MIT license.
 *
 * @ingroup SemanticResultFormats
 */
class MediaPlayer extends SMWResultPrinter {

	/**
	 * @var valid mime types
	 */
	protected $validMimeTypes = array( 'mp3', 'mp4', 'webm', 'webma', 'webmv', 'ogg', 'oga', 'ogv', 'm4v', 'm4a' );

	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf-printername-media' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {

		// Data processing
		$data = $this->getResultData( $result, $outputMode );

		// Check if the data processing returned any results otherwise just bailout
		if ( $data === array() ) {
			if ( $this->params['default'] !== '' ) {
				return $this->params['default'];
			} else{
				$result->addErrors( array( wfMessage( 'srf-no-results' )->inContentLanguage()->text() ) );
				return '';
			}
		} else {
			// Return formatted results
			return $this->getFormatOutput( $data );
		}
	}

	/**
	 * Returns an array with data
	 *
	 * @since 1.9
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getResultData( SMWQueryResult $result, $outputMode ) {

		$data = array();

		/**
		 * Get all values for all rows that belong to the result set
		 * @var SMWResultArray $rows
		 */
		while ( $rows = $result->getNext() ) {
			$rowData = array();
			$mediaType = null;
			$mimeType = null;

			/**
			 * @var SMWResultArray $field
			 * @var SMWDataValue $dataValue
			 */
			foreach ( $rows as $field ) {

				// Label for the current property
				$propertyLabel = $field->getPrintRequest()->getLabel();

				// Label for the current subject
				$subjectLabel = $field->getResultSubject()->getTitle()->getFullText();

				if ( $propertyLabel === '' || $propertyLabel === '-' ){
					$propertyLabel = 'subject';
				} elseif ( $propertyLabel === 'poster' ){
					// Label "poster" is a special case where we set the media type to video in order
					// to use the same resources that can display video and cover art
					// $data['mediaTypes'][] = 'video';
				}

				// Check if the subject itself is a media source
				if ( $field->getResultSubject()->getTitle()->getNamespace() === NS_FILE && $mimeType === null ){
					list( $mediaType, $mimeType, $source ) = $this->getMediaSource( $field->getResultSubject()->getTitle() );
					$rowData[$mimeType] = $source;
				}

				while ( ( $dataValue = $field->getNextDataValue() ) !== false ) {
					// Get other data value item details
					$value = $this->getDataValueItem( $propertyLabel, $dataValue->getDataItem()->getDIType(), $dataValue, $mediaType, $mimeType );
					$rowData[$propertyLabel] = $value;
				}
			}

			// Only select relevant source data that match the validMimeTypes
			if ( $mimeType !== '' && in_array( $mimeType, $this->validMimeTypes ) ){
				$data['mimeTypes'][]  = $mimeType;
				$data['mediaTypes'][] = $mediaType;
				$data[$subjectLabel]  = $rowData;
			}
		}

		return $data;
	}

	/**
	 * Returns media source information
	 *
	 * @since 1.9
	 *
	 * @param Title $title
	 */
	private function getMediaSource( Title $title ){

		// Find the file source
		$source = wfFindFile ( $title );
		if ( $source ){
			// $source->getExtension() returns ogg even though it is a ogv/oga file
			// this doesn't help much therefore we do it ourselves
			if( in_array( $source->getExtension(), array( 'ogg', 'oga', 'ogv' ) ) ){
				$extension = strtolower( substr( $source->getName(), strrpos( $source->getName(), '.' ) + 1 ) );
				$params = array( $extension === 'ogv' ? 'video' : 'audio',  $extension, $source->getUrl() );
			} else {
				list( $major, $minor ) = File::splitMime( $source->getMimeType() );
				$params = array( $major, $source->getExtension(), $source->getUrl() );
			}
		} else {
			$params = array();
		}
		return $params;
	}

	/**
	 * Returns single data value item
	 *
	 * @since 1.9
	 *
	 * @param string $label
	 * @param integer $type
	 * @param SMWDataValue $dataValue
	 * @param string $mediaType
	 * @param string $mimeType
	 *
	 * @return mixed
	 */
	private function getDataValueItem( &$label, $type, SMWDataValue $dataValue, &$mediaType, &$mimeType ) {

		if ( $type == SMWDataItem::TYPE_WIKIPAGE && $dataValue->getTitle()->getNamespace() === NS_FILE ) {

			if ( $label === 'source' && $mimeType === null ) {

				// Identify the media source
				// and get media information
				list( $mediaType, $mimeType, $source ) = $this->getMediaSource( $dataValue->getTitle() );
				$label = $mimeType;
				return $source;
			} elseif ( $label === 'poster' ) {
				$mediaType = 'video';

				// Get the cover art image url
				$source = wfFindFile ( $dataValue->getTitle() );
				return $source->getUrl();
			}
		}
			return $dataValue->getWikiValue();
	}

	/**
	 * Prepare data for the output
	 *
	 * @since 1.9
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	protected function getFormatOutput( $data ) {

		static $statNr = 0;
		$ID = 'srf-media-' . ++$statNr;

		$this->isHTML = true;

		// SRF related JS globals
		SRFUtils::addGlobalJSVariables();

		// Get the media/mime types
		if ( in_array( 'video', $data['mediaTypes'] ) ){
			$mediaType = 'video';
		} else {
			$mediaType = 'audio';
		}
		unset( $data['mediaTypes'] );

		$mimeTypes = array_unique( $data['mimeTypes'] );
		unset( $data['mimeTypes'] );

		// Reassign output array
		$output = array(
			'data'  => $data,
			'count' => count( $data ),
			'mediaType' => $mediaType,
			'mimeTypes' => implode( ',', $mimeTypes ),
			'inspector' => $this->params['inspector']
		);

		$requireHeadItem = array ( $ID => FormatJson::encode( $output ) );
		SMWOutputs::requireHeadItem( $ID, Skin::makeVariablesScript( $requireHeadItem ) );

		SMWOutputs::requireResource( 'ext.jquery.jplayer.skin.' . $this->params['theme'] );

		SMWOutputs::requireResource( 'ext.srf.mediaplayer' );

		$processing = SRFUtils::htmlProcessingElement();

		return Html::rawElement(
			'div',
			array(
				'class' => $this->params['class'] !== '' ? 'srf-media ' . $this->params['class'] : 'srf-media'
			),
			$processing . Html::element(
				'div',
				array(
					'id' => $ID,
					'class' => 'container',
					'style' => 'display:none;'
				),
				null
			)
		);
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.9
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['class'] = array(
			'message' => 'srf-paramdesc-class',
			'default' => '',
		);

		$params['theme'] = array(
			'message' => 'srf-paramdesc-theme',
			'default' => 'blue.monday',
			'values' => array( 'blue.monday', 'morning.light' ),
		);

		$params['inspector'] = array(
			'type' => 'boolean',
			'message' => 'srf-paramdesc-mediainspector',
			'default' => false,
		);

		return $params;
	}
}