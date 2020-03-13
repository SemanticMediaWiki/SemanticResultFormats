<?php

use ParamProcessor\ParamDefinition;
use SMW\DataValueFactory;

/**
 * API module to retrieve formatted results for a given page, printouts and template.
 *
 * @author Stephan Gambke
 * @ingroup SemanticResultFormats
 */
class SRFSlideShowApi extends ApiBase {

	/**
	 * Evaluates the parameters, performs the query, and sets up the result.
	 *
	 * The result data is stored in the ApiResult object available through getResult().
	 */
	public function execute() {

		// get request parameters
		$requestParams = $this->extractRequestParams();

		$title = Title::newFromID( $requestParams['pageid'] )->getPrefixedText();

		$rp = new SMWListResultPrinter( 'template', true );

		// get defaults of parameters for the 'template' result format as array of ParamDefinition
		$paramDefinitions = ParamDefinition::getCleanDefinitions( $rp->getParamDefinitions( [] ) );

		// transform into normal key-value array
		$queryParams = [];

		foreach ( $paramDefinitions as $def ) {
			$queryParams[$def->getName()] = $def->getDefault();
		}

		// add/set specific parameters for this call
		$queryParams = array_merge(
			$queryParams,
			[
				'format' => 'template',
				'template' => $requestParams['template'],
				'mainlabel' => '',
				'sort' => '',
				'order' => '',
				'intro' => null,
				'outro' => null,
				'searchlabel' => null,
				'link' => null,
				'default' => null,
				'headers' => null,
				'introtemplate' => '',
				'outrotemplate' => '',
			]
		);

		// A bit of a hack since the parser isn't run, avoids [[SMW::off]]/[[SMW::on]]
		$queryParams['import-annotation'] = 'true';

		// transform query parameters into format suitable for SMWQueryProcessor
		$queryParams = SMWQueryProcessor::getProcessedParams( $queryParams, [] );

		// build array of printouts

		$printoutsRaw = json_decode( $requestParams['printouts'], true );
		$printouts = [];

		foreach ( $printoutsRaw as $printoutData ) {

			// if printout mode is PRINT_PROP
			if ( $printoutData[0] == SMWPrintRequest::PRINT_PROP ) {
				// create property from property key
				$data = DataValueFactory::getInstance()->newPropertyValueByLabel( $printoutData[2] );
			} else {
				$data = null;
			}

			// create printrequest from request mode, label, property name, output format, parameters
			$printouts[] = new SMWPrintRequest(
				$printoutData[0],
				$printoutData[1],
				$data,
				$printoutData[3],
				$printoutData[4]
			);
		}

		// query SMWQueryProcessor and set query result as API call result
		$query = SMWQueryProcessor::createQuery(
			'[[' . $title . ']]',
			$queryParams,
			SMWQueryProcessor::INLINE_QUERY,
			'',
			$printouts
		);

		$this->getResult()->addValue(
			null,
			$requestParams['pageid'],
			SMWQueryProcessor::getResultFromQuery( $query, $queryParams, SMW_OUTPUT_HTML, SMWQueryProcessor::INLINE_QUERY )
		);
	}

	/**
	 * Returns the description string for this module
	 *
	 * @return mixed string or array of strings
	 */
	protected function getDescription() {
		return [
			'API module used by the SlideShow result printer to retrieve formatted results.',
			'This module is should not be called directly.'
		];
	}

	/**
	 * Returns usage examples for this module. Return false if no examples are available.
	 *
	 * @return bool|string|array
	 */
	protected function getExamples() {
		return false;
	}

	public function getHelpUrls() {
		return 'http://semantic-mediawiki.org/wiki/Help:Slideshow_format';
	}

	/**
	 * Returns an array of allowed parameters
	 *
	 * @return array|bool
	 */
	protected function getAllowedParams() {
		return [
			'pageid' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_REQUIRED => true,
			],
			'template' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_REQUIRED => true,
			],
			'printouts' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => '[]',
			],
		];
	}

	/**
	 * Returns an array of parameter descriptions.
	 * Don't call this function directly: use getFinalParamDescription() to
	 * allow hooks to modify descriptions as needed.
	 *
	 * @return array|bool False on no parameter descriptions
	 */
	protected function getParamDescription() {
		return [
			'pageid' => 'Id of the page (subject) to be displayed',
			'template' => 'Template to use for formatting',
			'printouts' => 'Printouts to send to the template',
		];
	}

	/**
	 * Returns a string that identifies the version of the extending class.
	 * Typically includes the class name, the svn revision, timestamp, and
	 * last author. Usually done with SVN's Id keyword
	 *
	 * @return string
	 */
	public function getVersion() {
		global $srfgIP;
		$gitSha1 = SpecialVersion::getGitHeadSha1( $srfgIP );
		return __CLASS__ . '-' . SRF_VERSION . ( $gitSha1 !== false ) ? ' (' . substr( $gitSha1, 0, 7 ) . ')' : '';
	}

}
