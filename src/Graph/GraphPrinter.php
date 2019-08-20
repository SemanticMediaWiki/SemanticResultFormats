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
		'none',
		'note',
		'octagon',
		'parallelogram',
		'pentagon ',
		'plaintext',
		'point',
		'polygon',
		'rect',
		'rectangle',
		'septagon',
		'square',
		'tab',
		'trapezium',
		'triangle',
		'tripleoctagon',
	];
	protected $nodeShape;
	protected $nodes = [];
	protected $nodeLabel;
	protected $parentRelation;

	protected $graphName;
	protected $graphSize;
	protected $graphColors = [
		'black',
		'red',
		'green',
		'blue',
		'darkviolet',
		'gold',
		'deeppink',
		'brown',
		'bisque',
		'darkgreen',
		'yellow',
		'darkblue',
		'magenta',
		'steelblue2'
	];

	protected $showGraphColor;
	protected $showGraphLabel;
	protected $showGraphLegend;
	protected $enableGraphLink;

	protected $rankdir;
	protected $legendItem = [];
	protected $nameProperty;
	protected $wordWrapLimit;


	public function getName() {
		return $this->msg( 'srf-printername-graph' )->text();
	}


	/**
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		$this->graphName = trim( $params['graphname'] );
		$this->graphSize = trim( $params['graphsize'] );
		$this->showGraphLegend = $params['graphlegend'];
		$this->showGraphLabel = $params['graphlabel'];
		$this->enableGraphLink = $params['graphlink'];
		$this->showGraphColor = $params['graphcolor'];
		$this->rankdir = strtoupper( trim( $params['arrowdirection'] ) );
		$this->nameProperty = $params['nameproperty'] === false ? false : trim( $params['nameproperty'] );
		$this->parentRelation =
			strtolower( trim( $params['relation'] ) ) == 'parent';        // false if anything other than 'parent'
		$this->nodeShape = $params['nodeshape'];
		$this->wordWrapLimit = $params['wordwraplimit'];
		$this->nodeLabel = $params['nodelabel'];
	}


	/**
	 * @param SMWQueryResult $res
	 * @param $outputmode
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		if ( !class_exists( 'GraphViz' )
			&& !class_exists( '\\MediaWiki\\Extension\\GraphViz\\GraphViz' )
		) {
			wfWarn( 'The SRF Graph printer needs the GraphViz extension to be installed.' );
			return '';
		}

		// set name of current graph
		$graphInput = "digraph $this->graphName {";

		// set fontsize and fontname of graph, nodes and edges
		$graphInput .= "graph [fontsize=10, fontname=\"Verdana\"]\n";
		$graphInput .= "node [fontsize=10, fontname=\"Verdana\"];\n";
		$graphInput .= "edge [fontsize=10, fontname=\"Verdana\"];\n";

		// choose graphsize, nodeshapes and rank direction
		if ( $this->graphSize != '' ) {
			$graphInput .= "size=\"$this->graphSize\";";
		}

		if ( $this->nodeShape ) {
			$graphInput .= "node [shape=$this->nodeShape];";
		}

		$graphInput .= "rankdir=$this->rankdir;";

		$nodeLabel = '';

		// iterate query result and create SRF\GraphNodes
		while ( $row = $res->getNext() ) {
			$this->processResultRow( $row );
		}

		/** @var \SRF\GraphNode $node */
		foreach ( $this->nodes as $node ) {

			$nodeName = $node->getID();

			// take "displaytitle" as node-label if it is set
			if ( $this->nodeLabel === self::NODELABEL_DISPLAYTITLE) {
				$objectDisplayTitle = $node->getLabel();
				if ( !empty( $objectDisplayTitle )) {
					$nodeLabel = $this->getWordWrappedText( $objectDisplayTitle,
						$this->wordWrapLimit );
				}
			}

			// add the node
			$graphInput .= "\"" . $nodeName . "\"";

			if ( $this->enableGraphLink ) {

				$nodeLinkURL = "[[" . $nodeName . "]]";

				if( $nodeLabel === '' ) {
					$graphInput .= " [URL = \"$nodeLinkURL\"]";
				} else {
					$graphInput .= " [URL = \"$nodeLinkURL\", label = \"$nodeLabel\"]";
				}
			}
			$graphInput .= "; ";
		}

		foreach ( $this->nodes as $node ) {

			if ( count( $node->getParentNode() ) > 0 ) {

				$nodeName = $node->getID();

				foreach ( $node->getParentNode() as $parentNode ) {

					// handle parent/child switch (parentRelation)
					$graphInput .= $this->parentRelation ? " \"" . $parentNode['object'] . "\" -> \"" . $nodeName . "\""
						: " \"" . $nodeName . "\" -> \"" . $parentNode['object'] . "\" ";

					if ( $this->showGraphLabel || $this->showGraphColor ) {
						$graphInput .= ' [';

						// add legend item only if missing
						if ( array_search( $parentNode['predicate'], $this->legendItem, true ) === false ) {
							$this->legendItem[] = $parentNode['predicate'];
						}

						// assign color
						$color = $this->graphColors[array_search( $parentNode['predicate'], $this->legendItem, true )];

						// show arrow label (graphLabel is misleading but kept for compatibility reasons)
						if ( $this->showGraphLabel ) {
							$graphInput .= "label=\"" . $parentNode['predicate'] . "\"";
							if ( $this->showGraphColor ) {
								$graphInput .= ",fontcolor=$color,";
							}
						}

						// colorize arrow
						if ( $this->showGraphColor ) {
							$graphInput .= "color=$color";
						}
						$graphInput .= ']';
					}
				}
				$graphInput .= ';';
			}
		}
		$graphInput .= "}";

		// Calls graphvizParserHook function from MediaWiki GraphViz extension
		$result = $GLOBALS['wgParser']->recursiveTagParse( "<graphviz>$graphInput</graphviz>" );

		// append legend
		$result .= $this->getGraphLegend();

		return $result;
	}

	/**
	 * Creates the graph legend
	 *
	 * @since 3.1
	 *
	 * @return string Html::rawElement
	 *
	 */
	protected function getGraphLegend(){
		if ( $this->showGraphLegend && $this->showGraphColor ) {
			$itemsHtml = '';
			$colorCount = 0;
			$arraySize = count( $this->graphColors );

			foreach ( $this->legendItem as $legendLabel ) {
				if ( $colorCount >= $arraySize ) {
					$colorCount = 0;
				}

				$color = $this->graphColors[$colorCount];
				$itemsHtml .= Html::rawElement( 'div', [ 'class' => 'graphlegenditem', 'style' => "color: $color" ],
					"$color: $legendLabel" );

				$colorCount ++;
			}

			return Html::rawElement( 'div', [ 'class' => 'graphlegend' ], "$itemsHtml" );
		}
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
					$node->setLabel($object->getDisplayTitle());
					$this->nodes[] = $node;
				} else {
					$node->addParentNode( $resultArray->getPrintRequest()->getLabel(), $object->getShortWikiText() );
				}
			}
		}
	}

	/**
	 * Returns the word wrapped version of the provided text.
	 *
	 * @since 1.5.4
	 *
	 * @param string $text
	 * @param integer $charLimit
	 *
	 * @return string
	 */
	protected function getWordWrappedText( $text, $charLimit ) {
		$charLimit = max( [ $charLimit, 1 ] );
		$segments = [];

		while ( strlen( $text ) > $charLimit ) {
			// Find the last space in the allowed range.
			$splitPosition = strrpos( substr( $text, 0, $charLimit ), ' ' );

			if ( $splitPosition === false ) {
				// If there is no space (lond word), find the next space.
				$splitPosition = strpos( $text, ' ' );

				if ( $splitPosition === false ) {
					// If there are no spaces, everything goes on one line.
					$splitPosition = strlen( $text ) - 1;
				}
			}

			$segments[] = substr( $text, 0, $splitPosition + 1 );
			$text = substr( $text, $splitPosition + 1 );
		}

		$segments[] = $text;

		return implode( '\n', $segments );
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

		$params['nameproperty'] = [
			'default' => false,
			'message' => 'srf-paramdesc-graph-nameprop',
			'manipulatedefault' => false,
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
