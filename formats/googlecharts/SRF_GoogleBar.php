<?php

use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\ResultPrinter;

/**
 * A query printer using the Google Chart API
 *
 * @note AUTOLOADED
 */

class SRFGoogleBar extends ResultPrinter {

	protected $m_width;

	/**
	 * (non-PHPdoc)
	 * @see ResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		$this->m_width = $this->params['width'];
	}

	public function getName() {
		return wfMessage( 'srf_printername_googlebar' )->text();
	}

	protected function getResultText( QueryResult $res, $outputmode ) {
		$this->isHTML = true;

		$t = "";
		$n = "";

		// if there is only one column in the results then stop right away
		if ( $res->getColumnCount() == 1 ) {
			return "";
		}

		// print all result rows
		$first = true;
		// How many bars will they be? Needed to calculate the height of the image
		$count = 0;
		// the biggest value. needed for scaling
		$max = 0;

		while ( $row = $res->getNext() ) {
			$name = $row[0]->getNextDataValue()->getShortWikiText();
			foreach ( $row as $field ) {
				while ( ( $object = $field->getNextDataValue() ) !== false ) {

					// use numeric sortkey
					if ( $object->isNumeric() ) {
						$nr = $object->getDataItem()->getSortKey();

						$count++;
						$max = max( $max, $nr );

						if ( $first ) {
							$first = false;
							$t .= $nr;
							$n = $name;
						} else {
							$t = $nr . ',' . $t;
							// yes, this is correct, it needs to be the other way
							$n .= '|' . $name;
						}
					}
				}
			}
		}
		// width of each bar
		$barwidth = 20;
		// distance between two bars
		$bardistance = 4;
		// calculates the height of the image
		$height = $count * ( $barwidth + $bardistance ) + 15;

		return '<img src="https://chart.apis.google.com/chart?cht=bhs&chbh=' . $barwidth . ',' . $bardistance . '&chs=' . $this->m_width . 'x' . $height . '&chds=0,' . $max . '&chd=t:' . $t . '&chxt=y&chxl=0:|' . $n . '" width="' . $this->m_width . '" height="' . $height . '" />';
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['width'] = [
			'type' => 'integer',
			'default' => 250,
			'message' => 'srf_paramdesc_chartwidth',
		];

		return $params;
	}

}
