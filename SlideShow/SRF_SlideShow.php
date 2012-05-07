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


	var $mDelay = 5;
	var $mHeight = '100px';
	var $mWidth = '200px';
	var $mNavButtons = false;
	var $mEffect = 'none';

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
			$printrequests[] = array(
				$printrequest->getMode(),
				$printrequest->getLabel(),
				null,
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
						$this->params['nav buttons'],
						$this->params['effect'],
						json_encode( $printrequests ) ,
					)
				) . ';'
			)
		);

		SMWOutputs::requireResource( 'ext.srf.slideshow' );

		return HTML::Element( 'div', array( 'id' => $id, 'class' => 'slideshow ' . $id ) );
	}

	/**
	 * Read an array of parameter values given as key-value-pairs and
	 * initialise internal member fields accordingly. Possibly overwritten
	 * (extended) by subclasses.
	 *
	 * @since 1.6
	 *
	 * @param array $params
	 * @param $outputmode
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		// // Set in SMWResultPrinter:
		// $this->mIntro = $params['intro'];
		// $this->mOutro = $params['outro'];
		// $this->mSearchlabel = $params['searchlabel'] === false ? null : $params['searchlabel'];
		// $this->mLinkFirst = true | false;
		// $this->mLinkOthers = true | false;
		// $this->mDefault = str_replace( '_', ' ', $params['default'] );
		// $this->mShowHeaders = SMW_HEADERS_HIDE | SMW_HEADERS_PLAIN | SMW_HEADERS_SHOW;

		// SlideShow specific params:
		$this->mDelay = $params['delay'];
		$this->mHeight = $params['height'];
		$this->mWidth = $params['width'];
		$this->mNavButtons = $params['nav buttons'];
		$this->mEffect = $params['effect'];

	}

	/**
	 * Check whether a "further results" link would normally be generated for this
	 * result set with the given parameters. Individual result printers may decide to
	 * create or hide such a link independent of that, but this is the default.
	 *
	 * @return boolean
	 */
	protected function linkFurtherResults( $results ) {
		return $this->mInline && $results->hasFurtherResults() && $this->mSearchlabel !== '';
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

		$params['delay'] = new Parameter( 'delay', Parameter::TYPE_INTEGER );
		$params['delay']->setMessage( 'srf-paramdesc-template' );
		$params['delay']->setDefault( '5' );

		$params['height'] = new Parameter( 'height' );
		$params['height']->setMessage( 'srf-paramdesc-height' );
		$params['height']->setDefault( '100px' );

		$params['width'] = new Parameter( 'width' );
		$params['width']->setMessage( 'srf-paramdesc-width' );
		$params['width']->setDefault( '200px' );

		$params['nav buttons'] = new Parameter( 'nav buttons', Parameter::TYPE_BOOLEAN );
		$params['nav buttons']->setMessage( 'srf-paramdesc-navigation-buttons' );
		$params['nav buttons']->setDefault( false );

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

			if ( $prData[0] == SMWPrintRequest::PRINT_PROP ) {
				$data = SMWPropertyValue::makeUserProperty( $prData[1] );
			} else {
				$data = null;
			}

			$extraprintouts[] = new SMWPrintRequest( $prData[0], $prData[1], $data, $prData[3], $prData[4] );
		}

		return SMWQueryProcessor::getResultFromQueryString( '[[' . $title . ']]', $params, $extraprintouts, SMW_OUTPUT_HTML, SMWQueryProcessor::INLINE_QUERY );

	}

}
