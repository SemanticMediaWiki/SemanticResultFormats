<?php

/**
 * Result printer that prints query results as a gallery.
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
 * @file SRF_Gallery.php
 * @ingroup SemanticResultFormats
 *
 * @author Jeroen De Dauw
 * @author mwjames
 * @author Rowan Rodrik van der Molen
 */
class SRFGallery extends SMWResultPrinter {

	/**
	 * @see SMWResultPrinter::getName
	 *
	 * @return string
	 */
	public function getName() {
		return wfMsg( 'srf_printername_gallery' );
	}

	/**
	 * @see SMWResultPrinter::buildResult
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $results
	 *
	 * @return string
	 */
	protected function buildResult( SMWQueryResult $results ) {

		// Features check
		// Widgets and intro/outro are not planned to work
		if ( $this->params['intro'] !== '' && $this->params['widget'] !== '' ){
			return $results->addErrors( array( wfMsgForContent( 'srf-error-option-mix', 'intro/widget' ) ) );
		} elseif( $this->params['outro'] !== '' && $this->params['widget'] !== '' ){
			return $results->addErrors( array( wfMsgForContent( 'srf-error-option-mix', 'outro/widget' ) ) );
		};

		return $this->getResultText( $results, SMW_OUTPUT_HTML );
	}

	public function getResultText( SMWQueryResult $results, $outputmode ) {

		$ig = new ImageGallery();
		$ig->setShowBytes( false );
		$ig->setShowFilename( false );
		$ig->setCaption( $this->mIntro ); // set caption to IQ header

		// Don't init the parser if it is a special page otherwise it causes a fatal error
		// We need the parser for "normal" pages to ensure caption text is rendered
		// correctly but is less important for Special:Ask
		!$this->isSpecialPage() ? $ig->setParser( $GLOBALS['wgParser'] ) : '';

		// Initialize
		static $statNr = 0;
		$html          = '';
		$processing    = '';

		// Carousel parameters
		if ( $this->params['widget'] == 'carousel' ) {

			// Set attributes for jcarousel
			$dataAttribs = array(
				'wrap' => 'both', // Whether to wrap at the first/last item (or both) and jump back to the start/end.
				'vertical' => 'false', // Orientation: vertical = false means horizontal
				'rtl' => 'false', // Directionality: rtl = false means ltr
			);

			// Use perrow parameter to determine the scroll sequence.
			if ( empty( $this->params['perrow'] ) ) {
				$dataAttribs['scroll'] = 1;  // default 1
			} else {
				$dataAttribs['scroll'] = $this->params['perrow'];
				$dataAttribs['visible'] = $this->params['perrow'];
			}

			$attribs = array(
				'id' =>  $this->params['widget'] . '-' . ++$statNr,
				'class' => 'jcarousel jcarousel-skin-smw' . $this->getImageOverlay(),
				'style' => 'display:none;',
			);

			foreach ( $dataAttribs as $name => $value ) {
				$attribs['data-' . $name] = $value;
			}

			$ig->setAttributes( $attribs );

			// RL module
			SMWOutputs::requireResource( 'ext.srf.gallery.carousel' );
		}

		// Slideshow parameters
		if ( $this->params['widget'] == 'slideshow' ) {
			$mAttribs = array(
				'id'    => $this->params['widget'] . '-' . ++$statNr,
				'class' => $this->getImageOverlay(),
				'style' => 'display:none;',
				'data-nav-control' => $this->params['navigation']
			);

			$ig->setAttributes( $mAttribs );

			// RL module
			SMWOutputs::requireResource( 'ext.srf.gallery.slideshow' );
		}

		// Only use redirects where overlay is false
		// Allow thumb pictures to be redirected towards a different target
		if ( $this->params['redirects'] !== '' && $this->params['overlay'] === false ){
			// RL module
			SMWOutputs::requireResource( 'ext.srf.gallery.redirect' );
		}

		// In case widget = carousel, perrow should not be set
		if ( $this->params['perrow'] !== '' && $this->params['widget'] !== 'carousel' ) {
			$ig->setPerRow( $this->params['perrow'] );
		}

		if ( $this->params['widths'] !== '' ) {
			$ig->setWidths( $this->params['widths'] );
		}

		if ( $this->params['heights'] !== '' ) {
			$ig->setHeights( $this->params['heights'] );
		}

		$printReqLabels = array();

		foreach ( $results->getPrintRequests() as /* SMWPrintRequest */ $printReq ) {
			$printReqLabels[] = $printReq->getLabel();
		}

		if ( $this->params['imageproperty'] !== '' && in_array( $this->params['imageproperty'], $printReqLabels ) ||
			$this->params['redirects'] !== '' && in_array( $this->params['redirects'], $printReqLabels ) ) {

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

		// SRF Global settings
		SRFUtils::addGlobalJSVariables();

		// Display a processing image as long as jquery is not loaded
		if ( $this->params['widget'] !== '' ) {
			$processing = SRFUtils::htmlProcessingElement();
		}

		// Beautify class selector
		$class = $this->params['widget'] ?  '-' . $this->params['widget'] . ' ' : '';
		$class = $this->params['redirects'] !== '' && $this->params['overlay'] === false ? $class . ' srf-redirect' . ' ': $class;
		$class = $this->params['class'] ? $class . ' ' . $this->params['class'] : $class ;

		// Separate content from result output
		if ( !$ig->isEmpty() ) {
			$attribs = array (
				'class'  => 'srf-gallery' . $class,
				'align'  => 'justify'
			);

			$html = Html::rawElement( 'div', $attribs, $processing . $ig->toHTML() );
		}

		// Allow to create a link that points to further results
		if ( $this->linkFurtherResults( $results ) ) {
			$html .= $this->getLink( $results, $outputmode )->getText( $outputmode, $this->mLinker );
		}

		return array( $html, 'nowiki' => true, 'isHTML' => true );
	}

	/**
	 * Handles queries where the images (and optionally their captions) are specified as properties.
	 *
	 * @since 1.5.3
	 *
	 * @param SMWQueryResult $results
	 * @param ImageGallery $ig
	 * @param string $imageProperty
	 * @param string $captionProperty
	 * @param $outputMode
	 */
	protected function addImageProperties( SMWQueryResult $results, ImageGallery &$ig, $imageProperty, $captionProperty, $redirectProperty, $outputMode ) {
		while ( /* array of SMWResultArray */ $row = $results->getNext() ) { // Objects (pages)
			$images = array();
			$captions = array();
			$redirects = array();

			for ( $i = 0, $n = count( $row ); $i < $n; $i++ ) { // Properties
				if ( $row[$i]->getPrintRequest()->getLabel() == $imageProperty ) {
					while ( ( $obj = $row[$i]->getNextDataValue() ) !== false ) { // Property values
						if ( $obj->getTypeID() == '_wpg' ) {
							$images[] = $obj->getTitle();
						}
					}
				} elseif ( $row[$i]->getPrintRequest()->getLabel() == $captionProperty ) {
					while ( ( $obj = $row[$i]->getNextDataValue() ) !== false ) { // Property values
						$captions[] = $obj->getShortText( $outputMode, $this->getLinker( true ) );
					}
				} elseif ( $row[$i]->getPrintRequest()->getLabel() == $redirectProperty ) {
					while ( ( $obj = $row[$i]->getNextDataValue() ) !== false ) { // Property values
						if ( $obj->getTypeID() == '_wpg' ) {
							$redirects[] = $obj->getTitle();
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
	 * @param SMWQueryResult $results
	 * @param ImageGallery $ig
	 */
	protected function addImagePages( SMWQueryResult $results, ImageGallery &$ig ) {
		while ( $row = $results->getNext() ) {
			$firstField = $row[0];
			$nextObject = $firstField->getNextDataValue();

			if ( $nextObject !== false ) {
				$imgTitle = $nextObject->getTitle();

				if ( !is_null( $imgTitle ) ) {
					$imgCaption = '';

					// Is there a property queried for display with ?property
					if ( isset( $row[1] ) ) {
						$imgCaption = $row[1]->getNextDataValue();
						if ( is_object( $imgCaption ) ) {
							$imgCaption = $imgCaption->getShortText( SMW_OUTPUT_HTML, $this->getLinker( true ) );
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
	 * @param ImageGallery $ig The gallery to add the image to
	 * @param Title $imgTitle The title object of the page of the image
	 * @param string $imgCaption An optional caption for the image
	 */
	protected function addImageToGallery( ImageGallery &$ig, Title $imgTitle, $imgCaption, $imgRedirect = '' ) {

		if ( empty( $imgCaption ) ) {
			if ( $this->m_params['autocaptions'] ) {
				$imgCaption = $imgTitle->getBaseText();

				if ( !$this->m_params['fileextensions'] ) {
					$imgCaption = preg_replace( '#\.[^.]+$#', '', $imgCaption );
				}
			} else {
				$imgCaption = '';
			}
		} else {
			if ( $imgTitle instanceof Title && $imgTitle->getNamespace() == NS_FILE && !$this->isSpecialPage() ) {
				$imgCaption = $GLOBALS['wgParser']->recursiveTagParse( $imgCaption );
			}
		}
		// Use image alt as helper for either text
		$imgAlt =  $this->params['redirects'] === '' ? $imgCaption : $imgRedirect !== '' ? $imgRedirect : '' ;
		$ig->add( $imgTitle, $imgCaption, $imgAlt );
	}

	/**
	 * Check if accessing page is a SpecialPage
	 *
	 * @since 1.8
	 *
	 * @return boolean
	 */
	protected function isSpecialPage() {
		// @TODO global
		// This should come from RequestContext but we can't because of MW 1.17
		return $GLOBALS['wgTitle']->isSpecialPage();
	}

	/**
	 * Return the image overlay setting
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	protected function getImageOverlay() {
		if ( array_key_exists( 'overlay', $this->params ) && $this->params['overlay'] == true ) {
			SMWOutputs::requireResource( 'ext.srf.gallery.overlay' );
			return ' srf-overlay';
		}
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

		$params['class'] = array(
			'type' => 'string',
			'message' => 'srf-paramdesc-class',
			'default' => ''
		);

		$params['widget'] = array(
			'type' => 'string',
			'default' => '',
			'message' => 'srf-paramdesc-widget',
			'values' => array( 'carousel', 'slideshow' )
		);

		$params['navigation'] = array(
			'type' => 'string',
			'default' => 'nav',
			'message' => 'srf-paramdesc-navigation',
			'values' => array( 'nav', 'pager', 'auto' )
		);

		$params['overlay'] = array(
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-overlay'
		);

		$params['perrow'] = array(
			'type' => 'integer',
			'default' => '',
			'message' => 'srf_paramdesc_perrow'
		);

		$params['widths'] = array(
			'type' => 'integer',
			'default' => '',
			'message' => 'srf_paramdesc_widths'
		);

		$params['heights'] = array(
			'type' => 'integer',
			'default' => '',
			'message' => 'srf_paramdesc_heights'
		);

		$params['autocaptions'] = array(
			'type' => 'boolean',
			'default' => true,
			'message' => 'srf_paramdesc_autocaptions'
		);

		$params['fileextensions'] = array(
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf_paramdesc_fileextensions'
		);

		$params['captionproperty'] = array(
			'type' => 'string',
			'default' => '',
			'message' => 'srf_paramdesc_captionproperty'
		);

		$params['imageproperty'] = array(
			'type' => 'string',
			'default' => '',
			'message' => 'srf_paramdesc_imageproperty'
		);

		$params['redirects'] = array(
			'type' => 'string',
			'default' => '',
			'message' => 'srf-paramdesc-redirects'
		);

		return $params;
	}
}