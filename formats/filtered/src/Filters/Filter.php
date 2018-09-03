<?php

namespace SRF\Filtered\Filter;

/**
 * File holding the SRF_Filtered_Filter class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

use SMWPrintRequest;
use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;

/**
 * The SRF_Filtered_Filter class.
 *
 * @ingroup SemanticResultFormats
 */
abstract class Filter {

	private $resultItems = null;
	private $printRequest = null;
	private $queryPrinter = null;
	private $jsConfig = null;

	/**
	 * Filter constructor.
	 *
	 * @param ResultItem[] $results
	 * @param SMWPrintRequest $printRequest
	 * @param Filtered $queryPrinter
	 */
	public function __construct( array &$results, SMWPrintRequest $printRequest, Filtered &$queryPrinter ) {
		$this->resultItems = $results;
		$this->printRequest = $printRequest;
		$this->queryPrinter = $queryPrinter;
	}

	/**
	 * @return ResultItem[]
	 */
	public function &getQueryResults() {
		return $this->resultItems;
	}

	/**
	 * @return SMWPrintRequest
	 */
	public function &getPrintRequest() {
		return $this->printRequest;
	}

	/**
	 * @return Filtered
	 */
	public function &getQueryPrinter() {
		return $this->queryPrinter;
	}

	/**
	 * @return string[]
	 */
	public function getActualParameters() {
		return $this->printRequest->getParameters();
	}

	/**
	 * Returns the name (string) or names (array of strings) of the resource
	 * modules to load.
	 *
	 * @return string|string[]
	 */
	public function getResourceModules() {
		return null;
	}

	/**
	 * Returns the HTML text that is to be included for this filter.
	 *
	 * This text will appear on the page in a div that has the filter's id set
	 * as class.
	 *
	 * @return string
	 */
	public function getResultText() {
		return '';
	}

	/**
	 * @param ResultItem $row
	 *
	 * @return null | string
	 */
	public function getJsDataForRow( ResultItem $row ) {
		return null;
	}

	/**
	 * @return bool
	 */
	public function isValidFilterForPropertyType() {
		return true;
	}

	/**
	 * Returns an array of config data for this filter to be stored in the JS
	 *
	 * @return string[]
	 */
	public function getJsConfig() {

		if ( $this->jsConfig === null ) {
			$this->buildJsConfig();
		}

		return $this->jsConfig;
	}

	protected function buildJsConfig() {
		$this->jsConfig = [];

		$this->addValueToJsConfig(
			'show if undefined',
			'show if undefined',
			null,
			function ( $value ) { return filter_var( $value, FILTER_VALIDATE_BOOLEAN ); }
		);
	}

	/**
	 * @param string $paramName
	 * @param string $configName
	 * @param mixed | null $default
	 * @param callable | null $callback
	 */
	protected function addValueToJsConfig( $paramName, $configName, $default = null, $callback = null ) {

		$params = $this->getActualParameters();

		if ( array_key_exists( $paramName, $params ) ) {

			$parsedValue = trim( $this->getQueryPrinter()->getParser()->recursiveTagParse( $params[$paramName] ) );

			$this->jsConfig[$configName] = ( $callback !== null ) ? call_user_func(
				$callback,
				$parsedValue
			) : $parsedValue;

		} elseif ( $default !== null ) {

			$this->jsConfig[$configName] = $default;

		}

	}

	/**
	 * @param $paramName
	 * @param $configName
	 * @param null $default
	 */
	protected function addValueListToJsConfig( $paramName, $configName, $default = null, $callback = null ) {

		$this->addValueToJsConfig(
			$paramName,
			$configName,
			$default,
			function ( $valueList ) use ( $callback ) {
				$parsedValues = $this->getQueryPrinter()->getArrayFromValueList( $valueList );
				return ( $callback !== null ) ? array_map( $callback, $parsedValues ) : $parsedValues;
			}
		);

	}
}
