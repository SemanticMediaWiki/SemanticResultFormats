<?php

namespace SRF\DataTables;

use ApiBase;
use ParamProcessor\ParamDefinition;
use SMW\DataValueFactory;
use SMWPrintRequest;
use SMWQueryProcessor;
use SMWQuery;
use SMW\Services\ServicesFactory;
use SRF\DataTables;

/**
 * @author thomas-topway-it
 * @credits Stephan Gambke (SRFSlideShowApi)
 */
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

/*

{
    "draw": 3,
    "columns": [
        {
            "data": {
                "_": "0.display",
                "sort": "0.@data-order",
                "type": "0.@data-order"
            },
            "name": "",
            "searchable": true,
            "orderable": true,
            "search": {
                "value": "",
                "regex": false
            }
        },
        {
            "data": {
                "_": "1.display",
                "sort": "1.@data-order",
                "type": "1.@data-order"
            },
            "name": "",
            "searchable": true,
            "orderable": true,
            "search": {
                "value": "",
                "regex": false
            }
        },
        {
            "data": 2,
            "name": "",
            "searchable": true,
            "orderable": true,
            "search": {
                "value": "",
                "regex": false
            }
        },
        {
            "data": 3,
            "name": "",
            "searchable": true,
            "orderable": true,
            "search": {
                "value": "",
                "regex": false
            }
        },
        {
            "data": 4,
            "name": "",
            "searchable": true,
            "orderable": true,
            "search": {
                "value": "",
                "regex": false
            }
        }
    ],
    "order": [
        {
            "column": 0,
            "dir": "asc"
        }
    ],
    "start": 0,
    "length": 81,
    "search": {
        "value": "",
        "regex": false
    }
}
*/
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
		$queryParams = [];

		foreach ( $paramDefinitions as $def ) {
			$queryParams[$def->getName()] = $def->getDefault();
		}

		$printoutsRaw = json_decode( $requestParams['printouts'], true );

		$columnIndex = $datatableData['order'][0]['column'];

		// or $printoutsRaw[$columnIndex][1]
		$columnSortName = $datatableData['columns'][$columnIndex]['data'];

		// add/set specific parameters for this call
		$queryParams = array_merge(
			$queryParams,
			[
				// *** important !!
				'format' => 'datatables',

				"ajax" => "ajax",
				// @see https://datatables.net/manual/server-side
				"limit" => $datatableData['length'],	// max( $datatableData['length'], $settings['defer-each'] ),
				"offset" => $datatableData['start'],
				"sort" => $columnSortName,
				"order" => $datatableData['order'][0]['dir']
			]
		);

		// A bit of a hack since the parser isn't run, avoids [[SMW::off]]/[[SMW::on]]
		$queryParams['import-annotation'] = 'true';

		// transform query parameters into format suitable for SMWQueryProcessor
		$queryParams = SMWQueryProcessor::getProcessedParams( $queryParams, [] );

		// build array of printouts
		$printouts = [];
		foreach ( $printoutsRaw as $printoutData ) {

			// if printout mode is PRINT_PROP
			if ( $printoutData[0] == SMWPrintRequest::PRINT_PROP ) {
				// create property from property key
				$data = DataValueFactory::getInstance()->newPropertyValueByLabel( $printoutData[1] );
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

		//  @TODO search string
		// if ( !empty($requestParams['datatableData']['search']['value'] ) ) {
			// ... 
		// }

		$query = SMWQueryProcessor::createQuery(
			$requestParams['query'],
			$queryParams,
			SMWQueryProcessor::INLINE_QUERY,
			'',
			$printouts
		);

		$applicationFactory = ServicesFactory::getInstance();
		$results = $applicationFactory->getStore()->getQueryResult( $query );

		// or SMW_OUTPUT_RAW
		$res = $printer->getResult( $results, $queryParams, SMW_OUTPUT_FILE );

		// @see https://datatables.net/extensions/scroller/examples/initialisation/server-side_processing.html
		$ret = [
			'draw' => $datatableData['draw'],
			'data' => $res,
			'recordsTotal' => $settings['count'],
			'recordsFiltered' => $settings['count']
		];

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
			'printouts' => [
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
