<?php
/**
 * File holding the SRF_SlideShow class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

/**
 * The SRF_SlideShow class.
 *
 * @ingroup SemanticResultFormats
 */
class SRFSlideShow extends SMWResultPrinter {

	/**
	 * Return serialised results in specified format.
	 * Implemented by subclasses.
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {

		$html = '';
		$id = uniqid();

		// build an array of article IDs contained in the result set
		$objects = array();
		foreach ( $res->getResults() as $key => $object ) {

			$objects[] = array( $object->getTitle()->getArticleId() );

			$html .= $key . ': ' . $object->getSerialization() . "<br>\n";
		}

		// build an array of data about the printrequests
		$printrequests = array();
		foreach ( $res->getPrintRequests() as $key => $printrequest ) {
			$data = $printrequest->getData();
			if ( $data instanceof SMWPropertyValue ) {
				$name = $data->getDataItem()->getKey();
			} else {
				$name = null;
			}
			$printrequests[] = array(
				$printrequest->getMode(),
				$printrequest->getLabel(),
				$name,
				$printrequest->getOutputFormat(),
				$printrequest->getParameters(),
			);

		}

		// write out results and query params into JS arrays
		// Define the srf_filtered_values array
		SMWOutputs::requireScript( 'srf_slideshow', Html::inlineScript(
				'srf_slideshow = {};'
			)
		);

		SMWOutputs::requireScript( 'srf_slideshow' . $id, Html::inlineScript(
				'srf_slideshow["' . $id . '"] = ' . json_encode(
					array(
						$objects,
						$this->params['template'],
						$this->params['delay'] * 1000,
						$this->params['height'],
						$this->params['width'],
						$this->params['nav controls'],
						$this->params['effect'],
						json_encode( $printrequests ) ,
					)
				) . ';'
			)
		);

		SMWOutputs::requireResource( 'ext.srf.slideshow' );

		if ( $this->params['nav controls'] ) {
			SMWOutputs::requireResource( 'jquery.ui.slider' );
		}

		return Html::element(
			'div',
			array(
				'id' => $id,
				'class' => 'srf-slideshow ' . $id . ' ' . $this->params['class']
			)
		);
	}

	/**
	 * Check whether a "further results" link would normally be generated for this
	 * result set with the given parameters.
	 *
	 * @param SMWQueryResult $results
	 *
	 * @return boolean
	 */
	protected function linkFurtherResults( SMWQueryResult $results ) {
		return false;
	}

	/**
	 * A function to describe the allowed parameters of a query using
	 * any specific format - most query printers should override this
	 * function.
	 *
	 * @since 1.5
	 *
	 * @return array of Parameter
	 */
	public function getParameters() {

		$params['template'] = new Parameter( 'template' );
		$params['template']->setMessage( 'smw_paramdesc_template' );
		$params['template']->setDefault( '' );

		// TODO: Implement named args
//		$params['named args'] = new Parameter( 'named args', Parameter::TYPE_BOOLEAN, false );
//		$params['named args']->setMessage( 'smw_paramdesc_named_args' );

		$params['class'] = new Parameter( 'class' );
		$params['class']->setMessage( 'smw-paramdesc-class' );
		$params['class']->setDefault( '' );

		$params['delay'] = new Parameter( 'delay', Parameter::TYPE_INTEGER );
		$params['delay']->setMessage( 'srf-paramdesc-delay' );
		$params['delay']->setDefault( '5' );

		$params['height'] = new Parameter( 'height' );
		$params['height']->setMessage( 'srf-paramdesc-height' );
		$params['height']->setDefault( '100px' );

		$params['width'] = new Parameter( 'width' );
		$params['width']->setMessage( 'srf-paramdesc-width' );
		$params['width']->setDefault( '200px' );

		$params['nav controls'] = new Parameter( 'nav controls', Parameter::TYPE_BOOLEAN );
		$params['nav controls']->setMessage( 'srf-paramdesc-navigation-controls' );
		$params['nav controls']->setDefault( false );

		$params['effect'] = new Parameter( 'effect' );
		$params['effect']->setMessage( 'srf-paramdesc-effect' );
		$params['effect']->setDefault( 'none' );
		$params['effect']->addCriteria( new CriterionInArray(
				'none',
				'slide left',
				'slide right',
				'slide up',
				'slide down',
				'fade',
				'hide'
		) );

		return $params;
	}

	/**
	 * Handles Ajax call
	 * @param type $pageId
	 * @param type $template
	 * @param type $printrequests
	 * @return type
	 */
	static public function handleGetResult( $pageId, $template, $printrequests ) {

		$title = Title::newFromID( $pageId )->getPrefixedText();

		$rp = new SMWListResultPrinter( 'template', true );

		$validatorParams = $rp->getValidatorParameters();
		$params = array();

		foreach ( $validatorParams as $key => $param ) {
			$params[ $param->getName() ] = $param->getValue();
		}

		$params = array_merge( $params, array(
			'format' => 'template',
			'template' => "$template",
			'mainlabel' => '',
			'sort' => array(),
			'order' => array(),
			'intro' => null,
			'outro' => null,
			'searchlabel' => null,
			'link' => null,
			'default' => null,
			'headers' => null,
			'introtemplate' => '',
			'outrotemplate' => '',
			) );

		$p = json_decode( $printrequests, true );
		$extraprintouts = array();

		foreach ( $p as $key => $prData ) {

			// if printout mode is PRINT_PROP
			if ( $prData[0] == SMWPrintRequest::PRINT_PROP ) {
				// create property from property key
				$data = SMWPropertyValue::makeUserProperty( $prData[2] );
			} else {
				$data = null;
			}

			// create printrequest from request mode, label, property name, output format, parameters
			$extraprintouts[] = new SMWPrintRequest( $prData[0], $prData[1], $data, $prData[3], $prData[4] );
		}

		return SMWQueryProcessor::getResultFromQueryString( '[[' . $title . ']]', $params, $extraprintouts, SMW_OUTPUT_HTML, SMWQueryProcessor::INLINE_QUERY );

	}

}
