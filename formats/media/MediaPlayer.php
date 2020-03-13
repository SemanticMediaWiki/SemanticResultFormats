<?php

namespace SRF;

use File;
use FormatJson;
use Html;
use MediaWiki\MediaWikiServices;
use Skin;
use SMW\ResultPrinter;
use SMWDataItem;
use SMWDataValue;
use SMWOutputs;
use SMWQueryResult;
use SMWResultArray;
use SRFUtils;
use Title;

/**
 * HTML5 Audio / Video media query printer
 *
 * This printer integrates jPlayer which is a HTML5 Audio / Video
 * Javascript library under GPL/MIT license.
 *
 * @see http://www.semantic-mediawiki.org/wiki/Help:Media_format
 *
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 * @ingroup QueryPrinter
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */

/**
 * This printer integrates jPlayer which is a HTML5 Audio / Video
 * Javascript libray under GPL/MIT license.
 *
 * @ingroup SRF
 * @ingroup QueryPrinter
 */
class MediaPlayer extends ResultPrinter {

	/**
	 * Specifies valid mime types supported by jPlayer
	 *
	 * @var array
	 */
	protected $validMimeTypes = [ 'mp3', 'mp4', 'webm', 'webma', 'webmv', 'ogg', 'oga', 'ogv', 'm4v', 'm4a' ];

	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return $this->msg( 'srf-printername-media' )->text();
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
		if ( $data !== [] ) {
			// Return formatted results
			return $this->getFormatOutput( $data );
		}

		if ( $this->params[ 'default' ] !== '' ) {
			return $this->params[ 'default' ];
		}

		$result->addErrors( [ $this->msg( 'srf-no-results' )->inContentLanguage()->text() ] );
		return '';
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

		$data = [];

		/**
		 * Get all values for all rows that belong to the result set
		 *
		 * @var SMWResultArray $rows
		 */
		while ( $rows = $result->getNext() ) {
			$rowData = [];
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

				if ( $propertyLabel === '' || $propertyLabel === '-' ) {
					$propertyLabel = 'subject';
				} elseif ( $propertyLabel === 'poster' ) {
					// Label "poster" is a special case where we set the media type to video in order
					// to use the same resources that can display video and cover art
					// $data['mediaTypes'][] = 'video';
				}

				// Check if the subject itself is a media source
				if ( $field->getResultSubject()->getTitle()->getNamespace() === NS_FILE && $mimeType === null ) {
					list( $mediaType, $mimeType, $source ) = $this->getMediaSource(
						$field->getResultSubject()->getTitle()
					);
					$rowData[$mimeType] = $source;
				}

				while ( ( $dataValue = $field->getNextDataValue() ) !== false ) {
					// Get other data value item details
					$value = $this->getDataValueItem(
						$propertyLabel,
						$dataValue,
						$mediaType,
						$mimeType,
						$rowData
					);
					$rowData[$propertyLabel] = $value;
				}
			}

			// Only select relevant source data that match the validMimeTypes
			if ( $mimeType !== '' && in_array( $mimeType, $this->validMimeTypes ) ) {
				$data['mimeTypes'][] = $mimeType;
				$data['mediaTypes'][] = $mediaType;
				$data[$subjectLabel] = $rowData;
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
	 *
	 * @return string[]
	 *
	 */
	private function getMediaSource( Title $title ) {

		// Find the file source
		$source = $this->findFile( $title );

		if ( $source ) {
			// $source->getExtension() returns ogg even though it is a ogv/oga (same goes for m4p) file
			// this doesn't help much therefore we do it ourselves
			$extension = $source->getExtension();

			if ( in_array( $extension, [ 'ogg', 'oga', 'ogv' ] ) ) {
				$extension = strtolower( substr( $source->getName(), strrpos( $source->getName(), '.' ) + 1 ) );

				// Xiph.Org recommends that .ogg only be used for Ogg Vorbis audio files
				$extension = $extension === 'ogg' ? 'oga' : $extension;

				$params = [ $extension === 'ogv' ? 'video' : 'audio', $extension, $source->getUrl() ];
			} elseif ( in_array( $extension, [ 'm4v', 'm4a', 'm4p' ] ) ) {
				$params = [ $extension === 'm4v' ? 'video' : 'audio', $extension, $source->getUrl() ];
			} else {
				list( $major, $minor ) = File::splitMime( $source->getMimeType() );
				$params = [ $major, $extension, $source->getUrl() ];
			}
		} else {
			$params = [];
		}
		return $params;
	}

	/**
	 * Returns single data value item
	 *
	 * @param string $label
	 * @param SMWDataValue $dataValue
	 * @param string $mediaType
	 * @param string $mimeType
	 *
	 * @param $rowData
	 *
	 * @return mixed
	 * @since 1.9
	 *
	 */
	private function getDataValueItem( &$label, SMWDataValue $dataValue, &$mediaType, &$mimeType, &$rowData ) {

		$dataItem = $dataValue->getDataItem();
		$type = $dataItem->getDIType();

		if ( $type === SMWDataItem::TYPE_WIKIPAGE ) {

			$title = $dataItem->getTitle();

			if ( $title instanceof Title && $title->getNamespace() === NS_FILE ) {

				if ( $label === 'source' && $mimeType === null ) {

					// Identify the media source
					// and get media information
					list( $mediaType, $mimeType, $source ) = $this->getMediaSource( $title );
					$label = $mimeType;
					return $source;
				} elseif ( $label === 'poster' ) {
					$mediaType = 'video';

					// Get the cover art image url
					$source = $this->findFile( $title );

					return $source->getUrl();
				}
			}
		}

		if ( $type === SMWDataItem::TYPE_URI ) {

			$source = $dataItem->getURI();
			$mimeType = '';

			// Get file extension from the URI
			$extension = strtolower( substr( $source, strrpos( $source, '.' ) + 1 ) );

			// Xiph.Org recommends that .ogg only be used for Ogg Vorbis audio files
			if ( in_array( $extension, [ 'ogg', 'oga', 'ogv' ] ) ) {
				$mimeType = $extension === 'ogg' ? 'oga' : $extension;
				$mediaType = $extension === 'ogv' ? 'video' : 'audio';
			} elseif ( in_array( $extension, [ 'm4v', 'm4a', 'm4p' ] ) ) {
				$mimeType = $extension;
				$mediaType = $extension === 'm4v' ? 'video' : 'audio';
			} else {
				$mimeType = $extension;
				$mediaType = strpos( $extension, 'v' ) !== false ? 'video' : 'audio';
			}

			if ( $mimeType !== '' ) {
				$rowData[$mimeType] = $source;
			}

			return $source;
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

		$ID = 'srf-' . uniqid();
		$this->isHTML = true;

		// Get the media/mime types
		if ( in_array( 'video', $data['mediaTypes'] ) ) {
			$mediaType = 'video';
		} else {
			$mediaType = 'audio';
		}
		unset( $data['mediaTypes'] );

		$mimeTypes = array_unique( $data['mimeTypes'] );
		unset( $data['mimeTypes'] );

		// Reassign output array
		$output = [
			'data' => $data,
			'count' => count( $data ),
			'mediaType' => $mediaType,
			'mimeTypes' => implode( ',', $mimeTypes ),
			'inspector' => $this->params['inspector']
		];

		$requireHeadItem = [ $ID => FormatJson::encode( $output ) ];
		SMWOutputs::requireHeadItem( $ID, Skin::makeVariablesScript( $requireHeadItem, false ) );

		SMWOutputs::requireResource( 'ext.jquery.jplayer.skin.' . $this->params['theme'] );
		SMWOutputs::requireResource( 'ext.srf.formats.media' );

		$processing = SRFUtils::htmlProcessingElement();

		return Html::rawElement(
			'div',
			[
				'class' => $this->params['class'] !== '' ? 'srf-media ' . $this->params['class'] : 'srf-media'
			],
			$processing . Html::element(
				'div',
				[
					'id' => $ID,
					'class' => 'media-container',
					'style' => 'display:none;'
				],
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

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		$params['theme'] = [
			'message' => 'srf-paramdesc-theme',
			'default' => 'blue.monday',
			'values' => [ 'blue.monday', 'morning.light' ],
		];

		$params['inspector'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-mediainspector',
			'default' => false,
		];

		return $params;
	}

	/**
	 * @param Title $title
	 *
	 * @return bool|File
	 */
	private function findFile( Title $title ) {

		if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
			return MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
		}

		return wfFindFile( $title ); // TODO: Remove when min MW version is 1.34
	}
}
