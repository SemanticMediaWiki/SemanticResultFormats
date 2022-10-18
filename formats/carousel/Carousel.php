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
use SMWQueryResult as QueryResult;

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

		return $params;
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {
		$resourceFormatter = new ResourceFormatter();
		$data = $resourceFormatter->getData( $res, $outputmode, $this->params );

		$this->isHTML = true;
		// $id = $resourceFormatter->session();

		// Encode data object
		// $resourceFormatter->encode( $id, $data );

		// Init RL module
		$resourceFormatter->registerResources( [
			'ext.srf.carousel',
			'ext.srf.carousel.module'
		] );

		// SMWOutputs::requireResource( 'ext.srf.carousel' );

		//print_r($data['query']['result']);

		$elements = [];
		foreach( $data['query']['result']['results'] as $value ) {
			$inner_elements = [];

			foreach( $value['printouts'] as $name => $values ) {
				foreach( $values as $printout_value ) {

					if ( is_array( $printout_value ) ) {
						if ( array_key_exists( 'fullurl', $printout_value ) && $printout_value['namespace'] == NS_FILE ) {
							$title = \Title::newFromText( $printout_value['fulltext'], NS_FILE );
							$wikiFilePage = new \WikiFilePage( $title );
							$file = $wikiFilePage->getFile();
							if ( $file ) {
								$inner_elements[] = Html::rawElement( 'img', [
									'src' => $file->getUrl(),
									'class' => "slick-slide-content $name"
								] );
							}
						}
					
					} else {
						$inner_elements[] = Html::rawElement( 'div', [
							'class' => "slick-slide-content $name"
						], $printout_value );

					}
				}
			}

			$elements[] = Html::rawElement(
				'div',
				[
					'class' => 'slick-slide',
					// mainlabel
					'data-title' => ( array_key_exists( 'fulltext', $value ) ? $value['fulltext'] : '' ),
					'data-url' => ( array_key_exists( 'fullurl', $value ) ? $value['fullurl'] : '' )
				],
				implode( $inner_elements )
			);
		}

		return Html::rawElement(
				'div',
				[
					'class' => 'slick-slider'
				],
				implode( $elements )
			);
	}
}

