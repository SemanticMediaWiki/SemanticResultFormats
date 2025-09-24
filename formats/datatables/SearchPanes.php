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

use SMW\DataTypeRegistry;
use SMW\DataValueFactory;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\Query\PrintRequest;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMW\SQLStore\QueryEngine\QuerySegment;
use SMW\SQLStore\QueryEngineFactory;
use SMW\SQLStore\SQLStore;
use SMW\SQLStore\TableBuilder\FieldType;
use SMWDataItem as DataItem;
use SMWQueryProcessor;
use SRF\DataTables;

class SearchPanes {

	private array $searchPanesLog = [];

	private ?QueryEngineFactory $queryEngineFactory = null;

	private $connection;

	public function __construct(
		private DataTables $datatables
	) {
	}

	public function getSearchPanes( array $printRequests, array $searchPanesOptions ): array {
		$this->queryEngineFactory = new QueryEngineFactory( $this->datatables->store );
		$this->connection = $this->datatables->store->getConnection( 'mw.db.queryengine' );

		$ret = [];
		foreach ( $printRequests as $i => $printRequest ) {
			if ( count( $searchPanesOptions['columns'] ) && !in_array( $i, $searchPanesOptions['columns'] ) ) {
				continue;
			}

			$parameterOptions = $this->datatables->printoutsParametersOptions[$i];

			$searchPanesParameterOptions = ( array_key_exists( 'searchPanes', $parameterOptions ) ?
				$parameterOptions['searchPanes'] : [] );

			if ( array_key_exists( 'show', $searchPanesParameterOptions ) && $searchPanesParameterOptions['show'] === false ) {
				continue;
			}

			$canonicalLabel = ( $printRequest->getMode() !== PrintRequest::PRINT_THIS ?
				$printRequest->getCanonicalLabel() : '' );

			$ret[$i] = $this->getPanesOptions( $printRequest, $canonicalLabel, $searchPanesOptions, $searchPanesParameterOptions );
		}

		return $ret;
	}

	public function getLog(): array {
		return $this->searchPanesLog;
	}

	private function parseQuerySegment( $querySegment ) {
/*
parse QuerySegment in this form:
(
    [type] => 1
    [depth] => 
    [fingerprint] => 
    [null] => 
    [not] => 
    [joinType] => 
    [joinTable] => smw_object_ids
    [joinfield] => t0.smw_id
    [indexField] => 
    [from] =>  INNER JOIN (`smw_fpt_mdat` AS t2  INNER JOIN `smw_di_blob` AS t3 ON t2.s_id=t3.s_id) ON t0.smw_id=t2.s_id
    [fromTables] => Array
        (
            [nestedt2] => Array
                (
                    [t3] => smw_di_blob
                    [t2] => smw_fpt_mdat
                )

        )

    [joinConditions] => Array
        (
            [t3] => Array
                (
                    [0] => INNER JOIN
                    [1] => t2.s_id=t3.s_id
                )

            [nestedt2] => Array
                (
                    [0] => INNER JOIN
                    [1] => t0.smw_id=t2.s_id
                )

        )

    [where] => ((t3.p_id=519))
    [sortIndexField] => 
    [components] => Array
        (
        )

    [alias] => t0
    [sortfields] => Array
        (
            [] => t0.smw_sort
        )

    [queryNumber] => 0
)
*/
		$tables = [
			$querySegment['alias'] => $querySegment['joinTable']
		];
		$joins = [];
		$conds = $querySegment['where'] ?: [];

		if ( !empty( $querySegment['joinConditions'] ) ) {
			foreach ( $querySegment['joinConditions'] as $alias => $joinInfo ) {
				$aliasFixed = preg_replace( '/^nested/', '', $alias );
				$joins[$aliasFixed] = [ $joinInfo[0], [ $joinInfo[1] ] ];
			}
		}

		if ( !empty( $querySegment['fromTables'] ) ) {
			foreach ( $querySegment['fromTables'] as $nestedAlias => $nested ) {
				if ( is_array( $nested ) ) {
					foreach ( $nested as $alias => $table ) {
						$aliasFixed = preg_replace( '/^nested/', '', $alias );
						$tables[$aliasFixed] = $table;
					}
				} else {
					$tables[$nestedAlias] = $nested;
				}
			}
		}

		ksort( $tables );

		return [ $tables, $joins, $conds ];
	}

	private function getPanesOptions(
		PrintRequest $printRequest,
		string $canonicalLabel,
		array $searchPanesOptions,
		array $searchPanesParameterOptions
	): array {
		if ( empty( $canonicalLabel ) ) {
			return $this->searchPanesMainlabel( $printRequest, $searchPanesOptions, $searchPanesParameterOptions );
		}

		// create a new query for each printout/pane
		// and retrieve the query segment related to it
		// then perform the real query to get the results

		$queryParams = [
			'limit' => $this->datatables->query->getLimit(),
			'offset' => $this->datatables->query->getOffset(),
			'mainlabel' => $this->datatables->query->getMainlabel()
		];
		$queryParams = SMWQueryProcessor::getProcessedParams( $queryParams, [] );

		// @TODO @FIXME
		// get original description and add a conjunction
		// $queryDescription = $query->getDescription();
		// $queryCount = new \SMWQuery($queryDescription);
		// ...

		$isCategory = $printRequest->getMode() === PrintRequest::PRINT_CATS;

		// @TODO @FIXME cover PRINT_CHAIN as well
		$newQuery = SMWQueryProcessor::createQuery(
			$this->datatables->query->getQueryString() . ( !$isCategory ? '[[' . $canonicalLabel . '::+]]' : '' ),
			$queryParams,
			SMWQueryProcessor::INLINE_QUERY,
			''
		);

		$queryDescription = $newQuery->getDescription();
		$queryDescription->setPrintRequests( [ $printRequest ] );

		$conditionBuilder = $this->queryEngineFactory->newConditionBuilder();

		$rootid = $conditionBuilder->buildCondition( $newQuery );

		QuerySegment::$qnum = 0;
		$querySegmentList = $conditionBuilder->getQuerySegmentList();

		$querySegmentListProcessor = $this->queryEngineFactory->newQuerySegmentListProcessor();

		$querySegmentListProcessor->setQuerySegmentList( $querySegmentList );

		// execute query tree, resolve all dependencies
		$querySegmentListProcessor->process( $rootid );

		$qobj = $querySegmentList[$rootid];

		[ $tables, $joins, $conds ] = $this->parseQuerySegment( (array)$qobj );

		$property = new DIProperty( DIProperty::newFromUserLabel( $printRequest->getCanonicalLabel() ) );
		$propTypeid = $property->findPropertyValueType();

		if ( $isCategory ) {
			// data-length without the GROUP BY clause
			$sql_options_ = [ 'LIMIT' => 1 ];

			$tables_ = $tables;
			$tables_['insts'] = 'smw_fpt_inst';
			$fields_ = [ 'count' => 'COUNT(*)' ];
			$conds_ = $conds;
			$joins_ = $joins;
			$joins_['insts'] = [ 'JOIN', [ "$qobj->alias.smw_id = insts.s_id" ] ];

			$dataLength = (int)$this->connection->selectField(
				$tables_,
				$fields_,
				$conds_,
				__METHOD__,
				$sql_options_,
				$joins_
			);

			if ( !$dataLength ) {
				return [];
			}

			$groupBy = "i.smw_id";
			$orderBy = "count DESC, $groupBy ASC";
			$sql_options_ = [
				'GROUP BY' => $groupBy,
				// $this->query->getOption( 'count' ),
				'LIMIT' => $dataLength,
				'ORDER BY' => $orderBy,
				'HAVING' => 'count >= ' . $searchPanesOptions['minCount']
			];

			/*
			SELECT COUNT(i.smw_id), i.smw_id, i.smw_title FROM `smw_object_ids` AS t0
			JOIN `smw_fpt_inst` AS t1 ON t0.smw_id=t1.s_id
			JOIN `smw_fpt_inst` AS insts ON t0.smw_id=insts.s_id
			JOIN `smw_object_ids` AS i ON i.smw_id = insts.o_id
			WHERE (t1.o_id=1077)
			GROUP BY i.smw_id
			HAVING COUNT(i.smw_id) >= 1 ORDER BY COUNT(i.smw_id) DESC
			*/

			$tables_ = $tables;
			$tables_['insts'] = 'smw_fpt_inst';
			$tables_[SQLStore::ID_TABLE] = 'i';
			$joins_ = $joins;
			$joins_['insts'] = [ 'JOIN', [ "$qobj->alias.smw_id = insts.s_id" ] ];
			$joins_['i'] = [ 'JOIN', [ 'i.smw_id = insts.o_id' ] ];
			$conds_ = $conds;
			$fields_ = "COUNT($groupBy) AS count, i.smw_id, i.smw_title, i.smw_namespace, i.smw_iw, i.smw_sort, i.smw_subobject";

			$res = $this->connection->select(
				$tables_,
				$fields_,
				$conds_,
				__METHOD__,
				$sql_options_,
				$joins_
			);

			$isIdField = true;

		} else {
			$tableid = $this->datatables->store->findPropertyTableID( $property );

			$querySegmentList = array_reverse( $querySegmentList );

			// get aliases
			$p_alias = null;
			foreach ( $querySegmentList as $segment ) {
				if ( $segment->joinTable === $tableid ) {
					$p_alias = $segment->alias;
					break;
				}
			}

			if ( empty( $p_alias ) ) {
				$this->searchPanesLog[] = [
					'canonicalLabel' => $printRequest->getCanonicalLabel(),
					'error' => '$p_alias is null',
				];
				return [];
			}

			// data-length without the GROUP BY clause
			$sql_options_ = [ 'LIMIT' => 1 ];

			// SELECT COUNT(*) as count FROM `smw_object_ids` AS t0
			// INNER JOIN (`smw_fpt_mdat` AS t2 INNER JOIN `smw_di_wikipage` AS t3 ON t2.s_id=t3.s_id) ON t0.smw_id=t2.s_id
			// WHERE ((t3.p_id=517)) LIMIT 500

			$tables_ = $tables;
			$fields_ = [ 'count' => 'COUNT(*)' ];
			$conds_ = $conds;
			$joins_ = $joins;

			$dataLength = (int)$this->connection->selectField(
				$tables_,
				$fields_,
				$conds_,
				__METHOD__,
				$sql_options_,
				$joins_
			);

			if ( !$dataLength ) {
				return [];
			}

			[ $diType, $isIdField, $fields_, $groupBy, $orderBy ] = $this->fetchValuesByGroup( $property, $p_alias, $propTypeid );

			/*
			---GENERATED FROM DATATABLES
			SELECT t0.smw_id,t0.smw_title,t0.smw_namespace,t0.smw_iw,t0.smw_subobject,t0.smw_hash,t0.smw_sort,COUNT( t3.o_id ) as count FROM `smw_object_ids` AS t0 INNER JOIN (`smw_fpt_mdat` AS t2 INNER JOIN `smw_di_wikipage` AS t3 ON t2.s_id=t3.s_id <<and t3.s_id = smw_object_ids.smw_id>> ) ON t0.smw_id=t2.s_id  WHERE ((t3.p_id=517)) GROUP BY t3.o_id, t0.smw_id HAVING count >= 1 ORDER BY count DESC, t0.smw_sort ASC LIMIT 500

			---GENERATED ByGroupPropertyValuesLookup
			SELECT i.smw_id,i.smw_title,i.smw_namespace,i.smw_iw,i.smw_subobject,i.smw_hash,i.smw_sort,COUNT( p.o_id ) as count FROM `smw_object_ids` `o` INNER JOIN `smw_di_wikipage` `p` ON ((p.s_id=o.smw_id)) JOIN `smw_object_ids` `i` ON ((p.o_id=i.smw_id)) WHERE o.smw_hash IN ('1_-_A','1_-_Ab','1_-_Abc','10_-_Abcd','11_-_Abc') AND (o.smw_iw!=':smw') AND (o.smw_iw!=':smw-delete') AND p.p_id = 517 GROUP BY p.o_id, i.smw_id ORDER BY count DESC, i.smw_sort ASC
			*/

			$sql_options_ = [
				'GROUP BY' => $groupBy,
				// the following implies that if the user sets a threshold
				// close or equal to 1, and there are too many unique values,
				// the page will break, however the user has responsibility
				// for using searchPanes only for data reasonably grouped
				'LIMIT' => $dataLength,
				'ORDER BY' => $orderBy,
				'HAVING' => 'count >= ' . $searchPanesOptions['minCount']
			];

			// @see QueryEngine
			$tables_ = $tables;
			$joins_ = $joins;
			$conds_ = $conds;

			if ( $isIdField ) {
				$tables_['i'] = SQLStore::ID_TABLE;
				$joins_['i'] = [ 'JOIN', "$p_alias.o_id = i.smw_id" ];
				$conds_ .= !empty( $conds_ ) ? ' AND' : '';
				$conds_ .= ' i.smw_iw != ' . $this->connection->addQuotes( SMW_SQL3_SMWIW_OUTDATED );
				$conds_ .= ' AND i.smw_iw != ' . $this->connection->addQuotes( SMW_SQL3_SMWDELETEIW );
			}

			// perform the select
			$res = $this->connection->select(
				$tables_,
				$fields_,
				$conds_,
				__METHOD__,
				$sql_options_,
				$joins_
			);
		}

		// verify uniqueRatio

		// @see https://datatables.net/extensions/searchpanes/examples/initialisation/threshold.htm
		// @see https://github.com/DataTables/SearchPanes/blob/818900b75dba6238bf4b62a204fdd41a9b8944b7/src/SearchPane.ts#L824

		$threshold = !empty( $searchPanesParameterOptions['threshold'] ) ?
			$searchPanesParameterOptions['threshold'] : $searchPanesOptions['threshold'];

		$outputFormat = $printRequest->getOutputFormat();

		// *** if outputFormat is not set we can compute
		// uniqueness ratio by now, otherwise we have to
		// perform it after grouping the actual data
		if ( !$outputFormat ) {
			$binLength = $res->numRows();
			$uniqueRatio = $binLength / $dataLength;

			$this->searchPanesLog[] = [
				'canonicalLabel' => $printRequest->getCanonicalLabel(),
				'dataLength' => $dataLength,
				'binLength' => $binLength,
				'uniqueRatio' => $uniqueRatio,
				'threshold' => $threshold,
				'grouped' => false,
			];

			// || $binLength <= 1
			if ( $uniqueRatio > $threshold ) {
				return [];
			}
		}

		// @see ByGroupPropertyValuesLookup
		$diType = DataTypeRegistry::getInstance()->getDataItemByType(
			$propTypeid
		);

		$diHandler = $this->datatables->store->getDataItemHandlerForDIType(
			$diType
		);

		$fields = $diHandler->getFetchFields();

		$deepRedirectTargetResolver = ApplicationFactory::getInstance()
			->newMwCollaboratorFactory()->newDeepRedirectTargetResolver();

		$outputMode = SMW_OUTPUT_HTML;
		$isSubject = false;
		$groups = [];
		foreach ( $res as $row ) {

			if ( $isIdField ) {
				$dbKeys = [
					$row->smw_title,
					$row->smw_namespace,
					$row->smw_iw,
					$row->smw_sort,
					$row->smw_subobject
				];

			} else {
				$dbKeys = [];
				foreach ( $fields as $field => $fieldType ) {
					$dbKeys[] = $row->$field;
				}
			}

			$dbKeys = count( $dbKeys ) > 1 ? $dbKeys : $dbKeys[0];

			$dataItem = $diHandler->dataItemFromDBKeys(
				$dbKeys
			);

			// try to resolve redirect
			if ( $isIdField && $row->smw_iw === SMW_SQL3_SMWREDIIW ) {
				$redirectTarget = null;
				// @see SMWExportController
				try {
					$redirectTarget = $deepRedirectTargetResolver->findRedirectTargetFor( $dataItem->getTitle() );
				} catch ( \Exception $e ) {
				}
				if ( $redirectTarget ) {
					$dataItem = DIWikiPage::newFromTitle( $redirectTarget );
				}
			}

			$dataValue = DataValueFactory::getInstance()->newDataValueByItem(
				$dataItem,
				$property
			);

			if ( $outputFormat ) {
				$dataValue->setOutputFormat( $outputFormat );
			}

/*


					//  @see DIBlobHandler
					// $isKeyword = $dataItem->getOption( 'is.keyword' );

					if ( $propTypeid === '_keyw' ) {
						$value = $dataItem->normalize( $value );
					}

			*/
			$cellContent = $this->datatables->getCellContent(
				$printRequest->getCanonicalLabel(),
				[ $dataValue ],
				$outputMode,
				$isSubject,
				$propTypeid
			)['display'];

			if ( !array_key_exists( $cellContent, $groups ) ) {
				$groups[$cellContent] = [ 'count' => 0, 'value' => '' ];

				if ( $dataItem->getDiType() === DataItem::TYPE_TIME ) {
					// max Unix time
					$groups[$cellContent]['minDate'] = 2147483647;
					$groups[$cellContent]['maxDate'] = 0;
				}
			}

			$groups[$cellContent]['count'] += $row->count;

			// @TODO complete with all the possible transformations of
			// datavalues (DataValues/ValueFormatters)
			// based on $printRequest->getOutputFormat()
			// and provide to the API the information to
			// rebuild the query when values are grouped
			// by the output of the printout format, e.g.
			// if grouped by unit (for number datatype)
			// value should be *, for datetime see the
			// method below

			switch ( $dataItem->getDiType() ) {
				case DataItem::TYPE_NUMBER:
					if ( $outputFormat === '-u' ) {
						$value = '*';
					} else {
						$value = $dataValue->getNumber();
					}
					break;

				case DataItem::TYPE_BLOB:
					// @see IntlNumberFormatter
					// $requestedLength = intval( $outputFormat );
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_BOOLEAN:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_URI:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_TIME:
					$currentDate = $dataItem->asDateTime()->getTimestamp();
					$value = $dataValue->getISO8601Date();
					if ( $currentDate < $groups[$cellContent]['minDate'] ) {
						$groups[$cellContent]['minDate'] = $currentDate;
					}
					if ( $currentDate > $groups[$cellContent]['maxDate'] ) {
						$groups[$cellContent]['maxDate'] = $currentDate;
					}
					break;

				case DataItem::TYPE_GEO:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_CONTAINER:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_WIKIPAGE:
					$title_ = $dataValue->getTitle();
					if ( $title_ ) {
						$value = $title_->getFullText();
					} else {
						$value = $dataValue->getWikiValue();
						$this->searchPanesLog[] = [
							'canonicalLabel' => $printRequest->getCanonicalLabel(),
							'error' => 'TYPE_WIKIPAGE title is null',
							'wikiValue' => $value,
						];
					}
					break;

				case DataItem::TYPE_CONCEPT:
					$value = $dataValue->getWikiValue();
					break;

				case DataItem::TYPE_PROPERTY:
					break;
				case DataItem::TYPE_NOTYPE:
					$value = $dataValue->getWikiValue();
					break;

				default:
					$value = $dataValue->getWikiValue();

			}

			$groups[$cellContent]['value'] = $value;
		}

		if ( $outputFormat ) {
			$binLength = count( $groups );
			$uniqueRatio = $binLength / $dataLength;

			$this->searchPanesLog[] = [
				'canonicalLabel' => $printRequest->getCanonicalLabel(),
				'dataLength' => $dataLength,
				'binLength' => $binLength,
				'uniqueRatio' => $uniqueRatio,
				'threshold' => $threshold,
				'grouped' => true,
			];

			// || $binLength <= 1
			if ( $uniqueRatio > $threshold ) {
				return [];
			}

		}

		arsort( $groups, SORT_NUMERIC );

		$ret = [];
		foreach ( $groups as $content => $value ) {

			// @see https://www.semantic-mediawiki.org/wiki/Help:Search_operators
			// the latest value is returned, with the largest range
			if ( array_key_exists( 'minDate', $value ) && $value['minDate'] != $value['maxDate'] ) {
				// ISO 8601
				// @TODO use a symbol instead and transform from the API
				$value['value'] = '>' . date( 'c', $value['minDate'] ) . ']][[' . $printRequest->getCanonicalLabel() . '::<' . date( 'c', $value['maxDate'] );
			}

			$ret[] = [
				'label' => $content,
				'count' => $value['count'],
				'value' => $value['value']
			];
		}

		return $ret;
	}

	/**
	 * @see ByGroupPropertyValuesLookup
	 */
	private function fetchValuesByGroup( DIProperty $property, string $p_alias, string $propTypeId ): array {
		$tableid = $this->datatables->store->findPropertyTableID( $property );
		// $entityIdManager = $this->store->getObjectIds();

		$proptables = $this->datatables->store->getPropertyTables();

		// || $subjects === []
		if ( $tableid === '' || !isset( $proptables[$tableid] ) ) {
			return [];
		}

		$connection = $this->datatables->store->getConnection( 'mw.db' );

		$propTable = $proptables[$tableid];
		$isIdField = false;

		$diHandler = $this->datatables->store->getDataItemHandlerForDIType(
			$propTable->getDiType()
		);

		foreach ( $diHandler->getFetchFields() as $field => $fieldType ) {
			if ( !$isIdField && $fieldType === FieldType::FIELD_ID ) {
				$isIdField = true;
			}
		}

		$groupBy = $diHandler->getLabelField();
		$pid = '';

		if ( $groupBy === '' ) {
			$groupBy = $diHandler->getIndexField();
		}

		$groupBy = "$p_alias.$groupBy";
		$orderBy = "count DESC, $groupBy ASC";

		$diType = $propTable->getDiType();

		if ( $diType === DataItem::TYPE_WIKIPAGE ) {
			$fields = [
				"i.smw_id",
				"i.smw_title",
				"i.smw_namespace",
				"i.smw_iw",
				"i.smw_subobject",
				"i.smw_hash",
				"i.smw_sort",
				"COUNT( $groupBy ) as count"
			];

			$groupBy = "$p_alias.o_id, i.smw_id";
			$orderBy = "count DESC, i.smw_sort ASC";
		} elseif ( $diType === DataItem::TYPE_BLOB ) {
			$fields = [ "$p_alias.o_hash, $p_alias.o_blob", "COUNT( $p_alias.o_hash ) as count" ];

			// @see DIBlobHandler
			$groupBy = ( $propTypeId !== '_keyw' ? "$p_alias.o_hash, $p_alias.o_blob"
					: "$p_alias.o_hash" );

		} elseif ( $diType === DataItem::TYPE_URI ) {
			$fields = [ "$p_alias.o_serialized, $p_alias.o_blob", "COUNT( $p_alias.o_serialized ) as count" ];
			$groupBy = "$p_alias.o_serialized, $p_alias.o_blob";
		} elseif ( $diType === DataItem::TYPE_NUMBER ) {
			$fields = [ "$p_alias.o_serialized,$p_alias.o_sortkey, COUNT( $p_alias.o_serialized ) as count" ];
			$groupBy = "$p_alias.o_serialized,$p_alias.o_sortkey";
			$orderBy = "count DESC, $p_alias.o_sortkey DESC";
		} else {
			$fields = [ "$groupBy", "COUNT( $groupBy ) as count" ];
		}

		// if ( !$propTable->isFixedPropertyTable() ) {
		// 	$pid = $entityIdManager->getSMWPropertyID( $property );
		// }

		return [ $diType, $isIdField, $fields, $groupBy, $orderBy ];
	}

	private function searchPanesMainlabel( PrintRequest $printRequest, array $searchPanesOptions, array $searchPanesParameterOptions ): array {
		// mainlabel consists only of unique values,
		// so do not display if settings don't allow that
		if ( $searchPanesOptions['minCount'] > 1 ) {
			return [];
		}

		$threshold = !empty( $searchPanesParameterOptions['threshold'] ) ?
			$searchPanesParameterOptions['threshold'] : $searchPanesOptions['threshold'];

		$this->searchPanesLog[] = [
			'canonicalLabel' => 'mainLabel',
			'threshold' => $threshold,
		];

		// if ( $threshold < 1 ) {
		// 	return [];
		// }

		$query = $this->datatables->query;
		$queryDescription = $query->getDescription();
		$queryDescription->setPrintRequests( [] );

		$conditionBuilder = $this->queryEngineFactory->newConditionBuilder();
		$rootid = $conditionBuilder->buildCondition( $query );

		QuerySegment::$qnum = 0;
		$querySegmentList = $conditionBuilder->getQuerySegmentList();

		$querySegmentListProcessor = $this->queryEngineFactory->newQuerySegmentListProcessor();

		$querySegmentListProcessor->setQuerySegmentList( $querySegmentList );

		// execute query tree, resolve all dependencies
		$querySegmentListProcessor->process( $rootid );

		$qobj = $querySegmentList[$rootid];

		[ $tables, $joins, $conds ] = $this->parseQuerySegment( (array)$qobj );

		$sql_options_ = [
			// *** should we set a limit here ?
			// it makes sense to show the pane for
			// mainlabel only when page titles are grouped
			// through the printout format or even the printout template
			// title
			'ORDER BY' => 't'
		];

		// Selecting those is required in standard SQL (but MySQL does not require it).
		$sortfields = implode( ',', $qobj->sortfields );

		// @see QueryEngine
		$tables_ = $tables;
		$fields_ = [];
		$fields_['id'] = "$qobj->alias.smw_id";
		$fields_['t'] = "$qobj->alias.smw_title";
		$fields_['ns'] = "$qobj->alias.smw_namespace";
		$fields_['iw'] = "$qobj->alias.smw_iw";
		$fields_['so'] = "$qobj->alias.smw_subobject";
		$fields_['sortkey'] = "$qobj->alias.smw_sortkey";
		$fields_[] = $sortfields;

		$conds_ = $conds;
		$joins_ = $joins;

		$res = $this->connection->select(
			$tables_,
			$fields_,
			$conds_,
			__METHOD__,
			$sql_options_,
			$joins_
		);

		$diHandler = $this->datatables->store->getDataItemHandlerForDIType(
			DataItem::TYPE_WIKIPAGE
		);

		$outputMode = SMW_OUTPUT_HTML;
		$isSubject = false;
		$groups = [];
		foreach ( $res as $row ) {
			$dataItem = $diHandler->dataItemFromDBKeys( [
				$row->t,
				intval( $row->ns ),
				$row->iw,
				'',
				$row->so
			] );

			$dataValue = DataValueFactory::getInstance()->newDataValueByItem(
				$dataItem
			);

			if ( $printRequest->getOutputFormat() ) {
				$dataValue->setOutputFormat( $printRequest->getOutputFormat() );
			}

			$cellContent = $this->datatables->getCellContent(
				$printRequest->getCanonicalLabel(),
				[ $dataValue ],
				$outputMode,
				$isSubject
			)['display'];

			if ( !array_key_exists( $cellContent, $groups ) ) {
				$groups[$cellContent] = [ 'count' => 0, 'value' => '' ];
			}

			$groups[$cellContent]['count']++;
			$groups[$cellContent]['value'] = $dataValue->getTitle()->getText();
		}

		arsort( $groups, SORT_NUMERIC );

		$ret = [];
		foreach ( $groups as $content => $value ) {
			$ret[] = [
				'label' => $content,
				'value' => $value['value'],
				'count' => $value['count']
			];
		}

		return $ret;
	}

}
