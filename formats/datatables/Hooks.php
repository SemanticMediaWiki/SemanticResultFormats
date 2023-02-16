<?php

namespace SRF\DataTables;

use SMW\Services\ServicesFactory as ApplicationFactory;

class Hooks {

	public static function onSMWStoreBeforeQueryResultLookupComplete( $store, $query, &$result, $queryEngine ) {
		$params = $query->getOption( 'query.params' );
		if ( !empty( $params['defer-each'] ) ) {
			$deferEach = $params['defer-each'];

		} elseif ( is_numeric( $GLOBALS['smwgSRFDatatablesLimitEach'] ) ) {
			$deferEach = $GLOBALS['smwgSRFDatatablesLimitEach'];

		} else {
			$deferEach = min( 500, $GLOBALS['smwgQDefaultLimit'] );
		}

		$inlineLimit = $query->getLimit();
		$count = self::getCount( $query, $queryEngine );

		if ( $inlineLimit < $deferEach ) {
			$deferEach = $inlineLimit;
			$max = $count;

		} else {
			$max = $inlineLimit;
		}

		$query->setUnboundLimit( min( $deferEach, $count ) );
		$query->setOption('defer-each', min( $deferEach, $count ) );
		$query->setOption('max', min( $max, $count ) );

		$queryResult = $queryEngine->getQueryResult( $query );

		$result = new \SMW\Query\QueryResult(
			$queryResult->getPrintRequests(),
			$query,
			$queryResult->getResults(),
			$store,
			false
		);

		return false;
	}

	private static function getCount( $query, $queryEngine ) {
		global $smwgQMaxLimit, $smwgQMaxInlineLimit;

		$queryDescription = $query->getDescription();
		$queryCount = new \SMWQuery( $queryDescription );
		$queryCount->setLimit( min( $smwgQMaxLimit, $smwgQMaxInlineLimit ) );
		$queryCount->setQuerySource( \SMWQuery::MODE_COUNT );
		$queryResult = $queryEngine->getQueryResult( $queryCount );

		return $queryResult->getCount();
	}

}
