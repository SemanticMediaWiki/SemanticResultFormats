<?php

namespace SRF;

use Html;
use SMW\Query\ResultPrinters\TableResultPrinter;
use SMW\DIWikiPage;
use SMW\Message;
use SMW\Query\PrintRequest;
use SMW\Query\QueryStringifier;
use SMW\Utils\HtmlTable;
use SMWDataValue;
use SMWDIBlob as DIBlob;
use SMWQueryResult as QueryResult;
use SMWResultArray as ResultArray;

/**
 * DataTables v2.
 *
 * @since 1.9
 * @license GPL-2.0-or-later
 *
 * @see credits of TableResultPrinter
 * @author thomas-topway-it <business@topway.it>
 */
class DataTables extends TableResultPrinter {

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
			'default' => 0,
		];

		$params['mode'] = [
			'type' => 'string',
			'message' => 'smw-paramdesc-mode',
			'default' => 'abc',
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
			'type' => 'integer',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => -1,
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

		////////////////////


		$params['datatables-pageLength'] = [
			'type' => 'integer',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => 20,
		];

		$params['datatables-LengthMenu'] = [
			'type' => 'string',
			'message' => 'srf-paramdesc-datatables-library-option',
			'default' => '10, 25, 50, 100',
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
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( QueryResult $res, $outputMode ) {
		$query = $res->getQuery();

		if ( class_exists( '\\SMW\Query\\ResultPrinters\\PrefixParameterProcessor' ) ) {
			$this->prefixParameterProcessor = new \SMW\Query\ResultPrinters\PrefixParameterProcessor( $query, $this->params['prefix'] );
		}

		if ( $this->params['mode'] === 'json' ) {
 			return $this->getResultJson( $res, $outputMode );
		}

		$this->query = $query;
		$this->isHTML = ( $outputMode === SMW_OUTPUT_HTML );
		$this->isDataTable = true;
		$class = isset( $this->params['class'] ) ? $this->params['class'] : '';

		$this->htmlTable = new HtmlTable();

		$columnClasses = [];
		$headerList = [];

		// Default cell value separator
		if ( !isset( $this->params['sep'] ) || $this->params['sep'] === '' ) {
			$this->params['sep'] = '<br>';
		}

		// building headers
		if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) {
			// ***edited
			// $isPlain $this->mShowHeaders == SMW_HEADERS_PLAIN;
			$isPlain = true;

			foreach ( $res->getPrintRequests() as /* SMWPrintRequest */ $pr ) {
				$attributes = [];
				$columnClass = str_replace( [ ' ', '_' ], '-', strip_tags( $pr->getText( SMW_OUTPUT_WIKI ) ) );
				$attributes['class'] = $columnClass;
				// Also add this to the array of classes, for
				// use in displaying each row.
				$columnClasses[] = $columnClass;

				// #2702 Use a fixed output on a requested plain printout
				$mode = $this->isHTML && $isPlain ? SMW_OUTPUT_WIKI : $outputMode;
				$text = $pr->getText( $mode, ( $isPlain ? null : $this->mLinker ) );
				$headerList[] = $pr->getCanonicalLabel();

				$this->htmlTable->header( ( $text === '' ? '&nbsp;' : $text ), $attributes );
			}
		}

		
		$printrequests = [];
		foreach ( $res->getPrintRequests() as $key => $printrequest ) {
			$data = $printrequest->getData();
			if ( $data instanceof SMWPropertyValue ) {
				$name = $data->getDataItem()->getKey();
			} else {
				$name = null;
			}
			$printrequests[] = [
				$printrequest->getMode(),
				$printrequest->getLabel(),
				$name,
				$printrequest->getOutputFormat(),
				$printrequest->getParameters(),
			];

		}


		$rowNumber = 0;

		while ( $subject = $res->getNext() ) {
			$rowNumber++;
			$this->getRowForSubject( $subject, $outputMode, $columnClasses );

			$this->htmlTable->row(
				[
					'data-row-number' => $rowNumber
				]
			);
		}

		// print further results footer
		if ( $this->linkFurtherResults( $res ) ) {
			$link = $this->getFurtherResultsLink( $res, $outputMode );

			$this->htmlTable->cell(
					$link->getText( $outputMode, $this->mLinker ),
					[ 'class' => 'sortbottom', 'colspan' => $res->getColumnCount() ]
			);

			$this->htmlTable->row( [ 'class' => 'smwfooter' ] );
		}

		$tableAttrs = [ 'class' => $class ];

		if ( $this->mFormat == 'broadtable' ) {
			$tableAttrs['width'] = '100%';
			$tableAttrs['class'] .= ' broadtable';
		}

		if ( $this->isDataTable ) {
			$this->addDataTableAttrs(
				$res,
				$headerList,
				$tableAttrs,
				$printrequests
			);
		}

		$transpose = $this->mShowHeaders !== SMW_HEADERS_HIDE && ( $this->params['transpose'] ?? false );

		$html = $this->htmlTable->table(
			$tableAttrs,
			$transpose,
			$this->isHTML
		);

		if ( $this->isDataTable ) {

			// Simple approximation to avoid a massive text reflow once the DT JS
			// has finished processing the HTML table
			$count = ( $this->params['transpose'] ?? false ) ? $res->getColumnCount() : $res->getCount();
			$height = ( min( ( $count + ( $res->hasFurtherResults() ? 1 : 0 ) ), 10 ) * 50 ) + 40;

			$html = Html::rawElement(
				'div',
				[
					'class' => 'smw-datatable smw-placeholder is-disabled smw-flex-center' . (
						$this->params['class'] !== '' ? ' ' . $this->params['class'] : ''
					),
					'style'     => "height:{$height}px;"
				],
				Html::rawElement(
					'span',
					[
						'class' => 'smw-overlay-spinner medium flex'
					]
				) . $html
			);
		}

		return $html;
	}


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

		return json_encode( $ret );
	}


	/**
	 * Gets a table cell for all values of a property of a subject.
	 *
	 * @since 1.6.1
	 *
	 * @param SMWResultArray $resultArray
	 * @param int $outputMode
	 * @param string $columnClass
	 *
	 * @return string
	 */
	protected function getCellForPropVals( ResultArray $resultArray, $outputMode, $columnClass ) {
		/** @var SMWDataValue[] $dataValues */
		$dataValues = [];

		while ( ( $dv = $resultArray->getNextDataValue() ) !== false ) {
			$dataValues[] = $dv;
		}

		$printRequest = $resultArray->getPrintRequest();
		$printRequestType = $printRequest->getTypeID();

		$cellTypeClass = " smwtype$printRequestType";

		// We would like the cell class to always be defined, even if the cell itself is empty
		$attributes = [
			'class' => $columnClass . $cellTypeClass
		];

		$content = null;

		if ( count( $dataValues ) > 0 ) {
			$sortKey = $dataValues[0]->getDataItem()->getSortKey();
			$dataValueType = $dataValues[0]->getTypeID();

			// The data value type might differ from the print request type - override in this case
			if ( $dataValueType !== '' && $dataValueType !== $printRequestType ) {
				$attributes['class'] = "$columnClass smwtype$dataValueType";
			}

			if ( is_numeric( $sortKey ) ) {
				$attributes['data-sort-value'] = $sortKey;
			}

			if ( $this->isDataTable && $sortKey !== '' ) {
				$attributes['data-order'] = $sortKey;
			}

			$alignment = trim( $printRequest->getParameter( 'align' ) );

			if ( in_array( $alignment, [ 'right', 'left', 'center' ] ) ) {
				$attributes['style'] = "text-align:$alignment;";
			}

			$width = htmlspecialchars(
				trim( $printRequest->getParameter( 'width' ) ),
				ENT_QUOTES
			);

			if ( $width ) {
				$attributes['style'] = ( isset( $attributes['style'] ) ? $attributes['style'] . ' ' : '' ) . "width:$width;";
			}

			$content = $this->getCellContent(
				$dataValues,
				$outputMode,
				$printRequest->getMode() == PrintRequest::PRINT_THIS
			);
		}

		// Sort the cell HTML attributes, to make test behavior more deterministic
		ksort( $attributes );

		$this->htmlTable->cell( $content, $attributes );
	}

	/**
	 * Gets the contents for a table cell for all values of a property of a subject.
	 *
	 * @since 1.6.1
	 *
	 * @param SMWDataValue[] $dataValues
	 * @param $outputMode
	 * @param boolean $isSubject
	 *
	 * @return string
	 */
	protected function getCellContent( array $dataValues, $outputMode, $isSubject ) {
		if ( !$this->prefixParameterProcessor ) {
			$dataValueMethod = 'getLongText';
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
				$value = $dv->$dataValueMethod( $outputMode, $this->getLinker( $isSubject ) );
			}

			// ***edited
			$values[] = $value === '' ? '&nbsp;' : $value;
			// $values[] = $value === '' ? '' : $value;
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
	 * @see ResultPrinter::getResources
	 */
	protected function getResources() {
		return [
			'modules' => [
				'ext.srf.datatables.v2.module',
				'ext.srf.datatables.v2.format'
			],
			'targets' => [ 'mobile', 'desktop' ]
		];
	}

	/**
	 * Gets a single table row for a subject, ie page.
	 *
	 * @since 1.6.1
	 *
	 * @param SMWResultArray[] $subject
	 * @param int $outputMode
	 * @param string[] $columnClasses
	 *
	 * @return string
	 */
	private function getRowForSubject( array $subject, $outputMode, array $columnClasses ) {
		foreach ( $subject as $i => $field ) {
			// $columnClasses will be empty if "headers=hide"
			// was set.
			if ( array_key_exists( $i, $columnClasses ) ) {
				$columnClass = $columnClasses[$i];
			} else {
				$columnClass = null;
			}

			$this->getCellForPropVals( $field, $outputMode, $columnClass );
		}
	}

	private function addDataTableAttrs( $res, $headerList, &$tableAttrs, $printrequests ) {
		$tableAttrs['class'] = 'datatable';
		$tableAttrs['width'] = '100%';
		$tableAttrs['style'] = 'opacity:.0; display:none;';

		$tableAttrs['data-column-sort'] = json_encode(
			[
				'list'  => $headerList,
				'sort'  => $this->params['sort'],
				'order' => $this->params['order']
			]
		);

		$datatablesOptions = [];
		foreach ( $this->params as $key => $value ) {
			if ( strpos( $key, 'datatables-')  === 0 ) {
				$datatablesOptions[ str_replace( 'datatables-', '', self::$camelCaseParamsKeys[$key] ) ] = $value ;
			}
		}

		$tableAttrs['data-query'] = QueryStringifier::toJson( $res->getQuery() );
		$tableAttrs['data-datatables'] = json_encode( $datatablesOptions, true );
		$tableAttrs['data-printrequests'] = json_encode( $printrequests, true );
		$tableAttrs['data-mode'] = $this->params['mode'];
		$tableAttrs['data-max'] = $this->query->getOption( 'max' );
		$tableAttrs['data-defer-each'] = $this->query->getOption( 'defer-each' );
		$tableAttrs['data-theme'] = $this->params['theme'];
		$tableAttrs['data-columnstype'] = ( !empty( $this->params['columnstype'] ) ? $this->params['columnstype'] : null );
		$tableAttrs['data-collation'] = !empty( $GLOBALS['smwgEntityCollation'] ) ? $GLOBALS['smwgEntityCollation'] : $GLOBALS['wgCategoryCollation'];

	}

}
