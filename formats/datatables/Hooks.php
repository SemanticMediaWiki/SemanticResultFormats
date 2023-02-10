<?php

namespace SRF\DataTables;

use SMW\Services\ServicesFactory as ApplicationFactory;

class Hooks {

	public static function onSMWStoreBeforeQueryResultLookupComplete( $store, $query, &$result, $queryEngine ) {
		$count = self::getCount( $query, $queryEngine );
		$inlineLimit = $query->getLimit();

		$query->setUnboundLimit( min( $inlineLimit, $count ) );
		$query->setOption('max', $count );

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
