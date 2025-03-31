<?php

/**
 * SRF DataTables and SMWAPI.
 *
 * @see http://datatables.net/
 *
 * @license GPL-2.0-or-later
 * @author thomas-topway-it for KM-A
 */

namespace SRF\DataTables;

use SMW\Query\PrintRequest;
use SMW\Query\QueryResult;
use SMWQuery;

class Hooks {

	public static function onSMWStoreBeforeQueryResultLookupComplete( $store, $query, &$result, $queryEngine ) {
		$params = $query->getOption( 'query.params' );

		if ( !is_array( $params ) || $params['format'] !== 'datatables' || $params['apicall'] === 'apicall' ) {
			return true;
		}

		// default @see https://datatables.net/reference/option/order
		if ( empty( $params['sort'][0] ) ) {
			$printouts = [];
			foreach ( $query->getExtraPrintouts() as $printRequest ) {
				// *** is PRINT_THIS always appropriate to match the mainLabel ?
				$printouts[] = ( $printRequest->getMode() !== PrintRequest::PRINT_THIS ?
					$printRequest->getCanonicalLabel() : '' );
			}
			$query->setSortKeys( [ $printouts[0] => "ASC" ] );
		}

		$inlineLimit = $query->getLimit();

		$count = self::getCount( $query, $queryEngine );

		if ( empty( $params['noajax'] ) ) {
			$limit = max( $params['datatables-pagelength'], $inlineLimit );

		} else {
			$limit = $count;
		}

		$query->setUnboundLimit( min( $limit, $count ) );
		$query->setOption( 'count', (int)$count );

		$queryResult = $queryEngine->getQueryResult( $query );

		// *** attention ! use the following rather
		// that after this hook is called, since SMW::Store::AfterQueryResultLookupComplete
		// migth change the result length !!
		$query->setOption( 'useAjax', (int)$count > $queryResult->getCount() );

		$result = new QueryResult(
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
		$queryCount = new SMWQuery( $queryDescription );
		$queryCount->setLimit( min( $smwgQMaxLimit, $smwgQMaxInlineLimit ) );
		$queryCount->setQuerySource( SMWQuery::MODE_COUNT );
		$queryResult = $queryEngine->getQueryResult( $queryCount );

		return $queryResult->getCount();
	}

}
