<?php

namespace SRF\Filtered\Filter;

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
class ValueFilter extends Filter {

	/**
	 * Returns the name (string) or names (array of strings) of the resource
	 * modules to load.
	 *
	 * @return string|string[]
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.value-filter';
	}

	protected function buildJsConfig() {
		parent::buildJsConfig();

		$this->addValueListToJsConfig( 'value filter switches', 'switches' );
		$this->addValueListToJsConfig( 'value filter values', 'values' );
		$this->addValueListToJsConfig( 'value filter max checkboxes', 'max checkboxes' );
		$this->addValueToJsConfig( 'value filter collapsible', 'collapsible' );

	}

}
