<?php
/**
 * SRF DataTables and SMWAPI.
 *
 * @see http://datatables.net/
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it for KM-A
 * @credits Stephan Gambke (SRFSlideShowApi)
 */

namespace SRF\DataTables;

use ApiBase;
use ParamProcessor\ParamDefinition;
use SMW\DataValueFactory;
use SMWPrintRequest;
use SMWQueryProcessor;
use SMWQuery;
use SMW\Services\ServicesFactory;
use SRF\DataTables;

class Api extends ApiBase {

	/**
	 * Evaluates the parameters, performs the query, and sets up the result.
	 *
	 * The result data is stored in the ApiResult object available through getResult().
	 */
	public function execute() {
		// get request parameters
		$requestParams = $this->extractRequestParams();

		// @see https://datatables.net/reference/option/ajax
		$datatableData = json_decode( $requestParams['datatable'], true );
		$settings = json_decode( $requestParams['settings'], true );

		if ( empty( $datatableData['length'] ) ) {
			$datatableData['length'] = $settings['defer-each'];
		}

		if ( empty( $datatableData['start'] ) ) {
			$datatableData['start'] = 0;
		}

		if ( empty( $datatableData['draw'] ) ) {
			$datatableData['draw'] = 0;
		}

		$printer = new Datatables( 'datatables', true );

		// get defaults of parameters for the 'datatable' result format as array of ParamDefinition
		$paramDefinitions = ParamDefinition::getCleanDefinitions( $printer->getParamDefinitions( [] ) );

		// transform into normal key-value array
		$parameters = [];

		foreach ( $paramDefinitions as $def ) {
			$parameters[$def->getName()] = $def->getDefault();
		}

		$printoutsRaw = json_decode( $requestParams['printouts'], true );

		// add/set specific parameters for this call
		$parameters = array_merge(
			$parameters,
			[
				// *** important !!
				'format' => 'datatables',

				"apicall" => "apicall",
				// @see https://datatables.net/manual/server-side
				// array length will be sliced client side if greater
				// than the required datatables length
				"limit" => max( $datatableData['length'], $settings['defer-each'] ),
				"offset" => $datatableData['start'],

				"sort" => implode( ',', array_map( static function ( $value ) use( $datatableData ) {
					return $datatableData['columns'][$value['column']]['name'];
					 }, $datatableData['order'] ) ),

				"order" => implode( ',', array_map( static function ( $value ) {
					return $value['dir'];
					 }, $datatableData['order'] ) )

			]
		);

		// A bit of a hack since the parser isn't run, avoids [[SMW::off]]/[[SMW::on]]
		$parameters['import-annotation'] = 'true';

		// transform query parameters into format suitable for SMWQueryProcessor
		$queryParams = SMWQueryProcessor::getProcessedParams( $parameters, [] );

		// @TODO use printrequests for printouts as well
		// printrequests seems to lack of the "parameters"
		// parameter only

		$hasMainlabel = array_key_exists( 'mainlabel', $parameters );

		// build array of printouts
		$printouts = [];
		$dataValueFactory = DataValueFactory::getInstance();
		foreach ( $printoutsRaw as $printoutData ) {
			
			// create property from property key
			if ( $printoutData[0] === SMWPrintRequest::PRINT_PROP ) {
				$data = $dataValueFactory->newPropertyValueByLabel( $printoutData[1] );
			} else {
				$data = null;
				if  ( $hasMainlabel && trim( $parameters['mainlabel'] ) === '-' ) {	
					continue;
				}
				// match something like |?=abc |+ datatables-columns.type=any-number |+template=mytemplate
			}

			// create printrequest from request mode, label, property name, output format, parameters
			$printouts[] = new SMWPrintRequest(
				$printoutData[0],	// mode
				$printoutData[1],	// (canonical) label
				$data,				// property name
				$printoutData[3],	// output format
				$printoutData[4]	// parameters
			);

		}

		// SMWQueryProcessor::addThisPrintout( $printouts, $parameters );

		$printrequests = json_decode( $requestParams['printrequests'], true );
		$columnDefs = json_decode( $requestParams['columndefs'], true );

		$getColumnAttribute = function( $label, $attr ) use( $columnDefs ) {
			foreach ( $columnDefs as $value ) {
				if ( $value['name'] === $label && array_key_exists( $attr, $value ) ) {
					return $value[$attr];
				}
			}
			return null;
		};

		// filter the query
		$queryDisjunction = [];
		$allowedTypes = [ '_wpg', '_txt', '_cod', '_uri' ];
		if ( !empty( $datatableData['search']['value'] ) ) {
			foreach ( $printoutsRaw as $key => $value ) {
				$printrequest = $printrequests[$key];

				if ( !in_array( $printrequest['typeid'], $allowedTypes ) ) {
					continue;
				}

				// $value['key'] === '' is the mainlabel, is this always reliable ?
				$label = ( $printrequest['key'] !== '' ? $value[1] : '' );
				$searchable = $getColumnAttribute( $label, 'searchable' );

				if ( $searchable === null || $searchable === true ) {
					$queryDisjunction[] = '[[' . ( $label !== '' ? $label . '::' : '' ) . '~*' . $datatableData['search']['value'] . '*]]';
				}
			}
		}

		$queryConjunction = [];
		foreach ( $printoutsRaw as $key => $value ) {
			if ( !empty( $datatableData['searchPanes'][$key] ) ) {
				$printrequest = $printrequests[$key];
				$label = ( $printrequest['key'] !== '' ? $value[1] : '' );
				// @TODO consider combiner
				// https://www.semantic-mediawiki.org/wiki/Help:Unions_of_results#User_manual
				$queryConjunction[] = '[[' . ( $label !== '' ? $label . '::' : '' ) . implode( '||', $datatableData['searchPanes'][$key] ) . ']]';
			}
		}

		global $smwgQMaxSize;

		if ( !count( $queryDisjunction ) ) {
			$queryDisjunction = [''];
		}

		$query = $requestParams['query'] . implode( '', $queryConjunction );
		
		$conditions = array_map( static function( $value ) use ( $query ) {
			return $query . $value;
		}, $queryDisjunction );

		// @TODO get query size as in class Conjunction
		$smwgQMaxSize = 32;

		$queryStr =	implode( 'OR', $conditions );

		// trigger_error('queryStr ' . $queryStr);

		$log['queryStr '] = $queryStr;

		$query = SMWQueryProcessor::createQuery(
			$queryStr,
			$queryParams,
			SMWQueryProcessor::INLINE_QUERY,
			'',
			$printouts
		);

		// $size = $query->getDescription()->getSize();
		
		// $smwgQMaxSize = max( $smwgQMaxSize, $size );
		// trigger_error('smwgQMaxSize ' . $smwgQMaxSize);


		$applicationFactory = ServicesFactory::getInstance();
		$queryEngine = $applicationFactory->getStore();
		$results = $queryEngine->getQueryResult( $query );

		// or SMW_OUTPUT_RAW
		$res = $printer->getResult( $results, $queryParams, SMW_OUTPUT_FILE );

		global $smwgQMaxLimit, $smwgQMaxInlineLimit;
		
		// get count
		if ( !empty( $datatableData['search']['value'] ) || count( $queryConjunction ) ) {
			$queryDescription = $query->getDescription();
			$queryCount = new \SMWQuery( $queryDescription );
			$queryCount->setLimit( min( $smwgQMaxLimit, $smwgQMaxInlineLimit ) );
			$queryCount->setQuerySource( \SMWQuery::MODE_COUNT );
			$queryResult = $queryEngine->getQueryResult( $queryCount );
			$count = $queryResult->getCount();

		} else {
			$count = $settings['count'];
		}

		// @see https://datatables.net/extensions/scroller/examples/initialisation/server-side_processing.html
		$ret = [
			'draw' => $datatableData['draw'],
			'data' => $res,
			'recordsTotal' => $settings['count'],
			'recordsFiltered' => $count,
		];

		if ( $settings['displayLog'] ) {
			$ret['log'] = $log;
		}

		$this->getResult()->addValue( null, "datatables-json", $ret );
	}

	/**
	 * Returns the description string for this module
	 *
	 * @return mixed string or array of strings
	 */
	protected function getDescription() {
		return [
			'API module used by the Datatables (v2) result printer to retrieve formatted results.',
			'This module should not be called directly.'
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
		return 'http://semantic-mediawiki.org/wiki/Help:Datatables_format';
	}

	/**
	 * Returns an array of allowed parameters
	 *
	 * @return array|bool
	 */
	protected function getAllowedParams() {
		return [
			'query' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'columndefs' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'printouts' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'printrequests' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'settings' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'datatable' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
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
			'query' => 'Original query',
			'printouts' => 'Printouts used in the original query',
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
