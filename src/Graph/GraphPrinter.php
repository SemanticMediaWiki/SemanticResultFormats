<?php

namespace SRF\Graph;

use SMW\ResultPrinter;
use SMWQueryResult;
use SMWWikiPageValue;
use GraphViz;
use Html;

/**
 * SMW result printer for graphs using graphViz.
 * In order to use this printer you need to have both
 * the graphViz library installed on your system and
 * have the graphViz MediaWiki extension installed.
 *
 * @file SRF_Graph.php
 * @ingroup SemanticResultFormats
 *
 * @licence GNU GPL v2+
 * @author Frank Dengler
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Sebastian Schmid
 */
class GraphPrinter extends ResultPrinter {

	//@see https://github.com/SemanticMediaWiki/SemanticMediaWiki/pull/4273
	// Implement `ResultPrinterDependency` once SMW 3.1 becomes mandatory

	const NODELABEL_DISPLAYTITLE = 'displaytitle';
	public static $NODE_LABELS = [
		self::NODELABEL_DISPLAYTITLE,
	];

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

	private $nodes = [];
	private $options;

	public function getName() {
		return $this->msg( 'srf-printername-graph' )->text();
	}

	/**
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		$this->options = new GraphOptions($params);
	}

	/**
	 * @see ResultPrinterDependency::hasMissingDependency
	 *
	 * {@inheritDoc}
	 */
	public function hasMissingDependency() {
		return !class_exists( 'GraphViz' ) && !class_exists( '\\MediaWiki\\Extension\\GraphViz\\GraphViz' );
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
			'The SRF Graph printer requires the GraphViz extension to be installed.'
		);
	}

	/**
	 * @param SMWQueryResult $res
	 * @param $outputmode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {

		// Remove this once SRF requires 3.1+
		if ( $this->hasMissingDependency() ) {
			return $this->getDependencyError();
		}

		// iterate query result and create SRF\GraphNodes
		while ( $row = $res->getNext() ) {
			$this->processResultRow( $row );
		}

		// use GraphFormater to build the graph
		$graphFormatter = new GraphFormatter( $this->options );
		$graphFormatter->buildGraph( $this->nodes );

		// Calls graphvizParserHook function from MediaWiki GraphViz extension
		$result = $GLOBALS['wgParser']->recursiveTagParse( "<graphviz>" . $graphFormatter->getGraph
				() . "</graphviz>" );

		// append legend
		$result .= $graphFormatter->getGraphLegend();

		return $result;
	}

	/**
	 * Process a result row and create SRF\GraphNodes
	 *
	 * @since 3.1
	 *
	 * @param array $row
	 *
	 */
	protected function processResultRow( array /* of SMWResultArray */ $row ) {

		// loop through all row fields
		foreach ( $row as $i => $resultArray ) {

			// loop through all values of a multivalue field
			while ( ( /* SMWWikiPageValue */
				$object = $resultArray->getNextDataValue() ) !== false ) {

				// create SRF\GraphNode for column 0
				if ( $i == 0 ) {
					$node = new GraphNode( $object->getShortWikiText() );
					$node->setLabel( $object->getPreferredCaption() );
					$this->nodes[] = $node;
				} else {
					$node->addParentNode( $resultArray->getPrintRequest()->getLabel(), $object->getShortWikiText() );
				}
			}
		}
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

		return $params;
	}
}
