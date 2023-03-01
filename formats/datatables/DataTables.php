<?php

namespace SRF;

use Html;
use SMW\Query\ResultPrinters\TableResultPrinter;
use SMW\ResultPrinter;
use SMW\DIWikiPage;
use SMW\Message;
use SMW\Query\PrintRequest;
use SMWQueryResult as QueryResult;


class DataTables extends ResultPrinter {

	/*
	 * camelCase params
	 */
	protected static $camelCaseParamsKeys = [];

	/**
	 * @var HtmlTable
	 */
	private $htmlTable;

	private $prefixParameterProcessor;

	private $query;

	private $parser;

	/**
	 * @var boolean
	 */
	private $recursiveAnnotation = false;

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
			'name' => 'class',
			'message' => 'smw-paramdesc-table-class',
			'default' => 'srf-datatable',
		];

		$params['transpose'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'smw-paramdesc-table-transpose',
		];

		$params['sep'] = [
			'message' => 'smw-paramdesc-sep',
			'default' => '',
		];

		$params['theme'] = [
			'message' => 'srf-paramdesc-theme',
			'default' => 'basic',
			'values' => [ 'bootstrap', 'basic' ]
		];

		$params['columnstype'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-columnstype',
			'default' => '',
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

		$params['ajax'] = [
			'type' => 'string',
			'message' => 'smw-paramdesc-ajax',
			'default' => "",
		];

		// https://datatables.net/reference/option/

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

		$params['datatables-serverSide'] = [
			'type' => 'boolean',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => false,
		];

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

		////////////////////

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

		// *** work-around to allow camelCase parameters
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

		$outputMode = ( $this->params['ajax'] !== "ajax" ? SMW_OUTPUT_HTML : $this->outputMode );

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
		$query = $res->getQuery();

		if ( class_exists( '\\SMW\Query\\ResultPrinters\\PrefixParameterProcessor' ) ) {
			$this->prefixParameterProcessor = new \SMW\Query\ResultPrinters\PrefixParameterProcessor( $query, $this->params['prefix'] );
		}

		if ( $this->params['ajax'] === "ajax" ) {
 			return $this->getResultJson( $res, $outputMode );
		}

		$resourceFormatter = new ResourceFormatter();
		// $data = $resourceFormatter->getData( $res, $outputmode, $this->params );

		// @see src/ResourceFormatter.php -> getData
		$ask = $query->toArray();

		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'datatables-')  === 0 ) {
				continue;
			}
			if ( is_string( $value ) || is_int( $value ) || is_bool( $value ) ) {
				$ask['parameters'][$key] = $value;
			}
		}

		$result = $this->getResultJson( $res, $outputmode );
		// $tableAttrs['data-query'] = QueryStringifier::toJson( $query );

		// Combine all data into one object
		$data = [
			'query' => [
				// 'result' => $queryResult->toArray(),
				'ask' => $ask,
				'result' => $result
			]
		];

		$id = $resourceFormatter->session();

		// Add options
		$data['version'] = '0.2.5';

		// Encode data object
		$resourceFormatter->encode( $id, $data );

		// Init RL module
		// $resourceFormatter->registerResources( [
		// 	'ext.srf.datatables.v2.module',
		// 	'ext.srf.datatables.v2.format'
		// ] );

		$headerList = [];
		foreach ( $res->getPrintRequests() as /* SMWPrintRequest */ $pr ) {
			$value = $pr->getCanonicalLabel();

			// mainlabel in the form: mainlabel=abc
			if ( $ask['parameters']['mainlabel'] === $value ) {
				$value = '';
			}
			$headerList[] = $value;
		}

		$datatablesOptions = [];
		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'datatables-')  === 0 ) {
				$datatablesOptions[ str_replace( 'datatables-', '', self::$camelCaseParamsKeys[$key] ) ] = $value ;
			}
		}

		// @TODO use only one between printouts and printrequests
		$resultArray = $res->toArray();
		$printrequests = $resultArray['printrequests'];

		$printouts = [];
		foreach ( $res->getPrintRequests() as $key => $value ) {
			$data = $value->getData();
			if ( $data instanceof SMWPropertyValue ) {
				$name = $data->getDataItem()->getKey();
			} else {
				$name = null;
			}
			$printouts[] = [
				$value->getMode(),
				$value->getLabel(),
				$name,
				$value->getOutputFormat(),
				$value->getParameters(),
			];
		}

		$tableAttrs = [
			'class' => 'datatable' . ( $this->params['class'] ? ' ' . $this->params['class'] : '' ),
			'data-theme' => $this->params['theme'],
			'data-columnstype' => ( !empty( $this->params['columnstype'] ) ? $this->params['columnstype'] : null ),
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
			'data-count' => $query->getOption( 'count' ),
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

				while ( ( $dv = $resultArray->getNextDataValue() ) !== false ) {
					$dataValues[] = $dv;
				}

				$content = $this->getCellContent(
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
	 * {@inheritDoc}
	 */
	protected function getCellContent( array $dataValues, $outputMode, $isSubject ) {
		if ( !$this->prefixParameterProcessor ) {
			$dataValueMethod = 'getShortText';
		} else {
			$dataValueMethod = $this->prefixParameterProcessor->useLongText( $isSubject ) ? 'getLongText' : 'getShortText';
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
				$value =  $dv->$dataValueMethod( $outputMode, $this->getLinker( $isSubject ) );
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
