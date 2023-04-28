<?php

/**
 * SRF DataTables and SMWAPI.
 *
 * @see http://datatables.net/
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it for KM-A
 * @credits mwjames
 */

namespace SRF;

use Html;
use RequestContext;
use SMW\DataValueFactory;
use SMW\DataTypeRegistry;
use SMW\ResultPrinter;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMW\Exception\PredefinedPropertyLabelMismatchException;
use SMW\Message;
use SMW\SQLStore\SQLStore;
use SMW\SQLStore\TableBuilder\FieldType;
use SMW\QueryFactory;
use SMW\Query\PrintRequest;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMWDataItem as DataItem;
use SMWPrintRequest;
use SMWPropertyValue;
use SMWQueryProcessor;
use SMWQueryResult as QueryResult;

class DataTables extends ResultPrinter {

	/*
	 * camelCase params
	 */
	protected static $camelCaseParamsKeys = [];

	private $prefixParameterProcessor;

	private $printoutsParameters = [];

	private $printoutsParametersOptions = [];

	private $parser;

	/**
	 * @var boolean
	 */
	private $recursiveAnnotation = false;

	private $queryEngineFactory;

	private $store;

	private $query;

	private $connection;

	private $queryFactory;

	private $searchPanesLog = [];

	private $useAjax;

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return $this->msg( 'srf-printername-datatables' )->text();
	}

	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['class'] = [
			'type' => 'string',
			'message' => 'smw-paramdesc-table-class',
			'default' => '',
		];

		// use instead |?=my label |+ template = my template
		$params['mainlabel-template'] = [
			'type' => 'string',
			'message' => 'smw-paramdesc-table-mainlabel-template',
			'default' => '',
		];

		$params['noajax'] = [
			'type' => 'boolean',
			'message' => 'smw-paramdesc-table-noajax',
			'default' => false,
		];

		$params['sep'] = [
			'type' => 'string',
			'message' => 'smw-paramdesc-sep',
			'default' => ',&#32;',
			//'default' => '&#32;',
		];

		$params['prefix'] = [
			'message' => 'smw-paramdesc-prefix',
			'default' => 'none',
			'values' => [ 'all', 'subject', 'none', 'auto' ],
		];

		$params['defer-each'] = [
			'type' => 'integer',
			'message' => 'smw-paramdesc-defer-each',	
			// $GLOBALS['smwgQMaxLimit']
			'default' => 0,
		];

		// *** only used internally, do not use in query
		$params['apicall'] = [
			'type' => 'string',
			'message' => 'smw-paramdesc-apicall',
			'default' => "",
		];

		//////////////// datatables native options https://datatables.net/reference/option/

		$params['datatables-autoWidth'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-deferRender'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-info'] = [
		 	'type' => 'boolean',
		 	'message' => 'srf-paramdesc-datatables-library-option',
		 	'default' => true,
		];

		$params['datatables-lengthChange'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => true,
		];

		$params['datatables-ordering'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => true,
		];

		$params['datatables-paging'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => true,
		];

		$params['datatables-processing'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-scrollX'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-scrollY'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '',
		];

		$params['datatables-searching'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => true,
		];

		// $params['datatables-serverSide'] = [
		// 	'type' => 'boolean',
		// 	'message' => 'srf-paramdesc-datatables-library-option',
		// 		'default' => false,
		// ];

		$params['datatables-stateSave'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-displayStart'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 0,
		];

		$params['datatables-pagingType'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 'full_numbers',
		];

		$params['datatables-pageLength'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 20,
		];

		$params['datatables-lengthMenu'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '10, 20, 50, 100, 200',
		];

		$params['datatables-scrollCollapse'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-scroller'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-scroller.displayBuffer'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 50,
		];

		$params['datatables-scroller.loadingIndicator'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-buttons'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '',
		];

		$params['datatables-dom'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 'lfrtip',
		];

		$params['datatables-fixedHeader'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-responsive'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => true,
		];

		$params['datatables-keys'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		//////////////// datatables columns

		// only the options whose value has a sense to
		// use for all columns, otherwise use (for single printouts)
		// |?printout name |+ datatables-columns.type = string

		$params['datatables-columns.type'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '',
		];

		$params['datatables-columns.width'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '',
		];

		//////////////// datatables searchPanes

		$params['datatables-searchPanes'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-searchPanes.initCollapsed'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-searchPanes.collapse'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => true,
		];

		$params['datatables-searchPanes.columns'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '',
		];

		$params['datatables-searchPanes.threshold'] = [
			'type' => 'float',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 0.6,
		];

		// ***custom parameter
		$params['datatables-searchPanes.minCount'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 1,
		];

		// ***custom parameter
		$params['datatables-searchPanes.htmlLabels'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		// ***custom parameter
		// use the following as long as searchPanes created server-side
		// are used always client-side, for which we need to use
		// the trick mentioned below (this isn't, however, strictly
		// necessary)
		// $params['datatables-searchPanes.forceClient'] = [
		// 	'type' => 'boolean',
		// 	'message' => 'srf-paramdesc-datatables-library-option',
		// 	'default' => false,
		// ];

		// ***custom parameter
		// @TODO sort panes after rendering using the following
		// https://github.com/DataTables/SearchPanes/blob/master/src/SearchPane.ts

		// $params['datatables-searchPanes.defaultOrder'] = [
		// 	'type' => 'string',
		// 	'message' => 'srf-paramdesc-datatables-library-option',
		// 	// label-sort, label-rsort, count-asc, count-desc
		// 	'default' => 'label-sort',
		// ];
		
		// only single value
		$params['datatables-columns.searchPanes.show'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => null,
		];

		// *** workaround to allow camelCase parameters
		$ret = [];
		foreach ( $params as $key => $value ) {
			$strlower = strtolower($key);
			self::$camelCaseParamsKeys[$strlower] = $key;
			$ret[$strlower] = $value;
		}

		return $ret;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildResult( QueryResult $results ) {
		$this->isHTML = true;
		$this->hasTemplates = false;

		$this->parser = $this->copyParser();

		$outputMode = ( $this->params['apicall'] !== "apicall" ? SMW_OUTPUT_HTML : $this->outputMode );

		// Get output from printer:
		$result = $this->getResultText( $results, $outputMode );

		// $outputMode = SMW_OUTPUT_WIKI;

		if ( $outputMode !== SMW_OUTPUT_FILE ) {
			$result = $this->handleNonFileResult( $result, $results, $outputMode );
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function handleNonFileResult( $result, QueryResult $results, $outputmode ) {

		// append errors
		$result .= $this->getErrorString( $results );

		if ( $this->recursiveTextProcessor === null ) {
			$this->recursiveTextProcessor = new RecursiveTextProcessor();
		}

		$this->recursiveTextProcessor->uniqid();

		$this->recursiveTextProcessor->setMaxRecursionDepth(
			self::$maxRecursionDepth
		);

		$this->recursiveTextProcessor->transcludeAnnotation(
			$this->transcludeAnnotation
		);

		$this->recursiveTextProcessor->setRecursiveAnnotation(
			$this->recursiveAnnotation
		);

		// Apply intro parameter
		if ( ( $this->mIntro ) && ( $results->getCount() > 0 ) ) {
			$result = $this->parser->recursiveTagParseFully( $this->mIntro ) . $result;
		}

		// Apply outro parameter
		if ( ( $this->mOutro ) && ( $results->getCount() > 0 ) ) {
			$result = $result . $this->parser->recursiveTagParseFully( $this->mOutro );
		}

		// Preprocess embedded templates if needed
		if ( ( !$this->isHTML ) && ( $this->hasTemplates ) ) {
			$result = $this->recursiveTextProcessor->recursivePreprocess( $result );
		}

		if ( ( !$this->isHTML ) && ( $outputmode == SMW_OUTPUT_HTML ) ) {
			$result = $this->recursiveTextProcessor->recursiveTagParse( $result );
		}

		if ( $this->mShowErrors && $this->recursiveTextProcessor->getError() !== [] ) {
			$result .= Message::get( $this->recursiveTextProcessor->getError(), Message::TEXT, Message::USER_LANGUAGE );
		}

		$this->recursiveTextProcessor->releaseAnnotationBlock();

		return [ $result, 'isHTML' => true ];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {
		$this->query = $res->getQuery();
		$this->store = $res->getStore();

		if ( class_exists( '\\SMW\Query\\ResultPrinters\\PrefixParameterProcessor' ) ) {
			$this->prefixParameterProcessor = new \SMW\Query\ResultPrinters\PrefixParameterProcessor( $this->query, $this->params['prefix'] );
		}

		if ( $this->params['apicall'] === "apicall" ) {
 			return $this->getResultJson( $res, $outputmode );
		}

		// @see src/ResourceFormatter.php -> getData
		$ask = $this->query->toArray();

		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'datatables-')  === 0 ) {
				continue;
			}
			if ( is_string( $value ) || is_int( $value ) || is_bool( $value ) ) {
				$ask['parameters'][$key] = $value;
			}
		}

		$printRequests = $res->getPrintRequests();
		$printouts = $this->getPrintouts( $printRequests );
		
		$headerList = [];
		foreach ( $printouts as $printout ) {
			$headerList[] = ( $printout[0] !== SMWPrintRequest::PRINT_THIS ? $printout[1] : '' );
		}

		// @TODO put inside $this->formatOptions
		// and remove from $tableAttrs
		$datatablesOptions = [];
		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'datatables-')  === 0 ) {
				$datatablesOptions[ str_replace( 'datatables-', '', self::$camelCaseParamsKeys[$key] ) ] = $value ;
			}
		}

		$formattedOptions = $this->formatOptions( $datatablesOptions );

		// @TODO use only one between printouts and printrequests
		$resultArray = $res->toArray();
		$printrequests = $resultArray['printrequests'];

		$result = $this->getResultJson( $res, $outputmode );

		$this->useAjax = $this->query->getOption( 'useAjax' );
		
		// @TODO use this instead than the block below as long as 
		// the following trick https://github.com/Knowledge-Wiki/SemanticResultFormats/blob/2230aa3eb8e65dd33ff493ba81269689f50d2945/formats/datatables/resources/ext.srf.formats.datatables.js
		// is added to the library
		// we use searchPanes server-side also if Ajax isn't required
		// since they are more accurate
		// $searchpanes = ( !$formattedOptions['searchPanes']['forceClient'] ?
		// 	$this->getSearchPanes( $printRequests, $formattedOptions ) : [] );

		$searchpanes = ( $this->useAjax ? $this->getSearchPanes( $printRequests, $formattedOptions ) : [] );

		$data = [
			'query' => [
				'ask' => $ask,
				'result' => $result
			],
			'searchPanes' => $searchpanes,
			'searchPanesLog' => $this->searchPanesLog,
			'formattedOptions' => $formattedOptions,
			'printoutsParametersOptions' => $this->printoutsParametersOptions
		];

		return $this->printContainer( $data, $headerList, $datatablesOptions,
			$printrequests, $printouts );
	}

	/**
	 * @param array $data
	 * @param array $headerList
	 * @param array $datatablesOptions
	 * @param array $printrequests
	 * @param array $printouts
	 * @return string
	 */
	private function printContainer( $data, $headerList, $datatablesOptions, $printrequests, $printouts ) {
		$resourceFormatter = new ResourceFormatter();
		$id = $resourceFormatter->session();
		$resourceFormatter->encode( $id, $data );

		// the following does not work with $wgCachePages
		// so we deliberately use it to provide client-side
		// the performer only when the page is edited

		$context = RequestContext::getMain();
		$performer = $context->getUser();
		$context->getOutput()->addJsConfigVars( [
			'performer' => $performer->getName(),
		]);

		$tableAttrs = [
			'class' => 'srf-datatable' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ),
			// 'data-theme' => $this->params['theme'],
			'data-collation' => !empty( $GLOBALS['smwgEntityCollation'] ) ? $GLOBALS['smwgEntityCollation'] : $GLOBALS['wgCategoryCollation'],
			'data-nocase' => ( $GLOBALS['smwgFieldTypeFeatures'] === SMW_FIELDT_CHAR_NOCASE ? true : false ),
			'data-column-sort' => json_encode( [
				'list'  => $headerList,
				'sort'  => $this->params['sort'],
				'order' => $this->params['order']
			] ),
			'data-datatables' => json_encode( $datatablesOptions, true ),
			'data-printrequests' => json_encode( $printrequests, true ),
			'data-printouts' => json_encode( $printouts, true ),
			'data-use-ajax' => $this->useAjax,
			'data-count' => $this->query->getOption( 'count' ),
			'data-editor' => $performer->getName(),
		];

		// Element includes info, spinner, and container placeholder
		return Html::rawElement(
			'div',
			$tableAttrs,
			Html::element(
				'div',
				[
					'class' => 'top'
				],
				''
			) . $resourceFormatter->placeholder() . Html::element(
				'div',
				[
					'id' => $id,
					'class' => 'datatables-container',
					'style' => 'display:none;'
				]
			)
		);
	}

	/**
	 * @see SRFSlideShow
	 * @param array $printRequests
	 * @return array
	 */
	private function getPrintouts( $printRequests ) {

		foreach ( $printRequests as $key => $printRequest ) {
			$canonicalLabel = $printRequest->getCanonicalLabel();

			$data = $printRequest->getData();

			$name = ( $data instanceof SMWPropertyValue ?
				$data->getDataItem()->getKey() : null );

			$parameters = $printRequest->getParameters();

			$printouts[] = [
				$printRequest->getMode(),
				$canonicalLabel,
				$name,
				$printRequest->getOutputFormat(),
				$parameters
			];

			$this->printoutsParameters[$canonicalLabel] = $parameters;
			$this->printoutsParametersOptions[$key] = $this->getPrintoutsOptions( $parameters );
		}

		return $printouts;
	}

	/**
	 * @param array $parameters
	 * @return array
	 */
	private function getPrintoutsOptions( $parameters ) {
		$arrayTypesColumns = [
			'orderable' => 'boolean',
			'searchable' => 'boolean',
			'visible' => 'boolean',
			'orderData' => 'numeric-array',
			'searchPanes.collapse' => 'boolean',
			'searchPanes.controls' => 'boolean',
			'searchPanes.hideCount' => 'boolean',
			'searchPanes.orderable' => 'boolean',
			'searchPanes.initCollapsed' => 'boolean',
			'searchPanes.show' => 'boolean',
			'searchPanes.threshold' => 'number',
			'searchPanes.viewCount' => 'boolean',
			// ...
		];

		$ret = [];
		foreach ( $parameters as $key => $value ) {
			$key = preg_replace( '/datatables-(columns\.)?/', '', $key );
			$value = trim( $value );

			if ( array_key_exists( $key, $arrayTypesColumns ) ) {
				switch ( $arrayTypesColumns[$key] ) {
					case "boolean":
						$value = strtolower( $value ) === "true"
							|| ( is_numeric( $value ) && $value == 1 );
						break;

					case "numeric-array":
						$value = preg_split( "/\s*,\s*/", $value, -1, PREG_SPLIT_NO_EMPTY );
						break;

					case "number":
						$value = $value * 1;
						break;
					
					// ...
				}

			}

			// convert strings like columns.searchPanes.show
			// to nested objects
			$arr = explode( '.', $key );

			$ret = array_merge_recursive( $this->plainToNestedObj( $arr, $value ),
				$ret );
		}
	
		return $ret;
	}

	/**
	 * @param array $arr
	 * @param string $value
	 * @return array
	 */
	private function plainToNestedObj( $arr, $value ) {
		$ret = [];

		// link to first level
		$t = &$ret;
		foreach ( $arr as $key => $k ) {
			if ( !array_key_exists( $k, $t ) ) {
				$t[$k] = [];
			}
			// link to deepest level
			$t = &$t[$k];
			if ( $key === count( $arr ) - 1 ) {
				$t = $value;
			}
		}
		return $ret;
	}

	/**
	 * @param array $printRequests
	 * @param array $formattedOptions
	 * @return array
	 */
	private function getSearchPanes( $printRequests, $formattedOptions ) {
		$searchPanesOptions = $formattedOptions['searchPanes'];

		// searchPanes are disabled
		if ( empty( $searchPanesOptions ) ) {
			return [];
		}
		$this->queryEngineFactory = new \SMW\SQLStore\QueryEngineFactory( $this->store );
		$this->connection = $this->store->getConnection( 'mw.db.queryengine' );		
		$this->queryFactory = new QueryFactory();

		$ret = [];
		foreach ( $printRequests as $i => $printRequest ) {
			if ( count( $searchPanesOptions['columns'] ) && !in_array( $i, $searchPanesOptions['columns'] ) ) {
				continue;
			}

			$parameterOptions = $this->printoutsParametersOptions[$i];

			$searchPanesParameterOptions = ( array_key_exists( 'searchPanes', $parameterOptions ) ?
				$parameterOptions['searchPanes'] : [] );

			if ( array_key_exists( 'show', $searchPanesParameterOptions ) && $searchPanesParameterOptions['show'] === false ) {
				continue;
			}

			$canonicalLabel = ( $printRequest->getMode() !== SMWPrintRequest::PRINT_THIS ?
				$printRequest->getCanonicalLabel() : '' );

			$ret[$i] = $this->getPanesOptions( $printRequest, $canonicalLabel, $searchPanesOptions, $searchPanesParameterOptions );
		}

		return $ret;
	}

	/**
	 * @TODO move to a dedicated class with separate code blocks
	 * @param PrintRequest $printRequest
	 * @param string $canonicalLabel
	 * @param array $searchPanesOptions
	 * @param array $searchPanesParameterOptions
	 * @return array
	 */
	private function getPanesOptions( $printRequest, $canonicalLabel, $searchPanesOptions, $searchPanesParameterOptions ) {

		if ( empty( $canonicalLabel ) ) {
			return $this->searchPanesMainlabel( $printRequest, $searchPanesOptions, $searchPanesParameterOptions );
		}

		// create a new query for each printout/pane
		// and retrieve the query segment related to it
		// then perform the real query to get the results

		$queryParams = [
			'limit' => $this->query->getLimit(),
			'offset' => $this->query->getOffset(),
			'mainlabel' => $this->query->getMainlabel()
		];
		$queryParams = SMWQueryProcessor::getProcessedParams( $queryParams, [] );

		// @TODO @FIXME
		// get original description and add a conjunction
		// $queryDescription = $query->getDescription();
		// $queryCount = new \SMWQuery($queryDescription);
		// ...

		$isCategory = $printRequest->getMode() === PrintRequest::PRINT_CATS;

		// @TODO @FIXME cover PRINT_CHAIN as well
		$newQuery = SMWQueryProcessor::createQuery(
			$this->query->getQueryString() . ( !$isCategory ? '[[' . $canonicalLabel . '::+]]' : '' ),
			$queryParams,
			SMWQueryProcessor::INLINE_QUERY,
			''
		);

		$queryDescription = $newQuery->getDescription();
		$queryDescription->setPrintRequests( [$printRequest] );

		$conditionBuilder = $this->queryEngineFactory->newConditionBuilder();

		$rootid = $conditionBuilder->buildCondition( $newQuery );

		\SMW\SQLStore\QueryEngine\QuerySegment::$qnum = 0;
		$querySegmentList = $conditionBuilder->getQuerySegmentList();

		$querySegmentListProcessor = $this->queryEngineFactory->newQuerySegmentListProcessor();

		$querySegmentListProcessor->setQuerySegmentList( $querySegmentList );

		// execute query tree, resolve all dependencies
		$querySegmentListProcessor->process( $rootid );

		$qobj = $querySegmentList[$rootid];

		$property = new DIProperty( DIProperty::newFromUserLabel( $printRequest->getCanonicalLabel() ) );

		if ( $isCategory ) {

			// data-length without the GROUP BY clause
			$sql_options = [ 'LIMIT' => 1 ];

			$dataLength = (int)$this->connection->selectField(
				$this->connection->tableName( $qobj->joinTable ) . " AS $qobj->alias" . $qobj->from
					. ' JOIN ' . $this->connection->tableName( 'smw_fpt_inst' ) . " AS insts ON $qobj->alias.smw_id = insts.s_id",
				"COUNT(*) AS count",
				$qobj->where,
				__METHOD__,
				$sql_options
			);

			if ( !$dataLength ) {
				return [];
			}

			$groupBy = "i.smw_id";
			$orderBy = "count DESC, $groupBy ASC";
			$sql_options = [
				'GROUP BY' => $groupBy,
				'LIMIT' => $dataLength, 	// $this->query->getOption( 'count' ),
				'ORDER BY' => $orderBy,
				'HAVING' => 'count >= ' . $searchPanesOptions['minCount']
			];

			/*
			SELECT COUNT(i.smw_id), i.smw_id, i.smw_title FROM `smw_object_ids` AS t0 
			JOIN `smw_fpt_inst` AS t1 ON t0.smw_id=t1.s_id 
			JOIN `smw_fpt_inst` AS insts ON t0.smw_id=insts.s_id 
			JOIN `smw_object_ids` AS i ON i.smw_id = insts.o_id
			where (t1.o_id=1077)
			GROUP BY i.smw_id
			HAVING COUNT(i.smw_id) >= 1 ORDER BY COUNT(i.smw_id) DESC
			*/

			$res = $this->connection->select(
				$this->connection->tableName( $qobj->joinTable ) . " AS $qobj->alias" . $qobj->from
					// @see https://github.com/SemanticMediaWiki/SemanticDrilldown/blob/master/includes/Sql/SqlProvider.php
					. ' JOIN ' . $this->connection->tableName( 'smw_fpt_inst' ) . " AS insts ON $qobj->alias.smw_id = insts.s_id"
					. ' JOIN ' . $this->connection->tableName( SQLStore::ID_TABLE ) . " AS i ON i.smw_id = insts.o_id",
				"COUNT($groupBy) AS count, i.smw_id, i.smw_title, i.smw_namespace, i.smw_iw, i.smw_sort, i.smw_subobject",
				$qobj->where,
				__METHOD__,
				$sql_options
			);

			$isIdField = true;

		} else {

			$tableid = $this->store->findPropertyTableID( $property );
	
			$querySegmentList = array_reverse( $querySegmentList );

			// get aliases
			$p_alias = null;
			foreach ( $querySegmentList as $segment ) {			
				if ( $segment->joinTable === $tableid ) {
					$p_alias = $segment->alias;
					break;
				}
			}

			if ( empty( $p_alias ) ) {
				$this->searchPanesLog[] = [
					'canonicalLabel' => $printRequest->getCanonicalLabel(),
					'error' => '$p_alias is null',
				];
				return [];
			}

			// data-length without the GROUP BY clause
			$sql_options = [ 'LIMIT' => 1 ];

			// SELECT COUNT(*) as count FROM `smw_object_ids` AS t0
			// INNER JOIN (`smw_fpt_mdat` AS t2 INNER JOIN `smw_di_wikipage` AS t3 ON t2.s_id=t3.s_id) ON t0.smw_id=t2.s_id
			// WHERE ((t3.p_id=517)) LIMIT 500

			$dataLength = (int)$this->connection->selectField(
				$this->connection->tableName( $qobj->joinTable ) . " AS $qobj->alias" . $qobj->from,
				"COUNT(*) as count",
				$qobj->where,
				__METHOD__,
				$sql_options
			);

			if ( !$dataLength ) {
				return [];
			}

			list( $diType, $isIdField, $fields, $groupBy, $orderBy ) = $this->fetchValuesByGroup( $property, $p_alias );

			/*
			---GENERATED DATATABLES

			SELECT t0.smw_id,t0.smw_title,t0.smw_namespace,t0.smw_iw,t0.smw_subobject,t0.smw_hash,t0.smw_sort,COUNT( t3.o_id ) as count FROM `smw_object_ids` AS t0 INNER JOIN (`smw_fpt_mdat` AS t2 INNER JOIN `smw_di_wikipage` AS t3 ON t2.s_id=t3.s_id <<and t3.s_id = smw_object_ids.smw_id>> ) ON t0.smw_id=t2.s_id  WHERE ((t3.p_id=517)) GROUP BY t3.o_id, t0.smw_id HAVING count >= 1 ORDER BY count DESC, t0.smw_sort ASC LIMIT 500

			---GENERATED ByGroupPropertyValuesLookup
			SELECT i.smw_id,i.smw_title,i.smw_namespace,i.smw_iw,i.smw_subobject,i.smw_hash,i.smw_sort,COUNT( p.o_id ) as count FROM `smw_object_ids` `o` INNER JOIN `smw_di_wikipage` `p` ON ((p.s_id=o.smw_id)) JOIN `smw_object_ids` `i` ON ((p.o_id=i.smw_id)) WHERE o.smw_hash IN ('1_-_A','1_-_Ab','1_-_Abc','10_-_Abcd','11_-_Abc') AND (o.smw_iw!=':smw') AND (o.smw_iw!=':smw-delete') AND p.p_id = 517 GROUP BY p.o_id, i.smw_id ORDER BY count DESC, i.smw_sort ASC

			*/
		
			global $smwgQMaxLimit;

			$sql_options = [
				'GROUP BY' => $groupBy,
				// the following implies that if the user sets a threshold
				// close or equal to 1, and there are too many unique values,
				// the page will break, however the user has responsibility
				// for using searchPanes only for data reasonably grouped
				// shouldn't be 'LIMIT' => $smwgQMaxLimit, ?
				'LIMIT' => $dataLength,
				'ORDER BY' => $orderBy,
				'HAVING' => 'count >= ' . $searchPanesOptions['minCount']
			];

			// @see QueryEngine
			$res = $this->connection->select(
				 $this->connection->tableName( $qobj->joinTable ) . " AS $qobj->alias" . $qobj->from
				. ( !$isIdField ?  ''
					: " JOIN " . $this->connection->tableName( SQLStore::ID_TABLE ) . " AS `i` ON ($p_alias.o_id = i.smw_id)" ),
				implode( ',', $fields ),
				$qobj->where . ( !$isIdField ? '' : ( !empty( $qobj->where ) ? ' AND' : '' )
					. ' i.smw_iw!=' . $this->connection->addQuotes( SMW_SQL3_SMWIW_OUTDATED )
					. ' AND i.smw_iw!=' . $this->connection->addQuotes( SMW_SQL3_SMWDELETEIW ) ),
				__METHOD__,
				$sql_options
			);

		}

		// verify uniqueRatio

		// @see https://datatables.net/extensions/searchpanes/examples/initialisation/threshold.htm
		// @see https://github.com/DataTables/SearchPanes/blob/818900b75dba6238bf4b62a204fdd41a9b8944b7/src/SearchPane.ts#L824

		$threshold = !empty( $searchPanesParameterOptions['threshold'] ) ?
			$searchPanesParameterOptions['threshold'] : $searchPanesOptions['threshold'];

		$outputFormat = $printRequest->getOutputFormat();

		// *** if outputFormat is not set we can compute
		// uniqueness ratio by now, otherwise we have to
		// perform it after grouping the actual data
		if ( !$outputFormat ) {
			$binLength = $res->numRows();
			$uniqueRatio = $binLength / $dataLength;

			$this->searchPanesLog[] = [
				'canonicalLabel' => $printRequest->getCanonicalLabel(),
				'dataLength' => $dataLength,
				'binLength' => $binLength,
				'uniqueRatio' => $uniqueRatio,
				'threshold' => $threshold,
				'grouped' => false,
			];

			//  || $binLength <= 1
			if ( $uniqueRatio > $threshold ) {
				return [];
			}
		}

		// @see ByGroupPropertyValuesLookup
		$diType = DataTypeRegistry::getInstance()->getDataItemId(
			$property->findPropertyTypeID()
		);

		$diHandler = $this->store->getDataItemHandlerForDIType(
			$diType
		);

		$fields = $diHandler->getFetchFields();

		$deepRedirectTargetResolver = ApplicationFactory::getInstance()
			->newMwCollaboratorFactory()->newDeepRedirectTargetResolver();

		$outputMode = SMW_OUTPUT_HTML;
		$isSubject = false;
		$groups = [];
		foreach ( $res as $row ) {

			if ( $isIdField ) {
				$dbKeys = [
					$row->smw_title,
					$row->smw_namespace,
					$row->smw_iw,
					$row->smw_sort,
					$row->smw_subobject
				];

			} else {
				$dbKeys = [];
				foreach ( $fields as $field => $fieldType ) {
					$dbKeys[] = $row->$field;
				}
			}

			$dbKeys = count( $dbKeys ) > 1 ? $dbKeys : $dbKeys[0];

			$dataItem = $diHandler->dataItemFromDBKeys(
				$dbKeys
			);

			// try to resolve redirect
			if ( $isIdField && $row->smw_iw === SMW_SQL3_SMWREDIIW ) {
				$redirectTarget = null;
				// @see SMWExportController
				try {
					$redirectTarget = $deepRedirectTargetResolver->findRedirectTargetFor( $dataItem->getTitle() );
				} catch ( \Exception $e ) {
				}
				if ( $redirectTarget ) {
					$dataItem = DIWikiPage::newFromTitle( $redirectTarget );
				}
			}

			$dataValue = DataValueFactory::getInstance()->newDataValueByItem(
				$dataItem,
				$property
			);

			if ( $outputFormat ) {
				$dataValue->setOutputFormat( $outputFormat );
			}

			$cellContent = $this->getCellContent(
				$printRequest->getCanonicalLabel(),
				[ $dataValue ],
				$outputMode,
				$isSubject
			);

			if ( !array_key_exists( $cellContent, $groups ) ) {
				$groups[$cellContent] = [ 'count' => 0, 'value' => '' ];

				if (  $dataItem->getDiType() === DataItem::TYPE_TIME ) {
					// max Unix time
					$groups[$cellContent]['minDate'] = 2147483647;
					$groups[$cellContent]['maxDate'] = 0;
				}
			}

			$groups[$cellContent]['count'] += $row->count;

			// @TODO complete with all the possible transformations of
			// datavalues (DataValues/ValueFormatters)
			// based on $printRequest->getOutputFormat()
			// and provide to the API the information to
			// rebuild the query when values are grouped
			// by the output of the printout format, e.g.
			// if grouped by unit (for number datatype)
			// value should be *, for datetime see the 
			// method below

			switch( $dataItem->getDiType() ) {
				case DataItem::TYPE_NUMBER:
					if ( $outputFormat === '-u' ) {
						$value = '*';
					} else {
						$value = $dataValue->getNumber();
					}
					break;

				case DataItem::TYPE_BLOB:
					// @see IntlNumberFormatter
					// $requestedLength = intval( $outputFormat );
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_BOOLEAN:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_URI:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_TIME:
					$currentDate = $dataItem->asDateTime()->getTimestamp();
					$value = $dataValue->getISO8601Date();
					if ( $currentDate < $groups[$cellContent]['minDate'] ) {
						$groups[$cellContent]['minDate'] = $currentDate;
					}
					if ( $currentDate > $groups[$cellContent]['maxDate'] ) {
						$groups[$cellContent]['maxDate'] = $currentDate;
					}
					break;

				case DataItem::TYPE_GEO:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_CONTAINER:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_WIKIPAGE:
					$title_ = $dataValue->getTitle();
					if ( $title_ ) {
						$value = $title_->getFullText();
					} else {
						$value = $dataValue->getWikiValue();
						$this->searchPanesLog[] = [
							'canonicalLabel' => $printRequest->getCanonicalLabel(),
							'error' => 'TYPE_WIKIPAGE title is null',
							'wikiValue' => $value,
						];
					}
					break;

				case DataItem::TYPE_CONCEPT:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_PROPERTY:

					break;
				case DataItem::TYPE_NOTYPE:
					$value = $dataValue->getWikiValue();
					break;

				default:
					$value = $dataValue->getWikiValue();

			}

			$groups[$cellContent]['value'] = $value;
		}

		if ( $outputFormat ) {
			$binLength = count( $groups );
			$uniqueRatio = $binLength / $dataLength;

			$this->searchPanesLog[] = [
				'canonicalLabel' => $printRequest->getCanonicalLabel(),
				'dataLength' => $dataLength,
				'binLength' => $binLength,
				'uniqueRatio' => $uniqueRatio,
				'threshold' => $threshold,
				'grouped' => true,
			];

			//  || $binLength <= 1
			if ( $uniqueRatio > $threshold ) {
				return [];
			}

		}
	
		arsort( $groups, SORT_NUMERIC );

		$ret = [];
		foreach( $groups as $content => $value ) {

			// @see https://www.semantic-mediawiki.org/wiki/Help:Search_operators
			// the latest value is returned, with the largest range
			if ( array_key_exists( 'minDate', $value ) && $value['minDate'] != $value['maxDate'] ) {
				// ISO 8601
				// @TODO use a symbol instead and transform from the API
				$value['value'] = '>' . date( 'c', $value['minDate'] ) . ']][[' . $printRequest->getCanonicalLabel() . '::<' . date( 'c', $value['maxDate'] );
			}

			$ret[] = [
				'label' => $content,
				'count' => $value['count'],
				'value' => $value['value']
			];
		}

		return $ret;
	}

	/**
	 * @see ByGroupPropertyValuesLookup
	 * @param DIProperty $property
	 * @param string $p_alias
	 * @return array
	 */
	private function fetchValuesByGroup( DIProperty $property, $p_alias ) {

		$tableid = $this->store->findPropertyTableID( $property );
		// $entityIdManager = $this->store->getObjectIds();

		$proptables = $this->store->getPropertyTables();

		// || $subjects === []
		if ( $tableid === '' || !isset( $proptables[$tableid] ) ) {
			return [];
		}

		$connection = $this->store->getConnection( 'mw.db' );

		$propTable = $proptables[$tableid];
		$isIdField = false;

		$diHandler = $this->store->getDataItemHandlerForDIType(
			$propTable->getDiType()
		);

		foreach ( $diHandler->getFetchFields() as $field => $fieldType ) {
			if ( !$isIdField && $fieldType === FieldType::FIELD_ID ) {
				$isIdField = true;
			}
		}

		$groupBy = $diHandler->getLabelField();
		$pid = '';

		if ( $groupBy === '' ) {
			$groupBy = $diHandler->getIndexField();
		}

		$groupBy = "$p_alias.$groupBy";
		$orderBy = "count DESC, $groupBy ASC";

		$diType = $propTable->getDiType();

		if ( $diType === DataItem::TYPE_WIKIPAGE ) {
			$fields = [
				"i.smw_id",
				"i.smw_title",
				"i.smw_namespace",
				"i.smw_iw",
				"i.smw_subobject",
				"i.smw_hash",
				"i.smw_sort",
				"COUNT( $groupBy ) as count"
			];

			$groupBy = "$p_alias.o_id, i.smw_id";
			$orderBy = "count DESC, i.smw_sort ASC";
		} elseif ( $diType === DataItem::TYPE_BLOB ) {
			$fields = [ "$p_alias.o_hash, $p_alias.o_blob", "COUNT( $p_alias.o_hash ) as count" ];
			$groupBy = "$p_alias.o_hash, $p_alias.o_blob";
		} elseif ( $diType === DataItem::TYPE_URI ) {
			$fields = [ "$p_alias.o_serialized, $p_alias.o_blob", "COUNT( $p_alias.o_serialized ) as count" ];
			$groupBy = "$p_alias.o_serialized, $p_alias.o_blob";
		} elseif ( $diType === DataItem::TYPE_NUMBER ) {
			$fields = [ "$p_alias.o_serialized,$p_alias.o_sortkey, COUNT( $p_alias.o_serialized ) as count" ];
			$groupBy = "$p_alias.o_serialized,$p_alias.o_sortkey";
			$orderBy = "count DESC, $p_alias.o_sortkey DESC";
		} else {
			$fields = [ "$groupBy", "COUNT( $groupBy ) as count" ];
		}

		// if ( !$propTable->isFixedPropertyTable() ) {
		// 	$pid = $entityIdManager->getSMWPropertyID( $property );
		// }

		return [ $diType, $isIdField, $fields, $groupBy, $orderBy ];
	}

	/**
	 * @param PrintRequest $printRequest
	 * @param array $searchPanesOptions
	 * @param array $searchPanesParameterOptions
	 * @return array
	 */
	private function searchPanesMainlabel( $printRequest, $searchPanesOptions, $searchPanesParameterOptions ) {

		// mainlabel consists only of unique values,
		// so do not display if settings don't allow that
		if ( $searchPanesOptions['minCount'] > 1 ) {
			return [];
		}
		
		$threshold = !empty( $searchPanesParameterOptions['threshold'] ) ?
			$searchPanesParameterOptions['threshold'] : $searchPanesOptions['threshold'];

		$this->searchPanesLog[] = [
			'canonicalLabel' => 'mainLabel',
			'threshold' => $threshold,
		];

		if ( $threshold < 1 ) {
			return [];
		}

		$query = $this->query;
		$queryDescription = $query->getDescription();
		$queryDescription->setPrintRequests( [] );

		$conditionBuilder = $this->queryEngineFactory->newConditionBuilder();
		$rootid = $conditionBuilder->buildCondition( $query );

		\SMW\SQLStore\QueryEngine\QuerySegment::$qnum = 0;
		$querySegmentList = $conditionBuilder->getQuerySegmentList();

		$querySegmentListProcessor = $this->queryEngineFactory->newQuerySegmentListProcessor();

		$querySegmentListProcessor->setQuerySegmentList( $querySegmentList );

		// execute query tree, resolve all dependencies
		$querySegmentListProcessor->process( $rootid );

		$qobj = $querySegmentList[$rootid];

		global $smwgQMaxLimit;

		$sql_options = [
			// *** should we set a limit here ?
			// it makes sense to show the pane for
			// mainlabel only when page titles are grouped
			// through the printout format or even the printout template
			// 'LIMIT' => $smwgQMaxLimit,
			// title
			'ORDER BY' => 't'
		];

		// Selecting those is required in standard SQL (but MySQL does not require it).
		$sortfields = implode( ',', $qobj->sortfields );
		$sortfields = $sortfields ? ',' . $sortfields : '';

		// @see QueryEngine
		$res = $this->connection->select(
			$this->connection->tableName( $qobj->joinTable ) . " AS $qobj->alias" . $qobj->from,
			"$qobj->alias.smw_id AS id," .
			"$qobj->alias.smw_title AS t," .
			"$qobj->alias.smw_namespace AS ns," .
			"$qobj->alias.smw_iw AS iw," .
			"$qobj->alias.smw_subobject AS so," .
			"$qobj->alias.smw_sortkey AS sortkey" .
			"$sortfields",
			$qobj->where,
			__METHOD__,
			$sql_options
		);

		$diHandler = $this->store->getDataItemHandlerForDIType(
			DataItem::TYPE_WIKIPAGE
		);

		$outputMode = SMW_OUTPUT_HTML;
		$isSubject = false;
		$groups = [];
		foreach( $res as $row) {

			$dataItem = $diHandler->dataItemFromDBKeys( [
				$row->t,
				intval( $row->ns ),
				$row->iw,
				'',
				$row->so
			] );

			$dataValue = DataValueFactory::getInstance()->newDataValueByItem(
				$dataItem
			);

			if ( $printRequest->getOutputFormat() ) {
				$dataValue->setOutputFormat( $printRequest->getOutputFormat() );
			}

			$cellContent = $this->getCellContent(
				$printRequest->getCanonicalLabel(),
				[ $dataValue ],
				$outputMode,
				$isSubject
			);
		
			if ( !array_key_exists( $cellContent, $groups ) ) {
				$groups[$cellContent] = [ 'count' => 0, 'value' => '' ];
			}

			$groups[$cellContent]['count']++;
			$groups[$cellContent]['value'] = $dataValue->getTitle()->getText();
		}

		arsort( $groups, SORT_NUMERIC );

		$ret = [];
		foreach( $groups as $content => $value ) {
			$ret[] = [
				'label' => $content,
				'value' => $value['value'],
				'count' => $value['count']
			];
		}

		return $ret;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	private function formatOptions( $params ) {
		$arrayTypes = [
			'lengthMenu' => "number",
			'buttons' => "string",
			'searchPanes.columns' => "number",
			// ...
		];

		$ret = [];
		foreach ($params as $key => $value) {

			// transform csv to array
			if ( array_key_exists( $key, $arrayTypes ) ) {
				$value = preg_split( "/\s*,\s*/", $value, -1, PREG_SPLIT_NO_EMPTY );

				if ( $arrayTypes[$key] === 'number' ) {
					$value = array_map( static function ( $value ) {
						return (int)$value;
					}, $value );
				}
			}

			// convert strings like columns.searchPanes.show
			// to nested objects
			$arr = explode('.', $key);

			$ret = array_merge_recursive( $this->plainToNestedObj( $arr, $value ),
				$ret );

		}

		$isAssoc = function( $value ) {
			if ( !is_array( $value ) || array() === $value ) {
				return false;
			}
			return array_keys( $value ) !== range( 0, count( $value ) - 1 );
		};

		// remove $ret["searchPanes"] = [] if $ret["searchPanes"][0] === false
		foreach ( $ret as $key => $value ) {
			if ( $isAssoc(  $value ) && array_key_exists( 0, $value ) ) {
				if ( $value[0] === false ) {
					unset( $ret[$key] );
				} else {
					unset( $ret[$key][0] );
				}
			}
		}

		return $ret;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDeferrable() {
		return false;
	}

	/**
	 * @param QueryResult $res
	 * @param int $outputMode
	 * @return array
	 */
	public function getResultJson( QueryResult $res, $outputMode ) {
		// force html
		$outputMode = SMW_OUTPUT_HTML;

		$ret = [];
		while ( $subject = $res->getNext() ) {

			$row = [];
			foreach ( $subject as $i => $field ) {
				$dataValues = [];

				$resultArray = $field;
				$printRequest = $resultArray->getPrintRequest();

				// *** the path is the following:
				// ResultArray loadContent -> fieldItemFinder findFor -> getResultsForProperty
				// -> fetchContent -> ItemFetcher fetch -> (prefetchCache/EntityLookup)->getPropertyValues 
				// -> $semanticData->getPropertyValues -> $this->store->applyRequestOptions !!
				while ( ( $dv = $resultArray->getNextDataValue() ) !== false ) {
					$dataValues[] = $dv;
				}

				$content = $this->getCellContent(
					$printRequest->getCanonicalLabel(),
					$dataValues,
					$outputMode,
					$printRequest->getMode() == PrintRequest::PRINT_THIS
				);

				$row[] = $content;
			}

			$ret[] = $row;
		}

		return $ret;
	}

	// @see SMW\Query\ResultPrinters\TableResultPrinter
	protected function getCellContent( string $label, array $dataValues, $outputMode, $isSubject ) {
		if ( !$this->prefixParameterProcessor ) {
			$dataValueMethod = 'getShortText';
		} else {
			$dataValueMethod = $this->prefixParameterProcessor->useLongText( $isSubject ) ? 'getLongText' : 'getShortText';
		}

		$template = null;
		if ( !empty( $this->printoutsParameters[$label]['template'] ) ) {
			$template = $this->printoutsParameters[$label]['template'];

		} elseif ( $isSubject && !empty( $this->params['mainlabel-template'] ) ) {
			$template = $this->params['mainlabel-template'];
		}

		if ( $template ) {
			$outputMode = SMW_OUTPUT_WIKI;
		}

		$values = [];
		foreach ( $dataValues as $dv ) {
			// Restore output in Special:Ask on:
			// - file/image parsing
			// - text formatting on string elements including italic, bold etc.
			if ( $outputMode === SMW_OUTPUT_HTML && $dv->getDataItem() instanceof DIWikiPage && $dv->getDataItem()->getNamespace() === NS_FILE ||
				$outputMode === SMW_OUTPUT_HTML && $dv->getDataItem() instanceof DIBlob ) {
				// Too lazy to handle the Parser object and besides the Message
				// parse does the job and ensures no other hook is executed
				$value = Message::get(
					[ 'smw-parse', $dv->$dataValueMethod( SMW_OUTPUT_WIKI, $this->getLinker( $isSubject ) ) ],
					Message::PARSE
				);
			} else {
				$value = $dv->$dataValueMethod( $outputMode, $this->getLinker( $isSubject ) );
			}

			if ( $template ) {
				$value = $this->parser->recursiveTagParseFully( '{{' . $template . '|' . $value . '}}' );
			}

			$values[] = $value === '' ? '&nbsp;' : $value;
		}

		$sep = strtolower( $this->params['sep'] );

		if ( !$isSubject && $sep === 'ul' && count( $values ) > 1 ) {
			$html = '<ul><li>' . implode( '</li><li>', $values ) . '</li></ul>';
		} elseif ( !$isSubject && $sep === 'ol' && count( $values ) > 1 ) {
			$html = '<ol><li>' . implode( '</li><li>', $values ) . '</li></ol>';
		} else {
			$html = implode( $this->params['sep'], $values );
		}

		return $html;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getResources() {
		return [
			'modules' => [
				'ext.srf.datatables.v2.format'
			],
			'targets' => [ 'mobile', 'desktop' ]
		];
	}

}
