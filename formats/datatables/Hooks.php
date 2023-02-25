<?php

namespace SRF\DataTables;

use SMW\Services\ServicesFactory as ApplicationFactory;

class Hooks {

	public static function onSMWStoreBeforeQueryResultLookupComplete( $store, $query, &$result, $queryEngine ) {
		$params = $query->getOption( 'query.params' );
		$inlineLimit = $query->getLimit();
		$count = self::getCount( $query, $queryEngine );
		// $limit = ( !empty( $params['defer-each'] ) ? $params['defer-each'] : $inlineLimit );
		$limit = max( $params['defer-each'], $inlineLimit );

		$query->setUnboundLimit( min( $limit , $count ) );
		$query->setOption('count', $count );

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
