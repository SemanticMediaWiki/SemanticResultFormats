<?php

namespace SRF;

use Html;
use MediaWiki\MediaWikiServices;
use SMW\Query\PrintRequest;
use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\ResultPrinter;
use SMWDataItem;
use SMWOutputs;
use SRFUtils;
use Title;
use TraditionalImageGallery;

/**
 * Result printer that outputs query results as a image gallery.
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author mwjames
 * @author Rowan Rodrik van der Molen
 */
class Gallery extends ResultPrinter {

	/**
	 * @see ResultPrinter::getName
	 *
	 * @return string
	 */
	public function getName() {
		return $this->msg( 'srf_printername_gallery' )->text();
	}

	/**
	 * @see ResultPrinter::buildResult
	 *
	 * @since 1.8
	 *
	 * @param QueryResult $results
	 *
	 * @return string
	 */
	protected function buildResult( QueryResult $results ) {
		// Intro/outro are not planned to work with the widget option
		if ( ( $this->params['intro'] !== '' || $this->params['outro'] !== '' ) && $this->params['widget'] !== '' ) {
			$results->addErrors(
				[
					$this->msg( 'srf-error-option-mix', 'widget' )->inContentLanguage()->text()
				]
			);

			return '';
		}

		return $this->getResultText( $results, $this->outputMode );
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * @param $results QueryResult
	 * @param $outputmode integer
	 *
	 * @return string | array
	 */
	public function getResultText( QueryResult $results, $outputmode ) {
		$ig = new TraditionalImageGallery();

		$ig->setShowBytes( false );
		$ig->setShowFilename( false );

		if ( method_exists( $ig, 'setShowDimensions' ) ) {
			$ig->setShowDimensions( false );
		}
		// set caption to IQ header
		$ig->setCaption( $this->mIntro );

		// No need for a special page to use the parser but for the "normal" page
		// view we have to ensure caption text is parsed correctly through the parser
		if ( !$this->isSpecialPage() ) {
			$ig->setParser( MediaWikiServices::getInstance()->getParser() );
		}

		$html = '';
		$processing = '';

		if ( $this->params['widget'] == 'carousel' ) {
			// Carousel widget
			$ig->setAttributes( $this->getCarouselWidget() );
		} elseif ( $this->params['widget'] == 'slideshow' ) {
			// Slideshow widget
			$ig->setAttributes( $this->getSlideshowWidget() );
		} else {
			// Standard gallery attributes
			$ig->setAttributes( $this->getStandardWidget() );
		}

		// In any case we depend on mediawiki.page.gallery.styles
		SMWOutputs::requireStyle( 'mediawiki.page.gallery.styles' );

		// Only use redirects where the overlay option is not used and redirect
		// thumb images towards a different target
		if ( $this->params['redirects'] !== '' && !$this->params['overlay'] ) {
			SMWOutputs::requireResource( 'ext.srf.gallery.redirect' );
		}

		// For the carousel widget, the perrow option should not be set
		if ( $this->params['perrow'] !== '' && $this->params['widget'] !== 'carousel' ) {
			$ig->setPerRow( $this->params['perrow'] );
		}

		if ( $this->params['widths'] !== '' ) {
			$ig->setWidths( $this->params['widths'] );
		}

		if ( $this->params['heights'] !== '' ) {
			$ig->setHeights( $this->params['heights'] );
		}

		$printReqLabels = [];
		$redirectType = '';

		/**
		 * @var PrintRequest $printReq
		 */
		foreach ( $results->getPrintRequests() as $printReq ) {
			$printReqLabels[] = $printReq->getLabel();

			// Get redirect type
			if ( $this->params['redirects'] === $printReq->getLabel() ) {
				$redirectType = $printReq->getTypeID();
			}
		}

		if ( ( $this->params['imageproperty'] !== '' && in_array( $this->params['imageproperty'], $printReqLabels ) ) ||
			( $this->params['redirects'] !== '' && in_array( $this->params['redirects'], $printReqLabels ) ) ) {

			$this->addImageProperties(
				$results,
				$ig,
				$this->params['imageproperty'],
				$this->params['captionproperty'],
				$this->params['redirects'],
				$outputmode
			);
		} else {
			$this->addImagePages( $results, $ig );
		}

		// Display a processing image as long as the DOM is no ready
		if ( $this->params['widget'] !== '' ) {
			$processing = SRFUtils::htmlProcessingElement();
		}

		// Beautify the class selector
		$class = $this->params['widget'] ? '-' . $this->params['widget'] . ' ' : '';
		$class = $this->params['redirects'] !== '' && $this->params['overlay'] === false ? $class . ' srf-redirect' . ' ' : $class;
		$class = $this->params['class'] ? $class . ' ' . $this->params['class'] : $class;

		// Separate content from result output
		if ( !$ig->isEmpty() ) {
			$attribs = [
				'class' => 'srf-gallery' . $class,
				'data-redirect-type' => $redirectType,
				'data-ns-text' => $this->getFileNsTextForPageLanguage()
			];

			$html = Html::rawElement( 'div', $attribs, $processing . $ig->toHTML() );
		}

		// If available, create a link that points to further results
		if ( $this->linkFurtherResults( $results ) ) {
			$html .= $this->getLink( $results, SMW_OUTPUT_HTML )->getText( SMW_OUTPUT_HTML, $this->mLinker );
		}

		// If available and no results, return default message

		if ( $results == '' && $this->params[ 'default' ] !== '' ) {
			$html = $this->params[ 'default' ];
		}

		return [ $html, 'nowiki' => true, 'isHTML' => true ];
	}

	/**
	 * Handles queries where the images (and optionally their captions) are specified as properties.
	 *
	 * @since 1.5.3
	 *
	 * @param QueryResult $results
	 * @param TraditionalImageGallery &$ig
	 * @param string $imageProperty
	 * @param string $captionProperty
	 * @param string $redirectProperty
	 * @param $outputMode
	 */
	protected function addImageProperties( QueryResult $results, &$ig, $imageProperty, $captionProperty, $redirectProperty, $outputMode ) {
		/* array of \SMW\Query\Result\ResultArray */
		while (
		$rows = $results->getNext() ) {
			$images = [];
			$captions = [];
			$redirects = [];
			// Properties
			for ( $i = 0, $n = count( $rows ); $i < $n; $i++ ) {
				/**
				 * @var \SMW\Query\Result\ResultArray $resultArray
				 * @var \SMWDataValue $dataValue
				 */
				$resultArray = $rows[$i];

				$label = $resultArray->getPrintRequest()->getMode() == PrintRequest::PRINT_THIS
					? '-' : $resultArray->getPrintRequest()->getLabel();

				// Make sure always use real label here otherwise it results in an empty array
				if ( $resultArray->getPrintRequest()->getLabel() == $imageProperty ) {
					// Property values
					while ( ( $dataValue = $resultArray->getNextDataValue() ) !== false ) {
						if ( $dataValue->getTypeID() == '_wpg' ) {
							$images[] = $dataValue->getDataItem()->getTitle();
						}
					}
				} elseif ( $label == $captionProperty ) {
					// Property values
					while ( ( $dataValue = $resultArray->getNextDataValue() ) !== false ) {
						$captions[] = $dataValue->getShortText( $outputMode, $this->getLinker( true ) );
					}
				} elseif ( $label == $redirectProperty ) {
					// Property values
					while ( ( $dataValue = $resultArray->getNextDataValue() ) !== false ) {
						if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_WIKIPAGE ) {
							$redirects[] = $dataValue->getTitle();
						} elseif ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_URI ) {
							$redirects[] = $dataValue->getURL();
						}
					}
				}
			}

			// Check available matches against captions
			$amountMatches = count( $captions ) == count( $images );
			$hasCaption = $amountMatches || count( $captions ) > 0;

			// Check available matches against redirects
			$amountRedirects = count( $redirects ) == count( $images );
			$hasRedirect = $amountRedirects || count( $redirects ) > 0;

			/**
			 * @var Title $imgTitle
			 */
			foreach ( $images as $imgTitle ) {
				if ( $imgTitle->exists() ) {
					$imgCaption = $hasCaption ? ( $amountMatches ? array_shift( $captions ) : $captions[0] ) : '';
					$imgRedirect = $hasRedirect ? ( $amountRedirects ? array_shift( $redirects ) : $redirects[0] ) : '';
					$this->addImageToGallery( $ig, $imgTitle, $imgCaption, $imgRedirect );
				}
			}
		}
	}

	/**
	 * Handles queries where the result objects are image pages.
	 *
	 * @since 1.5.3
	 *
	 * @param QueryResult $results
	 * @param TraditionalImageGallery &$ig
	 */
	protected function addImagePages( QueryResult $results, &$ig ) {
		while ( $row = $results->getNext() ) {
			/**
			 * @var \SMW\Query\Result\ResultArray $firstField
			 */
			$firstField = $row[0];

			/** @var \SMWDataValue $nextObject */
			$nextObject = $firstField->getNextDataValue();

			if ( $nextObject !== false ) {
				$dataItem = $nextObject->getDataItem();
				$imgTitle = method_exists( $dataItem, 'getTitle' ) ? $dataItem->getTitle() : null;

				// Ensure the title belongs to the image namespace
				if ( $imgTitle instanceof Title && $imgTitle->getNamespace() === NS_FILE ) {
					$imgCaption = '';

					// Is there a property queried for display with ?property
					if ( isset( $row[1] ) ) {
						$imgCaption = $row[1]->getNextDataValue();
						if ( is_object( $imgCaption ) ) {
							$imgCaption = $imgCaption->getShortText( $this->outputMode, $this->getLinker( true ) );
						}
					}

					$this->addImageToGallery( $ig, $imgTitle, $imgCaption );
				}
			}
		}
	}

	/**
	 * Adds a single image to the gallery.
	 * Takes care of automatically adding a caption when none is provided and parsing it's wikitext.
	 *
	 * @since 1.5.3
	 *
	 * @param TraditionalImageGallery &$ig The gallery to add the image to
	 * @param Title $imgTitle The title object of the page of the image
	 * @param string $imgCaption An optional caption for the image
	 * @param string $imgRedirect
	 */
	protected function addImageToGallery( &$ig, Title $imgTitle, $imgCaption, $imgRedirect = '' ) {
		if ( empty( $imgCaption ) ) {
			if ( $this->params['autocaptions'] ) {
				$imgCaption = $imgTitle->getBaseText();

				if ( !$this->params['fileextensions'] ) {
					$imgCaption = preg_replace( '#\.[^.]+$#', '', $imgCaption );
				}
			} else {
				$imgCaption = '';
			}
		} else {
			if ( $imgTitle instanceof Title && $imgTitle->getNamespace() == NS_FILE && !$this->isSpecialPage() ) {
				$imgCaption = $ig->mParser->recursiveTagParse( $imgCaption );
			}
		}

		if ( $this->params['captiontemplate'] !== '' && gettype( $ig->mParser ) == "object" ) {
			$templateCode = "{{" . $this->params['captiontemplate'] .
				"|imageraw=" . $imgTitle->getPrefixedText() . "|imagecaption=$imgCaption|imageredirect=$imgRedirect}}";

			$imgCaption = $ig->mParser->recursiveTagParse( $templateCode );

		}

		// Use image alt as helper for either text
		$imgAlt = $this->params['redirects'] === '' ? $imgCaption : ( $imgRedirect !== '' ? $imgRedirect : '' );
		$ig->add( $imgTitle, $imgCaption, $imgAlt );
	}

	/**
	 * Returns the overlay setting
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	private function getImageOverlay() {
		if ( array_key_exists( 'overlay', $this->params ) && $this->params['overlay'] == true ) {
			SMWOutputs::requireResource( 'ext.srf.gallery.overlay' );
			return ' srf-overlay';
		} else {
			return '';
		}
	}

	/**
	 * @return array
	 */
	private function getStandardWidget() {
		return [
			'id' => uniqid(),
			'class' => $this->getImageOverlay()
		];
	}

	/**
	 * Init carousel widget
	 *
	 * @since 1.8
	 *
	 * @return string[]
	 */
	private function getCarouselWidget() {
		// Set attributes for jcarousel
		$dataAttribs = [
			// Whether to wrap at the first/last item (or both) and jump back to the start/end.
			'wrap' => 'both',
			// Orientation: vertical = false means horizontal
			'vertical' => 'false',
			// Directionality: rtl = false means ltr
			'rtl' => 'false',
		];

		// Use the perrow parameter to determine the scroll sequence.
		if ( empty( $this->params['perrow'] ) ) {
			$dataAttribs['scroll'] = 1;
		} else {
			$dataAttribs['scroll'] = $this->params['perrow'];
			$dataAttribs['visible'] = $this->params['perrow'];
		}

		$attribs = [
			'id' => uniqid(),
			'class' => 'jcarousel jcarousel-skin-smw' . $this->getImageOverlay(),
			'style' => 'display:none;',
		];

		foreach ( $dataAttribs as $name => $value ) {
			$attribs['data-' . $name] = $value;
		}

		SMWOutputs::requireResource( 'ext.srf.gallery.carousel' );

		return $attribs;
	}

	/**
	 * Init slideshow widget
	 *
	 * @since 1.8
	 *
	 * @return string[]
	 */
	private function getSlideshowWidget() {
		$attribs = [
			'id' => uniqid(),
			'class' => $this->getImageOverlay(),
			'style' => 'display:none;',
			'data-nav-control' => $this->params['navigation']
		];

		SMWOutputs::requireResource( 'ext.srf.gallery.slideshow' );

		return $attribs;
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['class'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-class',
			'default' => ''
		];

		$params['widget'] = [
			'type' => 'string',
			'default' => '',
			'message' => 'srf-paramdesc-widget',
			'values' => [ 'carousel', 'slideshow', '' ]
		];

		$params['navigation'] = [
			'type' => 'string',
			'default' => 'nav',
			'message' => 'srf-paramdesc-navigation',
			'values' => [ 'nav', 'pager', 'auto' ]
		];

		$params['overlay'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-overlay'
		];

		$params['perrow'] = [
			'type' => 'integer',
			'default' => '',
			'message' => 'srf_paramdesc_perrow'
		];

		$params['widths'] = [
			'type' => 'integer',
			'default' => '',
			'message' => 'srf_paramdesc_widths'
		];

		$params['heights'] = [
			'type' => 'integer',
			'default' => '',
			'message' => 'srf_paramdesc_heights'
		];

		$params['autocaptions'] = [
			'type' => 'boolean',
			'default' => true,
			'message' => 'srf_paramdesc_autocaptions'
		];

		$params['fileextensions'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf_paramdesc_fileextensions'
		];

		$params['captionproperty'] = [
			'type' => 'string',
			'default' => '',
			'message' => 'srf_paramdesc_captionproperty'
		];

		$params['captiontemplate'] = [
			'type' => 'string',
			'default' => '',
			'message' => 'srf-paramdesc-captiontemplate'
		];

		$params['imageproperty'] = [
			'type' => 'string',
			'default' => '',
			'message' => 'srf_paramdesc_imageproperty'
		];

		$params['redirects'] = [
			'type' => 'string',
			'default' => '',
			'message' => 'srf-paramdesc-redirects'
		];

		return $params;
	}

	/**
	 * @return bool
	 */
	private function isSpecialPage() {
		$title = $GLOBALS['wgTitle'];
		return $title instanceof Title && $title->isSpecialPage();
	}

	/**
	 * @return bool|null|string
	 */
	private function getFileNsTextForPageLanguage() {
		$title = $GLOBALS['wgTitle'];
		return $title instanceof Title ? $title->getPageLanguage()->getNsText( NS_FILE ) : null;
	}

}
