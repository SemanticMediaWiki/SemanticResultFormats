<?php

/**
 * SRF DataTables and SMWAPI.
 *
 * @see http://datatables.net/
 *
 * @license GPL-2.0-or-later
 * @author thomas-topway-it for KM-A
 * @credits mwjames
 */

namespace SRF;

use Html;
use MediaWiki\MediaWikiServices;
use Parser;
use RequestContext;
use SMW\DataValues\PropertyValue;
use SMW\DIWikiPage;
use SMW\Localizer\Message;
use SMW\Parser\RecursiveTextProcessor;
use SMW\Query\PrintRequest;
use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\PrefixParameterProcessor;
use SMW\Query\ResultPrinters\ResultPrinter;
use SMW\Store;
use SMW\Utils\HtmlTable;
use SMWDIBlob as DIBlob;
use SMWQuery as Query;
use SRF\DataTables\SearchPanes;

class DataTables extends ResultPrinter {

	/*
	 * camelCase params
	 */
	protected static array $camelCaseParamsKeys = [];

	private ?PrefixParameterProcessor $prefixParameterProcessor = null;

	private array $printoutsParameters = [];

	public array $printoutsParametersOptions = [];

	private ?Parser $parser = null;

	private bool $recursiveAnnotation = false;

	public ?Store $store = null;

	public ?Query $query = null;

	private bool $useAjax = false;

	private HtmlTable $htmlTable;

	private bool $hasMultipleValues = false;

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName(): string {
		return $this->msg( 'srf-printername-datatables' )->text();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ): array {
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
			// 'default' => '&#32;',
		];

		$params['prefix'] = [
			'message' => 'smw-paramdesc-prefix',
			'default' => 'none',
			'values' => [ 'all', 'subject', 'none', 'auto' ],
		];

		$params['limit'] = [
			'type' => 'integer',
			'message' => 'smw-paramdesc-limit',
			'default' => 100,
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

		$params['datatables-columns.width'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '',
		];

		//////////////// datatables mark
		// @see https://markjs.io/#mark
		// @see https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/776

		$params['datatables-mark'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-mark.separateWordSearch'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-mark.accuracy'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 'partially',
		];

		$params['datatables-mark.diacritics'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => true,
		];

		$params['datatables-mark.acrossElements'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-mark.caseSensitive'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-mark.ignoreJoiners'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

		$params['datatables-mark.ignorePunctuation'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			// or ':;.,-–—‒_(){}[]!\'"+='
			'default' => '',
		];

		$params['datatables-mark.wildcards'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 'disabled',
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
			$strlower = strtolower( $key );
			self::$camelCaseParamsKeys[$strlower] = $key;
			$ret[$strlower] = $value;
		}

		return $ret;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildResult( QueryResult $results ): array {
		$this->isHTML = true;
		$this->hasTemplates = false;

		$outputMode = ( $this->params['apicall'] !== 'apicall' ? SMW_OUTPUT_HTML : $this->outputMode );

		if ( $this->params['apicall'] === 'apicall' ) {
			$this->initializePrintoutParametersAndParser( $results );
		} else {
			$this->parser = $this->copyParser();
		}

		// Get output from printer:
		$result = $this->getResultText( $results, $outputMode );

		// $outputMode = SMW_OUTPUT_WIKI;

		if ( $outputMode !== SMW_OUTPUT_FILE ) {
			$result = $this->handleNonFileResult( $result, $results, $outputMode );
		}

		return $result;
	}

	/**
	 * @param QueryResult $results
	 */
	protected function initializePrintoutParametersAndParser( QueryResult $results ) {
		// rebuild $this->printoutsParameters from
		// printouts since $this->getPrintouts is not invoked
		// alternatively use the $data['printouts'] from the Api
		$printRequests = $results->getPrintRequests();
		foreach ( $printRequests as $printRequest ) {
			$canonicalLabel = $printRequest->getCanonicalLabel();
			$this->printoutsParameters[$canonicalLabel] = $printRequest->getParameters();
		}

		// @see https://github.com/SemanticMediaWiki/SemanticResultFormats/pull/854
		// the following ensures that $this->parser->recursiveTagParseFully
		// (getCellContent) will work
		$context = RequestContext::getMain();
		$performer = $context->getUser();
		$output = $context->getOutput();

		$this->parser = MediaWikiServices::getInstance()->getParserFactory()->getInstance();
		$this->parser->setTitle( $output->getTitle() );
		$this->parser->setOptions( $output->parserOptions() );
		$this->parser->setOutputType( Parser::OT_HTML );
		$this->parser->clearState();	
	}

	/**
	 * {@inheritDoc}
	 */
	protected function handleNonFileResult( $result, QueryResult $results, $outputmode ): array {
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
			// @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/853
			// $result = $this->parser->recursiveTagParseFully( $this->mIntro ) . $result;
			if ( $outputmode == SMW_OUTPUT_HTML && $this->isHTML ) {
				$result = Message::get( [ 'smw-parse', $this->mIntro ], Message::PARSE ) . $result;
			} elseif ( $outputmode !== SMW_OUTPUT_RAW ) {
				$result = $this->mIntro . $result;
			}
		}

		// Apply outro parameter
		if ( ( $this->mOutro ) && ( $results->getCount() > 0 ) ) {
			// @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/853
			// $result = $result . $this->parser->recursiveTagParseFully( $this->mOutro );
			if ( $outputmode == SMW_OUTPUT_HTML && $this->isHTML ) {
				$result = $result . Message::get( [ 'smw-parse', $this->mOutro ], Message::PARSE );
			} elseif ( $outputmode !== SMW_OUTPUT_RAW ) {
				$result = $result . $this->mOutro;
			}
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
	protected function getResultText( QueryResult $res, $outputmode ): mixed {
		$this->query = $res->getQuery();
		$this->store = $res->getStore();

		$this->prefixParameterProcessor = new PrefixParameterProcessor( $this->query, $this->params['prefix'] );

		if ( $this->params['apicall'] === "apicall" ) {
			return $this->getResultJson( $res, $outputmode );
		}

		// @see src/ResourceFormatter.php -> getData
		$ask = $this->query->toArray();

		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'datatables-' ) === 0 ) {
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
			$headerList[] = ( $printout[0] !== PrintRequest::PRINT_THIS ? $printout[1] : '' );
		}

		// @TODO put inside $this->formatOptions
		// and remove from $tableAttrs
		$datatablesOptions = [];
		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'datatables-' ) === 0 ) {
				$datatablesOptions[ str_replace( 'datatables-', '', self::$camelCaseParamsKeys[$key] ) ] = $value;
			}
		}

		$formattedOptions = $this->formatOptions( $datatablesOptions );

		// for the order @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/825
		$result = $this->getResultJson( $res, $outputmode );

		// @TODO use only one between printouts and printrequests
		$resultArray = $res->toArray();
		$printrequests = $resultArray['printrequests'];

		$this->htmlTable = new HtmlTable();
		foreach ( $headerList as $text ) {
			$attributes = [];
			$this->htmlTable->header( ( $text === '' ? '&nbsp;' : $text ), $attributes );
		}

		foreach ( $result as $i => $rows ) {
			$this->htmlTable->row();

			foreach ( $rows as $cell ) {
				$this->htmlTable->cell(
					( $cell['display'] === '' ? '&nbsp;' : $cell['display'] ),
					[]
				);
			}
			if ( $i > $datatablesOptions['pageLength'] ) {
				break;
			}
		}

		$this->useAjax = $this->query->getOption( 'useAjax' );

		$searchPanesData = [];
		$searchPanesLog = [];
		if ( array_key_exists( 'searchPanes', $formattedOptions )
			&& !empty( $formattedOptions['searchPanes'] )
			&& ( $this->useAjax || $this->hasMultipleValues ) ) {
			$searchPanes = new SearchPanes( $this );
			$searchPanesData = $searchPanes->getSearchPanes( $printRequests, $formattedOptions['searchPanes'] );
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

	private function printContainer(
		array $data,
		array $headerList,
		array $datatablesOptions,
		array $printrequests,
		array $printouts
	): string {
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
		] );

		$tableAttrs = [
			'class' => 'srf-datatable wikitable display' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ),
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

		$tableAttrs['width'] = '100%';
		// $tableAttrs['class'] .= ' broadtable';

		// remove sortable, that triggers jQuery's TableSorter
		$classes = preg_split( "/\s+/", $tableAttrs['class'], -1, PREG_SPLIT_NO_EMPTY );
		$key = array_search( 'sortable', $classes );
		if ( $key !== false ) {
			unset( $classes[$key] );
		}
		$tableAttrs['class'] = implode( " ", $classes );

		$transpose = false;
		$html = $this->htmlTable->table(
			$tableAttrs,
			$transpose,
			$this->isHTML
		);

		// @see https://cdn.datatables.net/v/dt/dt-1.13.8/datatables.js
		$datatableSpinner = Html::rawElement(
			'div',
			[
				'class' => 'datatables-spinner dataTables_processing',
				'role' => 'status'
			],
			'<div><div></div><div></div><div></div><div></div></div>'
		);

		return Html::rawElement(
			'div',
			[
				'id' => $id,
				'class' => 'datatables-container',
			],
			$datatableSpinner . $html
		);
	}

	/**
	 * @see SRFSlideShow
	 */
	private function getPrintouts( array $printRequests ): array {
		foreach ( $printRequests as $key => $printRequest ) {
			$canonicalLabel = $printRequest->getCanonicalLabel();

			$data = $printRequest->getData();

			$name = ( $data instanceof PropertyValue ?
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

	private function getPrintoutsOptions( array $parameters ): array {
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

	private function plainToNestedObj( array $arr, mixed $value ): array {
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

	private function formatOptions( array $params ): array {
		$arrayTypes = [
			'lengthMenu' => "number",
			'buttons' => "string",
			'searchPanes.columns' => "number",
			'mark.ignorePunctuation' => "",
			// ...
		];

		$ret = [];
		foreach ( $params as $key => $value ) {

			// transform csv to array
			if ( array_key_exists( $key, $arrayTypes ) ) {

				// https://markjs.io/#mark
				if ( $arrayTypes[$key] === '' ) {
					$value = str_split( $value );

				} else {
					$value = preg_split( "/\s*,\s*/", $value, -1, PREG_SPLIT_NO_EMPTY );

					if ( $arrayTypes[$key] === 'number' ) {
						$value = array_map( static function ( $value ) {
							return (int)$value;
						}, $value );
					}
				}
			}

			// convert strings like columns.searchPanes.show
			// to nested objects
			$arr = explode( '.', $key );

			$ret = array_merge_recursive( $this->plainToNestedObj( $arr, $value ),
				$ret );

		}

		$isAssoc = static function ( $value ) {
			if ( !is_array( $value ) || [] === $value ) {
				return false;
			}
			return array_keys( $value ) !== range( 0, count( $value ) - 1 );
		};

		// remove $ret["searchPanes"] = [] if $ret["searchPanes"][0] === false
		foreach ( $ret as $key => $value ) {
			if ( $isAssoc( $value ) && array_key_exists( 0, $value ) ) {
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
	public function isDeferrable(): bool {
		return false;
	}

	private function getResultJson( QueryResult $res, int $outputMode ): array {
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

	/**
	 * @see SMW\Query\ResultPrinters\TableResultPrinter
	 */
	public function getCellContent(
		string $label,
		array $dataValues,
		int $outputMode,
		bool $isSubject,
		?string $propTypeid = null
	): array {
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

		// this is only used by SearchPanes
		$isKeyword = ( $propTypeid === '_keyw' );
		$values = [];
		foreach ( $dataValues as $dv ) {
			$dataItem = $dv->getDataItem();
			// Restore output in Special:Ask on:
			// - file/image parsing
			// - text formatting on string elements including italic, bold etc.
			if ( ( $outputMode === SMW_OUTPUT_HTML && $dataItem instanceof DIWikiPage && $dataItem->getNamespace() === NS_FILE ) ||
				( $outputMode === SMW_OUTPUT_HTML && $dataItem instanceof DIBlob ) ) {
				// Too lazy to handle the Parser object and besides the Message
				// parse does the job and ensures no other hook is executed
				$value = Message::get(
					[ 'smw-parse', $dv->$dataValueMethod( SMW_OUTPUT_WIKI, $this->getLinker( $isSubject ) ) ],
					Message::PARSE
				);
			} else {
				$value = $dv->$dataValueMethod( $outputMode, $this->getLinker( $isSubject ) );
			}

			// @FIXME this is not the best way,
			// try to use $isKeyword = $dataItem->getOption( 'is.keyword' );
			// @see DIBlobHandler
			if ( $isKeyword ) {
				$value = $dataItem->normalize( $value );
			}

			if ( $template ) {
				// escape pipe character
				$value_ = str_replace( '|', '&#124;', (string)$value );
				$value = $this->parser->recursiveTagParseFully( '{{' . $template . '|' . $value_ . '}}' );
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

		// $dataValues could be empty
		$sortKey = array_key_exists( 0, $dataValues ) ? $dataValues[0]->getDataItem()->getSortKey() : '';

		return [
			'display' => $html,
			'filter' => $sortKey,
			'sort' => $sortKey
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getResources(): array {
		return [
			'modules' => [
				'ext.srf.datatables.v2.format'
			],
			'targets' => [ 'mobile', 'desktop' ]
		];
	}

}
