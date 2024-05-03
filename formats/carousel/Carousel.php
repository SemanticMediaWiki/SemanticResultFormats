<?php
/**
 * Carousel (Slick)
 *
 * @license GPL-2.0-or-later
 *
 * @author thomas-topway-it for KM-A
 */
namespace SRF;

use Html;
use SMW\ResultPrinter;
use SMWPrintRequest;
use SMWQueryResult;
use MediaWiki\MediaWikiServices;

class Carousel extends ResultPrinter {

	/*
	 * camelCase params
	 */
	protected static $camelCaseParamsKeys = [];

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->msg( 'srf-printername-carousel' )->text();
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

		$params['width'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-width',
			'default' => '',
		];

		$params['height'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-height',
			'default' => '',
		];

		$params['class'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-class',
			'default' => '',
		];

		$params['captionproperty'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-captionproperty',
			'default' => '',
		];

		$params['titleproperty'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-titleproperty',
			'default' => '',
		];

		$params['linkproperty'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-linkproperty',
			'default' => '',
		];

		$params['imageproperty'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-imageproperty',
			'default' => '',
		];

		$params['slick-accessibility'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-adaptiveHeight'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		// $params['slick-appendArrows'] = [
		// 	'type' => 'string',
		// 	'message' => 'srf-paramdesc-carousel-slick-option',
		// 	'default' => '$(element)',
		// ];

		// $params['slick-appendDots'] = [
		// 	'type' => 'string',
		// 	'message' => 'srf-paramdesc-carousel-slick-option',
		// 	'default' => '$(element)',
		// ];

		$params['slick-arrows'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		// $params['slick-asNavFor'] = [
		// 	'type' => 'string',
		// 	'message' => 'srf-paramdesc-carousel-slick-option',
		// 	'default' => '$(element)',
		// ];

		$params['slick-autoplay'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-autoplaySpeed'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 3000,
		];

		$params['slick-centerMode'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-centerPadding'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => "50px",
		];

		$params['slick-cssEase'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => "ease",
		];

		// $params['slick-customPaging'] = [
		//	'message' => 'srf-paramdesc-carousel-slick-option',
		//	'default' => "",
		// ];

		$params['slick-dots'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-dotsClass'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => "slick-dots",
		];

		$params['slick-draggable'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-easing'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => "linear",
		];

		$params['slick-edgeFriction'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 0.15,
		];

		$params['slick-fade'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-focusOnSelect'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-focusOnChange'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-infinite'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-initialSlide'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 0,
		];

		$params['slick-lazyLoad'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => "ondemand",
		];

		$params['slick-mobileFirst'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-nextArrow'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => '<button type="button" class="slick-next">Next</button>',
		];

		$params['slick-pauseOnDotsHover'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-pauseOnFocus'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-pauseOnHover'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-prevArrow'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => '<button type="button" class="slick-prev">Previous</button>',
		];

		$params['slick-respondTo'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => "window",
		];

		// @see https://github.com/kenwheeler/slick/#responsive-option-example
		// $params['slick-responsive'] = [
		// 	'type' => 'string',
		//  	'message' => 'srf-paramdesc-carousel-slick-option',
		//  	'default' => null,
		// ];

		$params['slick-rows'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 1,
		];

		$params['slick-rtl'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-slide'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => '',
		];

		$params['slick-slidesPerRow'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 1,
		];

		$params['slick-slidesToScroll'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 1,
		];

		$params['slick-slidesToShow'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 1,
		];

		$params['slick-speed'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 300,
		];

		$params['slick-swipe'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-swipeToSlide'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-touchMove'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-touchThreshold'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 5,
		];

		$params['slick-useCSS'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-useTransform'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-variableWidth'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-vertical'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-verticalSwiping'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => false,
		];

		$params['slick-waitForAnimate'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => true,
		];

		$params['slick-zIndex'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-option',
			'default' => 1000,
		];

		// *** work-around to allow camelCase parameters
		$ret = [];
		foreach ( $params as $key => $value ) {
			$strlower = strtolower($key);
			self::$camelCaseParamsKeys[$strlower] = $key;
			$ret[$strlower] = $value;
		}

		return $ret;
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( SMWQueryResult $results, $outputmode ) {
		$resourceFormatter = new ResourceFormatter();

		// serialized results
		$data = $resourceFormatter->getData( $results, $outputmode, $this->params );

		$this->isHTML = true;

		// SMWOutputs::requireResource( 'ext.srf.carousel' );
		$resourceFormatter->registerResources( [
			'ext.srf.carousel',
			'ext.srf.carousel.module'
		] );

		/*
		 * first retrieve explicitly set properties:
		 * titleproperty, captionproperty, imageproperty, linkproperty
		 * otherwise retrieve them from the mainlabel or
		 * the properties type
		 */

		// get printrequests and their types
		$printReqLabels = [];
		foreach( $data['query']['result']['printrequests'] as $value ) {
			// _uri, _txt, _wpg
			$printReqLabels[ $value['label'] ] = $value['typeid'];
		}

		$inlineStyles = $this->getInlineStyles();

		$parser = MediaWikiServices::getInstance()->getParser();
		$items = [];
		foreach( $data['query']['result']['results'] as $titleText => $value ) {
			$title_ = \Title::newFromText( $titleText );
			$captions = [];
			$titles = [];
			$images = [];
			$links = [];

			// values explicitly set
			foreach( $value['printouts'] as $name => $values ) {
				switch( $name ) {
					case $this->params['titleproperty']:
						$titles = $values;
					break;
					case $this->params['captionproperty']:
						$captions = $values;
					break;
					case $this->params['linkproperty']:
						$links = $values;
					break;
					case $this->params['imageproperty']:
						foreach( $values as $printout_value ) {
							$images[] = $this->getImage( $printout_value );
						}
					break;
				}
			}

			$captionValue = $this->getFirstValid( $captions );
			$titleValue = $this->getFirstValid( $titles );

			$linkValue = $this->getFirstValid( $links );
			$imageValue = $this->getFirstValid( $images );

			// if one or more value is empty infer them from the property type
			foreach( $value['printouts'] as $name => $values ) {
				// && $this->params['titleproperty'] !== $name
				if ( !$captionValue && !$titleValue && $printReqLabels[ $name ] === '_txt' ) {
					$captionValue = $this->getFirstValid( $values );
				}
				if ( !$linkValue && $printReqLabels[ $name ] === '_uri' ) {
					$linkValue = $this->getFirstValid( $values );
				}
				if ( !$imageValue && $printReqLabels[ $name ] === '_wpg' ) {
					foreach( $values as $printout_value ) {
						$images[] = $this->getImage( $printout_value );
					}
					$imageValue = $this->getFirstValid( $images );
				}
			}

			// mainlabel
			if ( array_key_exists( 'namespace', $value ) ) {
				if ( !$imageValue && $value['namespace'] === NS_FILE ) {
					$imageValue = $this->getImage( [ 'fullurl' => $value['fullurl'], 'fulltext' => $value['fulltext'], 'namespace' => $value['namespace'] ] );
				}

				if ( !$titleValue && $value['namespace'] !== NS_FILE ) {
					$arr_ = explode( "/", $value['fulltext'] );
					$titleValue = end( $arr_ );
				}

				if ( !$linkValue  ) {
					$linkValue = $value['fullurl'];
				}

			} else if ( !$imageValue || !$linkValue ) {
				if ( !$imageValue && $title_->getNamespace() === NS_FILE ) {
					$imageValue = $this->getImage( [ 'fullurl' => $title_->getFullUrl(), 'fulltext' => $title_->getFullText(), 'namespace' => $title_->getNamespace() ] );
				}
				if ( !$linkValue ) {
					$linkValue = $title_->getFullUrl();
				}
			}

			if ( !$imageValue ) {
				continue;
			}

			if ( $captionValue ) {
				$captionValue = $parser->recursiveTagParse( $captionValue );
			}

			$imgAttr = [
				'src' => $imageValue,
				'alt' => ( $titleValue ?? $captionValue ? strip_tags( $captionValue ) : $title_->getText() ),
				'class' => "slick-slide-content img"
			];
			
			if ( !empty( $inlineStyles['img'] ) ) {
				$imgAttr['style'] = $inlineStyles['img'];
			}

			$innerContent = Html::rawElement( 'img', $imgAttr );

			if ( $titleValue || $captionValue ) {
				$innerContent .= Html::rawElement( 'div', [ 'class' => 'slick-slide-content caption' ],
					( $titleValue ? Html::rawElement( 'div', [ 'class' => 'slick-slide-content caption-title' ], $titleValue ) : '' )
					. ( $captionValue ? Html::rawElement( 'div', [ 'class' => 'slick-slide-content caption-text' ], $captionValue ) : '' )
				);
			}
			
			$items[] = Html::rawElement(
				'div',
				[
					'class' => 'slick-slide',
					'data-url' => $linkValue
				],
				$innerContent
			);

		} // loop through pages

		$attr = [ 'class' => 'slick-slider' . ( empty( $this->params['class'] ) ? '' : ' ' . $this->params['class'] ) ];
	
		if ( !empty( $inlineStyles['div'] ) ) {
			$attr['style'] = $inlineStyles['div'];
		}

		$slick_attr = [];
		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'slick-')  === 0 ) {
				$slick_attr[ str_replace( 'slick-', '', self::$camelCaseParamsKeys[$key] ) ] = $value ;
			}
		}

		$attr['data-slick'] = json_encode( $slick_attr );

		return Html::rawElement(
				'div',
				$attr,
				implode( $items )
			);
	}
	
	/**
	 * @return array
	 */
	private function getInlineStyles() {
		if ( empty( $this->params['width'] ) ) {
			$this->params['width'] = '100%';
		}

		preg_match( '/^(\d+)(.+)?$/', $this->params['width'], $match );		
		$styleImg = [ 'object-fit: cover' ];

		$absoluteUnits = [ 'cm', 'mm', 'in', 'px', 'pt', 'pc' ];
		$slidestoshow = $this->params['slick-slidestoshow'];
		
		// @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/784
		if ( !empty( $slidestoshow ) && is_int( $slidestoshow ) && !empty( $match[1] ) ) {
			if ( empty( $match[2] ) ) {
				$match[2] = 'px';
			}
			$styleImg[] = 'max-width:' . ( in_array( $match[2], $absoluteUnits ) ?
				( $match[1] / $slidestoshow ) . $match[2]
				: '100%' );
		}
		
		$styleAttr = [ 'width', 'height' ];
		$style = [];
		foreach( $styleAttr as $attr ) {
			if ( !empty( $this->params[$attr] ) ) {
				$style[ $attr ] = "$attr: " . $this->params[$attr];
			}
		}

		return [ 'div' => implode( '; ',  $style ),
			'img' => implode( '; ',  $styleImg ) ];
	}

	/**
	 * @param array $array
	 * @return string|null
	 */
	protected function getFirstValid( $array ) {
		// *** or use array_filter with no arguments, then
		// retrieve the first entry
		foreach( $array as $value ) {
			if ( !empty( $value ) ) {
				return ( is_array( $value ) ? $value['fulltext'] : $value );
			}
		}
		return null;
	}

	/**
	 * @param array $value
	 * @return string|null
	 */
	protected function getImage( $value ) {
		if ( !is_array( $value ) || !array_key_exists( 'fullurl', $value ) || $value['namespace'] !== NS_FILE  ) {
			return null;
		}

		$title = \Title::newFromText( $value['fulltext'], NS_FILE );
		$wikiFilePage = new \WikiFilePage( $title );
		$file = $wikiFilePage->getFile();

		if ( !$file ) {
			return null;
		}

		return $file->getUrl();
	}

}
