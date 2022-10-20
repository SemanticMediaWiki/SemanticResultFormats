<?php
/**
 * Carousel (Slick)
 *
 * @license GPL-2.0-or-later
 *
 * @author thomas-topway-it
 */
namespace SRF;

use Html;
use SMW\ResultPrinter;
use SMWPrintRequest;
use SMWQueryResult;
use MediaWiki\MediaWikiServices;

class Carousel extends ResultPrinter {

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
			'message' => 'srf-paramdesc-carousel-slick-accessibility',
			'default' => true,
		];

		$params['slick-adaptiveHeight'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-adaptiveHeight',
			'default' => false,
		];

		// $params['slick-appendArrows'] = [
		// 	'type' => 'string',
		// 	'message' => 'srf-paramdesc-carousel-slick-appendArrows',
		// 	'default' => '$(element)',
		// ];

		// $params['slick-appendDots'] = [
		// 	'type' => 'string',
		// 	'message' => 'srf-paramdesc-carousel-slick-appendDots',
		// 	'default' => '$(element)',
		// ];

		$params['slick-arrows'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-arrows',
			'default' => true,
		];

		// $params['slick-asNavFor'] = [
		// 	'type' => 'string',
		// 	'message' => 'srf-paramdesc-carousel-slick-asNavFor',
		// 	'default' => '$(element)',
		// ];

		$params['slick-autoplay'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-autoplay',
			'default' => true,
		];

		$params['slick-autoplaySpeed'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-autoplaySpeed',
			'default' => 3000,
		];

		$params['slick-centerMode'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-centerMode',
			'default' => false,
		];

		$params['slick-centerPadding'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-centerPadding',
			'default' => "50px",
		];

		$params['slick-cssEase'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-cssEase',
			'default' => "ease",
		];

		// $params['slick-customPaging'] = [
		//	'message' => 'srf-paramdesc-carousel-slick-customPaging',
		//	'default' => "",
		// ];

		$params['slick-dots'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-dots',
			'default' => false,
		];

		$params['slick-dotsClass'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-dotsClass',
			'default' => "slick-dots",
		];

		$params['slick-dotsClass'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-dotsClass',
			'default' => "slick-dots",
		];

		$params['slick-draggable'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-draggable',
			'default' => true,
		];

		$params['slick-easing'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-easing',
			'default' => "linear",
		];

		$params['slick-edgeFriction'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-edgeFriction',
			'default' => 0.15,
		];

		$params['slick-fade'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-fade',
			'default' => false,
		];

		$params['slick-focusOnSelect'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-focusOnSelect',
			'default' => false,
		];

		$params['slick-focusOnChange'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-focusOnChange',
			'default' => false,
		];

		$params['slick-infinite'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-infinite',
			'default' => true,
		];

		$params['slick-initialSlide'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-initialSlide',
			'default' => 0,
		];

		$params['slick-lazyLoad'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-lazyLoad',
			'default' => "ondemand",
		];

		$params['slick-mobileFirst'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-mobileFirst',
			'default' => false,
		];

		$params['slick-nextArrow'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-nextArrow',
			'default' => '<button type="button" class="slick-next">Next</button>',
		];

		$params['slick-pauseOnDotsHover'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-pauseOnDotsHover',
			'default' => false,
		];

		$params['slick-pauseOnFocus'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-pauseOnFocus',
			'default' => true,
		];

		$params['slick-pauseOnHover'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-pauseOnHover',
			'default' => true,
		];

		$params['slick-prevArrow'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-prevArrow',
			'default' => '<button type="button" class="slick-prev">Previous</button>',
		];

		$params['slick-respondTo'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-respondTo',
			'default' => "window",
		];

		$params['slick-responsive'] = [
		 	'message' => 'srf-paramdesc-carousel-slick-responsive',
		 	'default' => null,
		];

		$params['slick-rows'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-rows',
			'default' => 1,
		];

		$params['slick-rtl'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-rtl',
			'default' => false,
		];

		$params['slick-slide'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-carousel-slick-slide',
			'default' => '',
		];

		$params['slick-slidesPerRow'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-slidesPerRow',
			'default' => 1,
		];

		$params['slick-slidesToScroll'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-slidesToScroll',
			'default' => 1,
		];

		$params['slick-slidesToShow'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-slidesToShow',
			'default' => 1,
		];

		$params['slick-speed'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-speed',
			'default' => 300,
		];

		$params['slick-swipe'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-swipe',
			'default' => true,
		];

		$params['slick-swipeToSlide'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-swipeToSlide',
			'default' => false,
		];

		$params['slick-touchMove'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-touchMove',
			'default' => true,
		];

		$params['slick-touchThreshold'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-touchThreshold',
			'default' => 5,
		];

		$params['slick-useCSS'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-useCSS',
			'default' => true,
		];

		$params['slick-useTransform'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-useTransform',
			'default' => true,
		];

		$params['slick-variableWidth'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-variableWidth',
			'default' => false,
		];

		$params['slick-vertical'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-vertical',
			'default' => false,
		];

		$params['slick-verticalSwiping'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-vertical',
			'default' => false,
		];

		$params['slick-waitForAnimate'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-carousel-slick-waitForAnimate',
			'default' => true,
		];

		$params['slick-zIndex'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-carousel-slick-zIndex',
			'default' => 1000,
		];

		return $params;
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

		$resourceFormatter->registerResources( [
			'ext.srf.carousel',
			'ext.srf.carousel.module'
		] );

		// or ...
		// SMWOutputs::requireResource( 'ext.srf.carousel' );

		// print_r($data);

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
				if ( !$captionValue && $printReqLabels[ $name ] === '_txt' ) {
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
					$titleValue = $value['fulltext'];
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

			$innerContent = Html::rawElement( 'img', [
					'src' => $imageValue,
					'alt' => ( $titleValue ?? $captionValue ? strip_tags( $captionValue ) : $title_->getText() ),
					'style' => "max-width:" . ( !empty( $this->params['width'] ) ? $this->params['width'] : "none" ),
					'class' => "slick-slide-content img"
				] );

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

		$attr = [ 'class' => 'slick-slider' ];

		if ( !empty( $this->params['width'] ) ) {
			$attr['style'] = "width:" . $this->params['width'];
		}

		$slick_attr = [];
		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'slick-')  === 0 ) {
				$slick_attr[ str_replace( 'slick-', '', $key ) ] = $value ;
			}
		}

		$attr['data-slick'] = json_encode( $slick_attr );

		return Html::rawElement(
				'div',
				$attr,
				implode( $items )
			);
	}

	protected function getFirstValid( $array ) {
		foreach( $array as $value ) {
			if ( !empty( $value ) ) {
				return $value;
			}
		}
		return null;
	}

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

