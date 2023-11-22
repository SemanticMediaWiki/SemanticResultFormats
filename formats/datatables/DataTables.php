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
use SMW\ResultPrinter;
use SMW\DIWikiPage;
use SMW\Message;
use SMW\Query\PrintRequest;
use SMW\Utils\HtmlTable;
use SMWPrintRequest;
use SMWPropertyValue;
use SMWQueryResult as QueryResult;
use SRF\DataTables\SearchPanes as SearchPanes; 

class DataTables extends ResultPrinter {

	/*
	 * camelCase params
	 */
	protected static $camelCaseParamsKeys = [];

	private $prefixParameterProcessor;

	private $printoutsParameters = [];

	public $printoutsParametersOptions = [];

	private $parser;

	/** @var bool */
	private $recursiveAnnotation = false;

	public $store;

	public $query;

	/** @var bool */
	private $useAjax;

	/** @var HtmlTable */
	private $htmlTable;
	
	/** @var bool */
	private $hasMultipleValues = false;	

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

		//////////////// datatables searchBuilder
		
		$params['datatables-searchBuilder'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
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
		
		if ( $datatablesOptions['scroller'] === false ) {
			$this->htmlTable = new HtmlTable();
			foreach ( $headerList as $text ) {
				$attributes = [];
				$this->htmlTable->header( ( $text === '' ? '&nbsp;' : $text ), $attributes );
			}

			foreach ( $result as $i => $rows ) {				
				$this->htmlTable->row();
				
				foreach ( $rows as $cell ) {
					$this->htmlTable->cell(
						( $cell === '' ? '&nbsp;' : $cell ),
						[]
					);
				}
				if ( $i > $datatablesOptions['pageLength'] ) {
					break;
				}
			}
		}

		$this->useAjax = $this->query->getOption( 'useAjax' );
		
		$searchPanesData = [];
		$searchPanesLog = [];
		if ( $this->useAjax || $this->hasMultipleValues ) {
			$searchPanes = new SearchPanes( $this );
			$searchPanesData = $searchPanes->getSearchPanes( $printRequests, $formattedOptions );
			$searchPanesLog = $searchPanes->getLog();
		}

		$data = [
			'query' => [
				'ask' => $ask,
				'result' => $result
			],
			'searchPanes' => $searchPanesData,
			'searchPanesLog' => $searchPanesLog,
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
			'class' => 'srf-datatable display dataTable' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ),
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
			'data-multiple-values' => $this->hasMultipleValues,
		];
		
		// $tableAttrs['width'] = '100%';
		
		$transpose = false;
		$html = $this->htmlTable->table(
			$tableAttrs,
			$transpose,
			$this->isHTML
		);
		
		return Html::rawElement(		
			'div',
			[
				'id' => $id,
				'class' => 'datatables-container',
			],
			$html
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
	public function getCellContent( string $label, array $dataValues, $outputMode, $isSubject ) {
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

			// @FIXME this won't work with equal symbol
			// as value
			if ( $template ) {
				$value = $this->parser->recursiveTagParseFully( '{{' . $template . '|' . $value . '}}' );
			}

			$values[] = $value === '' ? '&nbsp;' : $value;
		}

		$sep = strtolower( $this->params['sep'] );
		
		// *** used to force use of Ajax with
		// searchpanes since a client side solution
		// won't produce reliable matches
		if ( count( $values ) > 1 ) {
			$this->hasMultipleValues = true;
		}

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
