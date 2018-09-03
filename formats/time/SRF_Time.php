<?php

/**
 * Formats that returns a time.
 *
 * @licence GNU GPL v3+
 * @author nischayn22
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SRFTime extends SMWResultPrinter {

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getName()
	 */
	public function getName() {
		// Give grep a chance to find the usages:
		// srf_printername_latest, srf_printername_earliest
		return wfMessage( 'srf_printername_' . $this->mFormat )->text();
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getResultText()
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
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

		$dataValue = SMWDataValueFactory::getInstance()->newDataValueByItem( $dataItems[$result], null );
		return $dataValue->getLongHTMLText();
	}

	/**
	 * Returns an array with sortkeys for dates pointing to their source DataItems.
	 *
	 * @param SMWQueryResult $res
	 *
	 * @return array
	 */
	protected function getSortKeys( SMWQueryResult $res ) {
		$seconds = [];

		while ( $row = $res->getNext() ) {
			foreach ( $row as /* SMWResultArray */
					  $resultArray ) {
				foreach ( $resultArray->getContent() as /* SMWDataItem */
						  $dataItem ) {
					if ( $dataItem->getDIType() === SMWDataItem::TYPE_TIME ) {
						$seconds[$dataItem->getSortKey()] = $dataItem;
					}
				}
			}
		}

		return $seconds;
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
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
