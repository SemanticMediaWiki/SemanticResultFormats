<?php

/**
 * File holding the SRFFiltered class.
 *
 * @author Stephan Gambke
 *
 */

namespace SRF\Filtered;

use Exception;
use Html;
use SMW\Message;
use SMW\Query\PrintRequest;
use SMW\Query\QueryLinker;
use SMW\Query\ResultPrinters\ResultPrinter;
use SMWOutputs;
use SMWPropertyValue;
use SMWQueryResult;

/**
 * Result printer that displays results in switchable views and offers
 * client-side (JavaScript based) filtering.
 *
 * This result printer is ultimately planned to replace exhibit. Currently only
 * a list view is available. It is not yet possible to switch between views.
 * There is also only the 'value' filter available yet.
 *
 * Syntax of the #ask call:
 * (This is only a syntax example. For currently available features see the
 * documentation of the various classes.)
 *
 * {{#ask:[[SomeCondition]]
 * |? SomePrintout |+filter=value, someFutureFilter |+value filter switches=and or, disable, all, none |+someFutureFilter filter option=someOptionValue
 * |? SomeOtherPrintout |+filter=value, someOtherFutureFilter |+someOtherFutureFilter filter option=someOptionValue
 *
 * |format=filtered
 * |views=list, someFutureView, someOtherFutureView
 *
 * |list view type=list
 * |list view template=ListItem
 *
 * |someFutureView view option=someOptionValue
 *
 * |someOtherFutureView view option=someOptionValue
 *
 * }}
 *
 * All format specific parameters are optional, although leaving the 'views'
 * parameter empty probably does not make much sense.
 *
 */
class Filtered extends ResultPrinter {

	/**
	 * The available view types
	 *
	 * @var array of Strings
	 */
	private $mViewTypes = [
		'list' => 'ListView',
		'calendar' => 'CalendarView',
		'table' => 'TableView',
		'map' => 'MapView',
	];

	/**
	 * The available filter types
	 *
	 * @var array of Strings
	 */
	private $mFilterTypes = [
		'value' => 'ValueFilter',
		'distance' => 'DistanceFilter',
		'number' => 'NumberFilter',
	];

	private $viewNames;
	private $parameters;
	private $filtersOnTop;
	private $printrequests;

	private $parser;

	/**
	 * @param string $valueList
	 * @param string $delimiter
	 *
	 * @return string[]
	 */
	public function getArrayFromValueList( $valueList, $delimiter = ',' ) {
		return array_map( 'trim', explode( $delimiter, $valueList ) );
	}

	/**
	 * @return \Parser | \StubObject | null
	 */
	public function getParser() {

		if ( $this->parser === null ) {
			$this->setParser( $GLOBALS['wgParser'] );
		}

		return $this->parser;
	}

	/**
	 * @param \Parser | \StubObject $parser
	 */
	public function setParser( $parser ) {
		$this->parser = $parser;
	}

	/**
	 * @return mixed
	 */
	public function getPrintrequests() {
		return $this->printrequests;
	}

	public function hasTemplates( $hasTemplates = null ) {
		$ret = $this->hasTemplates;
		if ( is_bool( $hasTemplates ) ) {
			$this->hasTemplates = $hasTemplates;
		}
		return $ret;
	}

	/**
	 * Get a human readable label for this printer.
	 *
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf-printername-filtered' )->text();
	}

	/**
	 * Does any additional parameter handling that needs to be done before the
	 * actual result is build.
	 *
	 * @param array $params
	 * @param $outputMode
	 */
	protected function handleParameters( array $params, $outputMode ) {
		parent::handleParameters( $params, $outputMode );

		// // Set in SMWResultPrinter:
		// $this->mIntro = $params['intro'];
		// $this->mOutro = $params['outro'];
		// $this->mSearchlabel = $params['searchlabel'] === false ? null : $params['searchlabel'];
		// $this->mLinkFirst = true | false;
		// $this->mLinkOthers = true | false;
		// $this->mDefault = str_replace( '_', ' ', $params['default'] );
		// $this->mShowHeaders = SMW_HEADERS_HIDE | SMW_HEADERS_PLAIN | SMW_HEADERS_SHOW;

		$this->mSearchlabel = null;

		$this->parameters = $params;
		$this->viewNames = explode( ',', $params['views'] );
		$this->filtersOnTop = $params['filter position'] === 'top';

	}

	/**
	 * Return serialised results in specified format.
	 *
	 * @param SMWQueryResult $res
	 * @param $outputmode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {

		// collect the query results in an array
		/** @var ResultItem[] $resultItems */
		$resultItems = [];
		while ( $row = $res->getNext() ) {
			$resultItems[$this->uniqid()] = new ResultItem( $row, $this );
			usleep( 1 ); // This is ugly, but for now th opnly way to get all resultItems. See #288.
		}

		$config = [
			'query' => $res->getQueryString(),
			'printrequests' => [],
			'views' => [],
			'data' => [],
		];

		list( $filterHtml, $printrequests ) = $this->getFilterHtml( $res, $resultItems );

		$this->printrequests = $printrequests;
		$config['printrequests'] = $printrequests;

		list( $viewHtml, $config ) = $this->getViewHtml( $res, $resultItems, $config );

		SMWOutputs::requireResource( 'ext.srf.filtered' );

		$id = $this->uniqid();
		// wrap all in a div
		$html = '<div class="filtered-spinner"><div class="smw-overlay-spinner"></div></div>';
		$html .= $this->filtersOnTop ? $filterHtml . $viewHtml : $viewHtml . $filterHtml;
		$html = Html::rawElement( 'div', [ 'class' => 'filtered ' . $id, 'id' => $id ], $html );

		$config['data'] = $this->getResultsForJs( $resultItems );

		$config['filtersOnTop'] = $this->filtersOnTop;
		$this->addConfigToOutput( $id, $config );

		try {
			$this->fullParams['limit']->getOriginalValue();
		}
		catch ( Exception $exception ) {
			$res->getQuery()->setLimit( 0 );
		}

		$link = QueryLinker::get( $res->getQuery() );
		$link->setCaption( Message::get( "srf-filtered-noscript-link-caption" ) );
		$link->setParameter( 'table', 'format' );

		SMWOutputs::requireResource( 'ext.srf.filtered' );
		$this->registerResources( [], [ 'ext.srf.filtered' ] );

		return $html;
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 * @see DefaultConfig.php of param-processor/param-processor for allowed types
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params[] = [
			// 'type' => 'string',
			'name' => 'views',
			'message' => 'srf-paramdesc-filtered-views',
			'default' => '',
			// 'islist' => false,
		];

		$params[] = [
			// 'type' => 'string',
			'name' => 'filter position',
			'message' => 'srf-paramdesc-filtered-filter-position',
			'default' => 'top',
			// 'islist' => false,
		];

		foreach ( $this->mViewTypes as $viewType ) {
			$params = array_merge( $params, call_user_func( [ 'SRF\Filtered\View\\' . $viewType, 'getParameters' ] ) );
		}

		return $params;
	}

	public function getLinker( $firstcol = false, $force = false ) {
		return ( $force ) ? $this->mLinker : parent::getLinker( $firstcol );
	}

	private function addConfigToOutput( $id, $config ) {

		if ( $this->getParser()->getOutput() !== null ) {
			$getter = [ $this->getParser()->getOutput(), 'getExtensionData' ];
			$setter = [ $this->getParser()->getOutput(), 'setExtensionData' ];
		} else {
			$getter = [ \RequestContext::getMain()->getOutput(), 'getProperty' ];
			$setter = [ \RequestContext::getMain()->getOutput(), 'setProperty' ];
		}

		$previousConfig = call_user_func( $getter, 'srf-filtered-config' );

		if ( $previousConfig === null ) {
			$previousConfig = [];
		}

		$previousConfig[$id] = $config;

		call_user_func( $setter, 'srf-filtered-config', $previousConfig );

	}

	/**
	 * @param string | string[] | null $resourceModules
	 */
	protected function registerResourceModules( $resourceModules ) {

		array_map( 'SMWOutputs::requireResource', (array)$resourceModules );
	}

	/**
	 * @param string|null $id
	 *
	 * @return string
	 */
	public function uniqid( $id = null ) {
		$hashedId = ( $id === null ) ? uniqid() : md5( $id );
		return base_convert( $hashedId, 16, 36 );
	}

	/**
	 * @param ResultItem[] $result
	 *
	 * @return array
	 */
	protected function getResultsForJs( $result ) {
		$resultAsArray = [];
		foreach ( $result as $id => $row ) {
			$resultAsArray[$id] = $row->getArrayRepresentation();
		}
		return $resultAsArray;
	}

	public function addError( $errorMessage ) {
		parent::addError( $errorMessage );
	}

	/**
	 * @param SMWQueryResult $res
	 * @param $result
	 *
	 * @return array
	 */
	protected function getFilterHtml( SMWQueryResult $res, $result ) {

		// prepare filter data for inclusion in HTML and  JS
		$filterHtml = '';

		$printrequests = [];

		/** @var PrintRequest $printRequest */
		foreach ( $res->getPrintRequests() as $printRequest ) {

			$prConfig = [
				'mode' => $printRequest->getMode(),
				'label' => $printRequest->getLabel(),
				'outputformat' => $printRequest->getOutputFormat(),
				'type' => $printRequest->getTypeID(),
			];

			if ( $printRequest->getData() instanceof SMWPropertyValue ) {
				$prConfig['property'] = $printRequest->getData()->getInceptiveProperty()->getKey();
			}

			if ( filter_var( $printRequest->getParameter( 'hide' ), FILTER_VALIDATE_BOOLEAN ) ) {
				$prConfig['hide'] = true;
			}

			$filtersParam = $printRequest->getParameter( 'filter' );

			if ( $filtersParam ) {

				$filtersForPrintout = $this->getArrayFromValueList( $filtersParam );

				foreach ( $filtersForPrintout as $filterName ) {

					if ( array_key_exists( $filterName, $this->mFilterTypes ) ) {

						/** @var \SRF\Filtered\Filter\Filter $filter */
						$filterClassName = '\SRF\Filtered\Filter\\' . $this->mFilterTypes[$filterName];
						$filter = new  $filterClassName( $result, $printRequest, $this );

						if ( $filter->isValidFilterForPropertyType() ) {

							$this->registerResourceModules( $filter->getResourceModules() );

							$filterid = $this->uniqid();
							$filterHtml .= Html::rawElement(
								'div',
								[ 'id' => $filterid, 'class' => "filtered-filter filtered-$filterName" ],
								$filter->getResultText()
							);

							$filterdata = $filter->getJsConfig();
							$filterdata['type'] = $filterName;
							$filterdata['label'] = $printRequest->getLabel();

							$prConfig['filters'][$filterid] = $filterdata;

							foreach ( $result as $row ) {
								$row->setData( $filterid, $filter->getJsDataForRow( $row ) );
							}
						} else {
							// TODO: I18N
							$this->addError(
								"The '$filterName' filter can not be used on the '{$printRequest->getLabel()}' printout."
							);
						}

					}
				}
			}

			$printrequests[$this->uniqid( $printRequest->getHash() )] = $prConfig;
		}

		$filterHtml .= '<div class="filtered-filter-spinner" style="display: none;"><div class="smw-overlay-spinner"></div></div>';

		// wrap filters in a div
		$filterHtml = Html::rawElement(
			'div',
			[ 'class' => 'filtered-filters', 'style' => 'display:none' ],
			$filterHtml
		);

		return [ $filterHtml, $printrequests ];
	}

	/**
	 * @param SMWQueryResult $res
	 * @param $resultItems
	 * @param $config
	 *
	 * @return array
	 */
	protected function getViewHtml( SMWQueryResult $res, $resultItems, $config ) {

		// prepare view data for inclusion in HTML and  JS
		$viewHtml = '';
		$viewSelectorsHtml = '';

		foreach ( $this->viewNames as $viewName ) {

			// cut off the selector label (if one was specified) from the actual view name
			$viewnameComponents = explode( '=', $viewName, 2 );

			$viewName = trim( $viewnameComponents[0] );

			if ( array_key_exists( $viewName, $this->mViewTypes ) ) {

				// generate unique id
				$viewid = $this->uniqid();

				if ( count( $viewnameComponents ) > 1 ) {
					// a selector label was specified in the wiki text
					$viewSelectorLabel = trim( $viewnameComponents[1] );
				} else {
					// use the default selector label
					$viewSelectorLabel = Message::get( 'srf-filtered-selectorlabel-' . $viewName );
				}

				/** @var \SRF\Filtered\View\View $view */
				$viewClassName = '\SRF\Filtered\View\\' . $this->mViewTypes[$viewName];
				$view = new $viewClassName( $resultItems, $this->parameters, $this, $viewSelectorLabel );

				$initErrorMsg = $view->getInitError();

				if ( $initErrorMsg !== null ) {
					$res->addErrors( [ $this->msg( $initErrorMsg )->text() ] );
				} else {

					$this->registerResourceModules( $view->getResourceModules() );

					$viewHtml .= Html::rawElement(
						'div',
						[ 'id' => $viewid, 'class' => "filtered-view filtered-$viewName $viewid" ],
						$view->getResultText()
					);
					$viewSelectorsHtml .= Html::rawElement(
						'div',
						[ 'class' => "filtered-view-selector filtered-$viewName $viewid" ],
						$viewSelectorLabel
					);

					foreach ( $resultItems as $row ) {
						$row->setData( $viewid, $view->getJsDataForRow( $row ) );
					}

					$config['views'][$viewid] = array_merge( [ 'type' => $viewName ], $view->getJsConfig() );
				}
			}
		}

		$viewHtml = Html::rawElement(
			'div',
			[ 'class' => 'filtered-views', 'style' => 'display:none' ],
			Html::rawElement(
				'div',
				[ 'class' => 'filtered-views-selectors-container', 'style' => 'display:none' ],
				$viewSelectorsHtml
			) .
			Html::rawElement( 'div', [ 'class' => 'filtered-views-container' ], $viewHtml )
		);
		return [ $viewHtml, $config ];
	}

}