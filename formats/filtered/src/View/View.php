<?php

namespace SRF\Filtered\View;

/**
 * File holding the SRF_Filtered_View class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;

/**
 * The SRF_Filtered_View class.
 *
 * @ingroup SemanticResultFormats
 */
abstract class View {

	private $mParameters;
	private $mQueryPrinter;
	private $mResults;

	/**
	 * Constructor for the view.
	 *
	 * @param ResultItem[] $results
	 * @param string[] $params array of parameter values given as key-value-pairs
	 * @param Filtered $queryPrinter
	 */
	public function __construct( array &$results, array &$params, Filtered &$queryPrinter ) {
		$this->mResults = $results;
		$this->mParameters = $params;
		$this->mQueryPrinter = $queryPrinter;
	}

	/**
	 * @return ResultItem[]
	 */
	public function &getQueryResults() { return $this->mResults; }

	/**
	 * @return string[]
	 */
	public function &getActualParameters() { return $this->mParameters; }

	/**
	 * @return Filtered
	 */
	public function &getQueryPrinter() { return $this->mQueryPrinter; }

	/**
	 * Returns the name (string) or names (array of strings) of the resource
	 * modules to load.
	 *
	 * @return string|string[]|null
	 */
	public function getResourceModules() {
		return null;
	}

	/**
	 * A function to describe the allowed parameters of a query for this view.
	 *
	 * @see DefaultConfig.php of param-processor/param-processor for allowed types
	 *
	 * @return array of Parameter
	 */
	public static function getParameters() {
		return [];
	}

	/**
	 * Returns the HTML text that is to be included for this view.
	 *
	 * This text will appear on the page in a div that has the view's id set as
	 * class.
	 *
	 * @return string
	 */
	public function getResultText() {
		return '';
	}

	/**
	 * @param ResultItem $row
	 *
	 * @return null
	 */
	public function getJsDataForRow( ResultItem $row ) {
		return null;
	}

	/**
	 * Returns an array of config data for this view to be stored in the JS
	 *
	 * @return array
	 */
	public function getJsConfig() {
		return [];
	}

	/**
	 * Check if the view is ready to run, e.g. all required environment
	 * variables are set.
	 *
	 * @return string | null
	 */
	public function getInitError() {
		return null;
	}
}
