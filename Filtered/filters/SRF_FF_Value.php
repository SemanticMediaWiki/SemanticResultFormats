<?php

/**
 * File holding the SRF_FF_Value class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

/**
 * The SRF_FF_Value class.
 *
 * Available parameters for this filter:
 *   value filter switches: switches to be shown for this filter; currently only 'and or' supported
 *
 * @ingroup SemanticResultFormats
 */
class SRF_FF_Value extends SRF_Filtered_Filter {

	/**
	 * Returns the name (string) or names (array of strings) of the resource
	 * modules to load.
	 *
	 * @return string|array
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.value-filter';
	}

	/**
	 * Returns an array of config data for this filter to be stored in the JS
	 * @return null
	 */
	public function getJsData() {
		$params = $this->getActualParameters();
		
		$ret = array();

		if ( array_key_exists( 'value filter switches', $params ) ) {
			$switches = explode( ',', $params['value filter switches'] );
			$switches = array_map( 'trim', $switches );

			$ret['switches'] = $switches;
		}

		if ( array_key_exists( 'value filter collapsible', $params ) ) {
			$ret['collapsible'] = trim($params['value filter collapsible']);
		}

		if ( array_key_exists( 'value filter height', $params ) ) {
			$ret['height'] = trim($params['value filter height']);
		}

		if ( array_key_exists( 'value filter values', $params ) ) {
			$ret['values'] = trim($params['value filter values']);
		}

		return $ret;
	}

}
