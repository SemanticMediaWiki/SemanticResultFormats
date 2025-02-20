<?php

use SMW\DataValueFactory;
use SMW\Query\QueryResult;
use SMW\Query\ResultPrinters\ResultPrinter;

/**
 * Formats that returns a time.
 *
 * @license GPL-3.0-or-later
 * @author nischayn22
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SRFTime extends ResultPrinter {

	/**
	 * (non-PHPdoc)
	 * @see ResultPrinter::getName()
	 */
	public function getName() {
		// Give grep a chance to find the usages:
		// srf_printername_latest, srf_printername_earliest
		return wfMessage( 'srf_printername_' . $this->mFormat )->text();
	}

	/**
	 * (non-PHPdoc)
	 * @see ResultPrinter::getResultText()
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {
		$dataItems = $this->getSortKeys( $res );

		if ( empty( $dataItems ) ) {
			return $this->params['default'];
		}

		$sortKeys = array_keys( $dataItems );

		switch ( $this->mFormat ) {
			case 'latest':
				$result = max( $sortKeys );
				break;
			case 'earliest':
				$result = min( $sortKeys );
				break;
		}

		$dataValue = DataValueFactory::getInstance()->newDataValueByItem( $dataItems[$result], null );
		return $dataValue->getLongHTMLText();
	}

	/**
	 * Returns an array with sortkeys for dates pointing to their source DataItems.
	 *
	 * @param QueryResult $res
	 *
	 * @return array
	 */
	protected function getSortKeys( QueryResult $res ) {
		$seconds = [];

		while ( $row = $res->getNext() ) {
			/* \SMW\Query\Result\ResultArray */
			foreach ( $row as
					  $resultArray ) {
				/* SMWDataItem */
				foreach ( $resultArray->getContent() as
						  $dataItem ) {
					if ( $dataItem->getDIType() === SMWDataItem::TYPE_TIME ) {
						$seconds[(string)$dataItem->getSortKey()] = $dataItem;
					}
				}
			}
		}

		return $seconds;
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

		$params['limit'] = [
			'type' => 'integer',
			'default' => 1000,
			'message' => 'srf_paramdesc_limit',
		];

		return $params;
	}

}
