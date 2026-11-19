<?php

namespace SRF;

use File;
use FormatJson;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use SMW\Query\QueryResult;
use SMW\Query\Result\ResultArray;
use SMW\Query\ResultPrinters\ResultPrinter;
use SMWDataItem;
use SMWOutputs;

/**
 * HTML5 Audio / Video media query printer
 *
 * Renders the results with the native HTML5 <audio> / <video> elements. A
 * playlist (previous/next, shuffle, repeat) is added by a small dependency free
 * JavaScript module when more than one media file is returned. Without
 * JavaScript the first item is still playable with the native controls and
 * every item is available as a plain link.
 *
 * This printer used to integrate jPlayer, which is not maintained anymore and
 * does not work with recent versions of MediaWiki.
 *
 * The printout labels understood by this printer are "title", "artist",
 * "poster" and "source". The artist(s) are appended in round brackets to the
 * title, e.g. "Green Grow the Rushes O (Co-cheòl)".
 *
 * @see http://www.semantic-mediawiki.org/wiki/Help:Media_format
 *
 * @since 1.9
 *
 * @file
 * @ingroup SRF
 * @ingroup QueryPrinter
 *
 * @license GPL-2.0-or-later
 * @author mwjames
 */
class MediaPlayer extends ResultPrinter {

	/**
	 * Supported extensions (also used as keys by the result data) mapped to
	 * [ media type, MIME type ]
	 *
	 * @var array
	 */
	protected static $mediaTypes = [
		'mp3' => [ 'audio', 'audio/mpeg' ],
		'm4a' => [ 'audio', 'audio/mp4' ],
		'm4p' => [ 'audio', 'audio/mp4' ],
		'aac' => [ 'audio', 'audio/aac' ],
		'oga' => [ 'audio', 'audio/ogg' ],
		'ogg' => [ 'audio', 'audio/ogg' ],
		'opus' => [ 'audio', 'audio/ogg; codecs=opus' ],
		'wav' => [ 'audio', 'audio/wav' ],
		'flac' => [ 'audio', 'audio/flac' ],
		'webma' => [ 'audio', 'audio/webm' ],
		'mp4' => [ 'video', 'video/mp4' ],
		'm4v' => [ 'video', 'video/mp4' ],
		'ogv' => [ 'video', 'video/ogg' ],
		'webm' => [ 'video', 'video/webm' ],
		'webmv' => [ 'video', 'video/webm' ],
	];

	/**
	 * @see ResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return $this->msg( 'srf-printername-media' )->text();
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * @param QueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( QueryResult $result, $outputMode ) {
		$items = $this->getResultData( $result, $outputMode );

		if ( $items !== [] ) {
			return $this->getFormatOutput( $items );
		}

		if ( $this->params[ 'default' ] !== '' ) {
			return $this->params[ 'default' ];
		}

		$result->addErrors( [ $this->msg( 'srf-no-results' )->inContentLanguage()->text() ] );
		return '';
	}

	/**
	 * Returns the playable items, one per result row, each being
	 * [ 'subject' => string, 'title' => string, 'artist' => string, 'poster' => string,
	 *   'mediaType' => 'audio'|'video', 'sources' => [ [ 'src' => url, 'type' => mime ], ... ] ]
	 *
	 * The artist is the (comma separated) value of the printout labelled "artist"
	 * or an empty string.
	 *
	 * Rows without any playable source are skipped.
	 *
	 * @since 1.9
	 *
	 * @param QueryResult $result
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getResultData( QueryResult $result, $outputMode ) {
		$items = [];

		/** @var ResultArray[] $rows */
		while ( $rows = $result->getNext() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$sources = [];
			$mediaType = null;
			$poster = '';
			$title = '';
			$artists = [];
			$subjectText = '';
			$subjectLabel = '';

			foreach ( $rows as $field ) {
				$propertyLabel = $field->getPrintRequest()->getLabel();
				$subjectTitle = $field->getResultSubject()->getTitle();
				$subjectLabel = $subjectTitle->getFullText();

				if ( $propertyLabel === '' || $propertyLabel === '-' ) {
					$propertyLabel = 'subject';
				}

				// The subject itself can be a media file
				if ( $subjectTitle->getNamespace() === NS_FILE && $sources === [] ) {
					$source = $this->getFileSource( $subjectTitle );
					if ( $source !== null ) {
						$sources[$source['src']] = $source;
						$mediaType = $source['mediaType'];
					}
				}

				while ( ( $dataValue = $field->getNextDataValue() ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
					$dataItem = $dataValue->getDataItem();
					$type = $dataItem->getDIType();

					if ( $propertyLabel === 'title' ) {
						$title = $dataValue->getWikiValue();
					} elseif ( $propertyLabel === 'artist' ) {
						// Any property type is accepted (text, page, ...), several values are joined
						$artist = trim( $dataValue->getWikiValue() );
						if ( $artist !== '' && !in_array( $artist, $artists, true ) ) {
							$artists[] = $artist;
						}
					} elseif ( $propertyLabel === 'subject' && $type !== SMWDataItem::TYPE_URI ) {
						// Printouts with an empty label (e.g. "?Title=") have always been
						// treated as the subject, whose text is used as the title
						$subjectText = $dataValue->getWikiValue();
					} elseif ( $type === SMWDataItem::TYPE_WIKIPAGE && $dataItem->getTitle() instanceof Title
						&& $dataItem->getTitle()->getNamespace() === NS_FILE ) {
						if ( $propertyLabel === 'poster' ) {
							$file = $this->findFile( $dataItem->getTitle() );
							if ( $file ) {
								$poster = $file->getUrl();
							}
						} elseif ( $propertyLabel === 'source' ) {
							$source = $this->getFileSource( $dataItem->getTitle() );
							if ( $source !== null ) {
								$sources[$source['src']] = $source;
								$mediaType ??= $source['mediaType'];
							}
						}
					} elseif ( $type === SMWDataItem::TYPE_URI ) {
						if ( $propertyLabel === 'poster' ) {
							$poster = $dataItem->getURI();
							continue;
						}
						$source = $this->getUriSource( $dataItem->getURI() );
						if ( $source !== null ) {
							$sources[$source['src']] = $source;
							$mediaType ??= $source['mediaType'];
						}
					}
				}
			}

			if ( $sources !== [] ) {
				$itemSources = [];
				foreach ( $sources as $itemSource ) {
					$itemSources[] = [ 'src' => $itemSource['src'], 'type' => $itemSource['type'] ];
				}

				$items[] = [
					'subject' => $subjectLabel,
					'title' => $title !== '' ? $title : ( $subjectText !== '' ? $subjectText : $subjectLabel ),
					'artist' => implode( ', ', $artists ),
					'poster' => $poster,
					'mediaType' => $mediaType,
					'sources' => $itemSources,
				];
			}
		}

		return $items;
	}

	/**
	 * Returns the media type and MIME type for a file extension or null if
	 * the extension is not supported
	 *
	 * @since 6.0.2
	 *
	 * @param string $extension
	 *
	 * @return string[]|null [ media type, MIME type ]
	 */
	public static function getMediaTypeForExtension( $extension ) {
		return self::$mediaTypes[strtolower( (string)$extension )] ?? null;
	}

	/**
	 * Returns the text shown for an item: the title followed by the artist(s)
	 * in round brackets, if there are any
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	private function getDisplayTitle( array $item ) {
		return $item['artist'] !== '' ? $item['title'] . ' (' . $item['artist'] . ')' : $item['title'];
	}

	/**
	 * @param Title $title
	 *
	 * @return array|null
	 */
	private function getFileSource( Title $title ) {
		$file = $this->findFile( $title );

		if ( !$file ) {
			return null;
		}

		// File::getExtension() reports "ogg" for ogg/oga/ogv, so use the real name
		$name = $file->getName();
		$pos = strrpos( $name, '.' );
		$extension = $pos === false ? $file->getExtension() : substr( $name, $pos + 1 );
		$known = self::getMediaTypeForExtension( $extension );

		if ( $known === null ) {
			return null;
		}

		[ $mediaType, $mime ] = $known;

		// Container formats (webm, mp4, ogg) are audio or video, trust the file's MIME type
		[ $major ] = File::splitMime( (string)$file->getMimeType() );
		if ( $major === 'audio' || $major === 'video' ) {
			$mediaType = $major;
			$mime = $major . strstr( $mime, '/' );
		}

		return [ 'src' => $file->getUrl(), 'type' => $mime, 'mediaType' => $mediaType ];
	}

	/**
	 * @param string $uri
	 *
	 * @return array|null
	 */
	private function getUriSource( $uri ) {
		$path = parse_url( $uri, PHP_URL_PATH );
		$extension = is_string( $path ) ? pathinfo( $path, PATHINFO_EXTENSION ) : '';
		$known = self::getMediaTypeForExtension( $extension );

		if ( $known === null ) {
			return null;
		}

		return [ 'src' => $uri, 'type' => $known[1], 'mediaType' => $known[0] ];
	}

	/**
	 * Prepare data for the output
	 *
	 * @since 1.9
	 *
	 * @param array $items
	 *
	 * @return string
	 */
	protected function getFormatOutput( $items ) {
		$this->isHTML = true;

		// A <video> element is only used for real video, the cover art of audio
		// files is a plain image because a <video> shows no poster while playing audio
		$isVideo = false;
		$hasPoster = false;
		foreach ( $items as $item ) {
			$isVideo = $isVideo || $item['mediaType'] === 'video';
			$hasPoster = $hasPoster || $item['poster'] !== '';
		}
		$tag = $isVideo ? 'video' : 'audio';

		SMWOutputs::requireResource( 'ext.srf.formats.media' );

		$first = $items[0];
		$attributes = [
			'class' => 'srf-media-element',
			'controls' => true,
			'preload' => 'metadata',
		];
		$firstIsAudio = $first['mediaType'] !== 'video';
		if ( !$firstIsAudio && $first['poster'] !== '' ) {
			$attributes['poster'] = $first['poster'];
		}

		$sources = '';
		foreach ( $first['sources'] as $source ) {
			$sources .= Html::element( 'source', [ 'src' => $source['src'], 'type' => $source['type'] ] );
		}
		$fallback = Html::element( 'a', [ 'href' => $first['sources'][0]['src'] ], $this->getDisplayTitle( $first ) );

		$html = Html::rawElement( $tag, $attributes, $sources . $fallback );

		if ( $hasPoster ) {
			$showCover = $firstIsAudio && $first['poster'] !== '';
			$html = Html::rawElement(
				'div',
				[ 'class' => 'srf-media-stage' ],
				Html::element( 'img', [
					'class' => 'srf-media-cover',
					'src' => $showCover ? $first['poster'] : false,
					'alt' => '',
					'hidden' => !$showCover,
				] ) . $html
			);
		}

		$list = '';
		foreach ( $items as $index => $item ) {
			$list .= Html::rawElement(
				'li',
				[
					'class' => 'srf-media-item' . ( $index === 0 ? ' srf-media-current' : '' ),
					'data-sources' => FormatJson::encode( $item['sources'] ),
					'data-poster' => $item['poster'],
					'data-type' => $item['mediaType'] === 'video' ? 'video' : 'audio',
				],
				Html::element( 'a', [ 'href' => $item['sources'][0]['src'] ], $this->getDisplayTitle( $item ) )
			);
		}
		$html .= Html::rawElement( 'ol', [ 'class' => 'srf-media-playlist' ], $list );

		if ( count( $items ) === 1 ) {
			// The (hidden) playlist is only needed by the script, show the title instead
			$html .= Html::element( 'div', [ 'class' => 'srf-media-title' ], $this->getDisplayTitle( $first ) );
		}

		$classes = [ 'srf-media', 'srf-media-' . $tag ];
		if ( count( $items ) === 1 ) {
			$classes[] = 'srf-media-single';
		}
		if ( $this->params['theme'] !== '' ) {
			$classes[] = 'srf-media-theme-' . str_replace( '.', '-', $this->params['theme'] );
		}
		if ( $this->params['class'] !== '' ) {
			$classes[] = $this->params['class'];
		}

		return Html::rawElement( 'div', [ 'class' => implode( ' ', $classes ) ], $html );
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.9
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ): array {
		$params = parent::getParamDefinitions( $definitions );

		$params['class'] = [
			'message' => 'srf-paramdesc-class',
			'default' => '',
		];

		// Kept for backwards compatibility with queries written for the jPlayer based printer
		$params['theme'] = [
			'message' => 'srf-paramdesc-theme',
			'default' => 'blue.monday',
			'values' => [ 'blue.monday', 'morning.light' ],
		];

		// Kept for backwards compatibility, it has no effect anymore
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
		return MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
	}
}
