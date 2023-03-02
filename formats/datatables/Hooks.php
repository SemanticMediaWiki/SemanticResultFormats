<?php

/**
 * SRF DataTables and SMWAPI.
 *
 * @see http://datatables.net/
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it for KM-A
 */

namespace SRF\DataTables;

use SMW\Services\ServicesFactory as ApplicationFactory;

class Hooks {

	public static function onSMWStoreBeforeQueryResultLookupComplete( $store, $query, &$result, $queryEngine ) {
		$params = $query->getOption( 'query.params' );

		if ( $params['format'] !== 'datatables' ) {
			return true;
		}

		// if ( $query->getMainLabel() === '-' ) {
		// 	$printouts = [];
		// 	foreach ( $query->getExtraPrintouts() as $printout ) {
		// 		$printouts[] = $printout->getLabel();
		// 	}
		// 	if ( count( $printouts ) ) {
		// 		$query->setSortKeys( [$printouts[0] => "ASC"] );
		// 	}
		// }

		$inlineLimit = $query->getLimit();
		$count = self::getCount( $query, $queryEngine );
		// $limit = ( !empty( $params['defer-each'] ) ? $params['defer-each'] : $inlineLimit );

		// $lengthmenuMax = max( $params['datatables-lengthmenu'] );
		$limit = max( $params['datatables-pagelength'], $params['defer-each'], $inlineLimit );

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
