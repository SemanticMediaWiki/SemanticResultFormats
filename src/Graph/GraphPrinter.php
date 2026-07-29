<?php

namespace SRF\Graph;

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use SMW\Query\PrintRequest;
use SMW\Query\QueryResult;
use SMW\Query\Result\ResultArray;
use SMW\Query\ResultPrinters\ResultPrinter;

/**
 * SMW result printer for graphs using graphViz.
 * In order to use this printer you need to have both
 * the graphViz library installed on your system and
 * have the graphViz, Diagrams or ExternalData MediaWiki extension installed.
 *
 * @file SRF_Graph.php
 * @ingroup SemanticResultFormats
 *
 * @license GPL-2.0-or-later
 * @author Frank Dengler
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Sebastian Schmid
 */
class GraphPrinter extends ResultPrinter {

	// @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/pull/4273
	// Implement `ResultPrinterDependency` once SMW 3.1 becomes mandatory

	public const NODELABEL_DISPLAYTITLE = 'displaytitle';
	public static $NODE_LABELS = [
		self::NODELABEL_DISPLAYTITLE,
	];
	/** @const string[] PAGETYPES SMW types that represent SMW pages and should always be displayed as nodes. */
	private const PAGETYPES = [ '_wpg', '_wpp', '_wps', '_wpu', '__sup', '__sin', '__suc', '__con' ];

	public static $NODE_SHAPES = [
		'box',
		'box3d',
		'circle',
		'component',
		'diamond',
		'doublecircle',
		'doubleoctagon',
		'egg',
		'ellipse',
		'folder',
		'hexagon',
		'house',
		'invhouse',
		'invtrapezium',
		'invtriangle',
		'Mcircle',
		'Mdiamond',
		'Msquare',
		'Mrecord',
		'none',
		'note',
		'octagon',
		'parallelogram',
		'pentagon ',
		'plaintext',
		'point',
		'polygon',
		'rect',
		'record',
		'rectangle',
		'septagon',
		'square',
		'tab',
		'trapezium',
		'triangle',
		'tripleoctagon',
	];

	public static $ARROW_SHAPES = [
		'box',
		'crow',
		'curve',
		'icurve',
		'diamond',
		'dot',
		'inv',
		'none',
		'normal',
		'tee',
		'vee',
	];

	/** @var GraphNode[] */
	private $nodes = [];
	private $options;

	public function getName() {
		return $this->msg( 'srf-printername-graph' )->text();
	}

	/**
	 * @see ResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ): void {
		parent::handleParameters( $params, $outputmode );

		$this->options = new GraphOptions( $params );
	}

	/**
	 * @see ResultPrinterDependency::hasMissingDependency
	 *
	 * {@inheritDoc}
	 */
	public function hasMissingDependency() {
		$registry = \ExtensionRegistry::getInstance();
		return (
			// <graphviz> can be provided by Diagrams.
			!$registry->isLoaded( 'Diagrams' ) &&
			!class_exists( 'GraphViz' ) && !class_exists( '\\MediaWiki\\Extension\\GraphViz\\GraphViz' )
		) && !(
			// <graphviz can also be added by External Data in Tag emulation mode.
			$registry->isLoaded( 'External Data' ) &&
			in_array( 'graphviz', MediaWikiServices::getInstance()->getParser()->getTags() )
		);
	}

	/**
	 * @see ResultPrinterDependency::getDependencyError
	 *
	 * {@inheritDoc}
	 */
	public function getDependencyError() {
		return Html::rawElement(
			'div',
			[
				'class' => 'smw-callout smw-callout-error'
			],
			'The SRF Graph printer requires the GraphViz, Diagrams or External Data ' .
			'(with &lt;graphviz&gt; tag defined in Tag emulation mode) extension to be installed.'
		);
	}

	/**
	 * @param QueryResult $res
	 * @param $outputmode
	 *
	 * @return string
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {
		// Remove this once SRF requires 3.1+
		if ( $this->hasMissingDependency() ) {
			return $this->getDependencyError();
		}

		// iterate query result and create SRF\GraphNodes
		while ( $row = $res->getNext() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$this->processResultRow( $row );
		}

		// use GraphFormatter to build the graph
		$graphFormatter = new GraphFormatter( $this->options );
		$graphFormatter->buildGraph( $this->nodes );

		// GraphViz is not working for version >= 1.33, so we need to use the Diagrams or External Data extension
		// and formatting is a little different from the GraphViz extension
		if ( \ExtensionRegistry::getInstance()->isLoaded( 'Diagrams' ) ) {
			// Using Diagrams extension.
			$result = "<graphviz>{$graphFormatter->getGraph()}</graphviz>";
		} else {
			// Calls graphvizParserHook function from MediaWiki GraphViz or External Data extension
			$parser = MediaWikiServices::getInstance()->getParser();
			$result = $parser->recursiveTagParse( '<graphviz>' . $graphFormatter->getGraph() . '</graphviz>' );
		}

		// Append legend
		$result .= $graphFormatter->getGraphLegend();

		if ( $outputmode === SMW_OUTPUT_HTML ) {
			return $result;
		}

		return MediaWikiServices::getInstance()->getParser()->recursiveTagParse( $result );
	}

	/**
	 * Process a result row and create SRF\GraphNodes
	 *
	 * @since 3.1
	 *
	 * @param ResultArray[] $row
	 */
	protected function processResultRow( array $row ) {
		// ResultArray's value iterator is stateful and single-pass, so every value is read
		// out and classified exactly once, up front. The row is then processed in two
		// passes over that classification: determineNode() first, so that a node
		// candidate appearing after an earlier column - e.g. a PRINT_THIS ("?=") column
		// placed after other printouts - is already known before collectEdgesAndFields()
		// decides what any earlier column's value becomes.
		$values = $this->readRowValues( $row );

		$node = $this->determineNode( $values );
		[ $parents, $fields ] = $this->collectEdgesAndFields( $values, $node );

		if ( $node ) {
			foreach ( $parents as $parent ) {
				if ( !empty( $parent['object'] ) ) {
					$node->addParentNode( $parent['predicate'], $parent['object'] );
				}
			}
			foreach ( $fields as $field ) {
				if ( $field['value'] !== '' ) {
					$node->addField(
						$field['name'],
						$field['value'],
						$field['type'],
						$field['page'],
						$field['valueLink'] ?? null
					);
				}
			}
			$this->nodes[] = $node;
		}
	}

	/**
	 * Reads every data value out of the row's ResultArrays and classifies each one against
	 * the current display options, without yet knowing which value (if any) becomes the
	 * row's node.
	 *
	 * @since 4.0
	 *
	 * @param ResultArray[] $row
	 *
	 * @return GraphRowValue[]
	 */
	private function readRowValues( array $row ): array {
		$showGraphFieldsPages = $this->options->showGraphFieldsPages();
		$showGraphFields = $this->options->showGraphFields();
		$pageTypeSeen = 0;
		$values = [];

		foreach ( $row as $result_array ) {
			$request = $result_array->getPrintRequest();
			$type = $request->getTypeID();
			$isPageType = in_array( $type, self::PAGETYPES );
			$isThisPrintout = $request->isMode( PrintRequest::PRINT_THIS );
			$canonicalLabel = $request->getCanonicalLabel();
			// getLabel() already defaults to the canonical label when no "=" override was
			// given, so an empty string here means the label was explicitly suppressed
			// (e.g. "?Property=") and must stay empty rather than falling back to the
			// canonical property name (see issue #1131). Only a genuinely unset label
			// (null) falls back to the canonical label / '?'.
			$label = $request->getLabel() ?? $canonicalLabel ?? '?';

			if ( $isPageType ) {
				$pageTypeSeen++;
			}

			$showAsEdge = !$showGraphFields || $isPageType || $request->isMode( PrintRequest::PRINT_CHAIN );

			while ( ( $object = $result_array->getNextDataValue() ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				$objectText = $isPageType
					? ( $object->getDisplayTitle() ?: $object->getWikiValue() )
					: $object->getWikiValue();

				$values[] = new GraphRowValue(
					$object,
					$objectText,
					(bool)$object->getProperty(),
					$type,
					$isPageType,
					$isThisPrintout,
					$label,
					$canonicalLabel,
					$pageTypeSeen,
					$showAsEdge,
					!$showGraphFields || $isPageType || $request->isMode( PrintRequest::PRINT_CHAIN ),
					$showGraphFields && ( !$isPageType || $showGraphFieldsPages ),
				);
			}
		}

		return $values;
	}

	/**
	 * Picks the value that becomes the row's node. Node identity is anchored to the query
	 * subject (the PRINT_THIS printout) whenever the row has one, regardless of column
	 * position: every other page-type value is skipped as a node candidate, and PRINT_THIS
	 * itself is never skipped. Falls back to the first property-less page-type value when
	 * the row has no PRINT_THIS printout at all.
	 *
	 * @since 4.0
	 *
	 * @param GraphRowValue[] $values
	 */
	private function determineNode( array $values ): ?GraphNode {
		$hasThisPrintout = false;
		foreach ( $values as $value ) {
			if ( $value->isThisPrintout ) {
				$hasThisPrintout = true;
				break;
			}
		}

		foreach ( $values as $value ) {
			$skipNode = $hasThisPrintout
				? ( $value->isPageType && !$value->isThisPrintout )
				: ( $value->isPageType && $value->pageTypeSeen > 1 );

			if ( !$value->hasProperty && !$skipNode ) {
				$node = new GraphNode( $value->objectText );
				$node->setLabel( $value->object->getPreferredCaption() ?: $value->object->getText() );
				return $node;
			}
		}

		return null;
	}

	/**
	 * Collects the parent-node (edge) and field entries for the already-determined node.
	 *
	 * @since 4.0
	 *
	 * @param GraphRowValue[] $values
	 *
	 * @return array{0: array, 1: array} [ $parents, $fields ]
	 */
	private function collectEdgesAndFields( array $values, ?GraphNode $node ): array {
		$showGraphFieldsPages = $this->options->showGraphFieldsPages();
		$parents = [];
		$fields = [];

		foreach ( $values as $value ) {
			// Handle edge
			if ( $showGraphFieldsPages ) {
				if ( $value->includeAsEdge && $node ) {
					if ( $value->objectText !== $node->getId() && $value->pageTypeSeen === 2 ) {
						$parents[] = [
							'predicate' => $value->label,
							'object' => $value->objectText,
						];
					}
				}
			} elseif ( $value->showAsEdge ) {
				if ( $node && $value->objectText !== $node->getId() ) {
					$parents[] = [
						'predicate' => $value->label,
						'object' => $value->objectText,
					];
				}
				continue;
			}

			// Handle field in info box for node
			if ( $showGraphFieldsPages && $value->includeAsField ) {
				if ( $value->hasProperty || !$value->isPageType ) {
					// if is Page type, only add if seen more than once
					if ( $value->isPageType && $value->pageTypeSeen > 2 ) {
						$fields[] = [
							'name' => $value->label,
							'value' => $value->object->getDisplayTitle(),
							'valueLink' => $value->object->getShortWikiText(),
							'type' => $value->type,
							'page' => $value->canonicalLabel,
						];
					}
					// if is not Page type, always add
					if ( !$value->isPageType ) {
						$fields[] = [
							'name' => $value->label,
							'value' => $value->objectText,
							'type' => $value->type,
							'page' => $value->canonicalLabel,
						];
					}
				}
			} elseif ( !$showGraphFieldsPages && !$value->showAsEdge ) {
				$fields[] = [
					'name' => $value->label,
					'value' => $value->objectText,
					'type' => $value->type,
					'page' => $value->canonicalLabel,
				];
			}
		}

		return [ $parents, $fields ];
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
	public function getParamDefinitions( array $definitions ): array {
		$params = parent::getParamDefinitions( $definitions );

		$params['graphname'] = [
			'default' => 'QueryResult',
			'message' => 'srf-paramdesc-graphname',
		];

		$params['graphsize'] = [
			'type' => 'string',
			'default' => '',
			'message' => 'srf-paramdesc-graphsize',
			'manipulatedefault' => false,
		];

		$params['graphfontsize'] = [
			'type' => 'integer',
			'default' => 10,
			'message' => 'srf-paramdesc-graphfontsize',
			'manipulatedefault' => false,
		];

		$params['graphlegend'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-graphlegend',
		];

		$params['graphlabel'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-graphlabel',
		];

		$params['graphlink'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-graphlink',
		];

		$params['graphcolor'] = [
			'type' => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-graphcolor',
		];

		$params['arrowdirection'] = [
			'aliases' => 'rankdir',
			'default' => 'LR',
			'message' => 'srf-paramdesc-rankdir',
			'values'  => [ 'LR', 'RL', 'TB', 'BT' ],
		];

		$params['arrowhead'] = [
			'default' => 'normal',
			'message' => 'srf-paramdesc-arrowhead',
			'values' => self::$ARROW_SHAPES,
		];

		$params['nodeshape'] = [
			'default' => false,
			'message' => 'srf-paramdesc-graph-nodeshape',
			'manipulatedefault' => false,
			'values' => self::$NODE_SHAPES,
		];

		$params['relation'] = [
			'default' => 'child',
			'message' => 'srf-paramdesc-graph-relation',
			'manipulatedefault' => false,
			'values' => [ 'parent', 'child' ],
		];

		$params['wordwraplimit'] = [
			'type' => 'integer',
			'default' => 25,
			'message' => 'srf-paramdesc-graph-wwl',
			'manipulatedefault' => false,
		];

		$params['nodelabel'] = [
			'default' => '',
			'message' => 'srf-paramdesc-nodelabel',
			'values' => self::$NODE_LABELS,
		];

		$params['graphfields'] = [
			'default' => false,
			'message' => 'srf-paramdesc-graphfields',
			'manipluatedefault' => false,
			'type' => 'boolean'
		];

		$params['graphfieldspages'] = [
			'default' => false,
			'message' => 'srf-paramdesc-graphfieldspages',
			'type' => 'boolean'
		];

		return $params;
	}
}
