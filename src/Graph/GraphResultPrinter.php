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
class GraphResultPrinter extends ResultPrinter {

	public static $ARROW_HEAD = [
		'none',
		'normal',
		'inv',
		'dot',
		'odot',
		'tee',
		'invdot',
		'invodot',
		'empty',
		'invempty',
		'diamond',
		'ediamond',
		'odiamond',
		'crow',
		'obox',
		'box',
		'open',
		'vee',
		'circle',
		'halfopen'
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
		'record',
		'Mrecord'
	];

	protected $graphName;
	protected $graphLabel;
	protected $graphColor;
	protected $graphLegend;
	protected $graphLink;
	protected $rankdir;
	protected $graphSize;
	protected $legendItem = [];
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
	protected $nameProperty;
	protected $nodeShape;
	protected $parentRelation;
	protected $wordWrapLimit;
	protected $arrowHead;
	protected $nodes = [];


	public function getName() {
		return $this->msg( 'srf-printername-graph' )->text();
	}


	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		$this->graphName = trim( $params['graphname'] );
		$this->graphSize = trim( $params['graphsize'] );
		$this->graphLegend = $params['graphlegend'];
		$this->graphLabel = $params['graphlabel'];
		$this->rankdir = strtoupper( trim( $params['arrowdirection'] ) );
		$this->graphLink = $params['graphlink'];
		$this->graphColor = $params['graphcolor'];
		$this->arrowHead = $params['arrowhead'];
		$this->nameProperty = $params['nameproperty'] === false ? false : trim( $params['nameproperty'] );
		$this->parentRelation =
			strtolower( trim( $params['relation'] ) ) == 'parent';        // false if anything other than 'parent'
		$this->nodeShape = $params['nodeshape'];
		$this->wordWrapLimit = $params['wordwraplimit'];
	}


	/**
	 * @param SMWQueryResult $res
	 * @param $outputmode
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		if ( !is_callable( 'GraphViz::graphvizParserHook' ) ) {
			wfWarn( 'The SRF Graph printer needs the GraphViz extension to be installed.' );

			return '';
		}

		$this->isHTML = true;

		///////////////////////////////////
		// GRAPH OPTIONS
		///////////////////////////////////        

		$graphInput = "digraph $this->graphName {";

		// fontsize and fontname
		$graphInput .= "graph [fontsize=10, fontname=\"Verdana\"]\nnode [fontsize=10, fontname=\"Verdana\"];\nedge [fontsize=10, fontname=\"Verdana\"];";

		// size
		if ( $this->graphSize != '' ) {
			$graphInput .= "size=\"$this->graphSize\";";
		}

		// shape
		if ( $this->nodeShape ) {
			$graphInput .= "node [shape=$this->nodeShape];";
		}

		// rankdir
		$graphInput .= "rankdir=$this->rankdir;";

		///////////////////////////////////
		// NODES
		///////////////////////////////////

		// iterate query result and create GraphNodes
		while ( $row = $res->getNext() ) {
			$this->processResultRow( $row, $outputmode, $this->nodes );
		}

		/** @var \SRF\GraphNode $node */
		foreach ( $this->nodes as $node ) {

			// take node ID (title) if we don't have a label1
			$nodeName = ( empty( $node->getLabel( 1 ) ) ) ? $node->getID() : $node->getLabel( 1 );

			// add the node
			$graphInput .= "\"" . $nodeName . "\"";

			if ( $this->graphLink ) {
				$nodeLinkURL = "[[" . $node->getID() . "]]";
				$graphInput .= "[URL = \"$nodeLinkURL\"] ";
			}

			// build the additional labels only for record or Mrecord
			if ( ( $node->getLabel( 2 ) != "" || $node->getLabel( 3 ) != "" ) &&
				 ( $this->nodeShape == "record" || $this->nodeShape == "Mrecord" )
			) {

				// label1
				$label = ( empty( $node->getLabel( 1 ) ) ) ? $node->getID() : $node->getLabel( 1 );
				$graphInput .= "[label=\"{" . $label;

				// label2 onwards
				foreach ( $node->getLabels() as $labelIndex => $label ) {
					if ( $labelIndex < 2 ) {
						continue;
					}
					if ( $label != "" ) {
						$graphInput .= "|" . $label;
					}
				}

				$graphInput .= " }\"];";
			} else {
				$graphInput .= ";";
			}
		}

		///////////////////////////////////
		// EDGES
		///////////////////////////////////

		foreach ( $this->nodes as $node ) {

			if ( count( $node->getParentNode() ) > 0 ) {

				$nodeName = ( empty( $node->getLabel( 1 ) ) ) ? $node->getID() : $node->getLabel( 1 );

				foreach ( $node->getParentNode() as $parentNode ) {

					// handle parent/child switch (parentRelation)
					$graphInput .= $this->parentRelation ? " \"" . $parentNode['object'] . "\" -> \"" . $nodeName . "\""
						: " \"" . $nodeName . "\" -> \"" . $parentNode['object'] . "\" ";

					$graphInput .= "[arrowhead = " . $this->arrowHead . "]";

					if ( $this->graphLabel || $this->graphColor ) {
						$graphInput .= ' [';

						// add legend item only if missing
						if ( array_search( $parentNode['predicate'], $this->legendItem, true ) === false ) {
							$this->legendItem[] = $parentNode['predicate'];
						}

						// assign color
						$color = $this->graphColors[array_search( $parentNode['predicate'], $this->legendItem, true )];

						// show arrow label (graphLabel is misleading but kept for compatibility reasons)
						if ( $this->graphLabel ) {
							$graphInput .= "label=\"" . $parentNode['predicate'] . "\"";
							if ( $this->graphColor ) {
								$graphInput .= ",fontcolor=$color,";
							}
						}

						// colorize arrow
						if ( $this->graphColor ) {
							$graphInput .= "color=$color";
						}
						$graphInput .= ']';
					}
				}
				$graphInput .= ';';
			}
		}
		$graphInput .= "}";


		// calls graphvizParserHook from GraphViz extension
		$result = GraphViz::graphvizParserHook( $graphInput, "", $GLOBALS['wgParser'], true );


		// append legend
		if ( $this->graphLegend && $this->graphColor ) {
			$itemsHtml = '';
			$colorCount = 0;
			$arraySize = count( $this->graphColors );

			foreach ( $this->legendItem as $m_label ) {
				if ( $colorCount >= $arraySize ) {
					$colorCount = 0;
				}

				$color = $this->graphColors[$colorCount];
				$itemsHtml .= Html::rawElement( 'div', [ 'class' => 'graphlegenditem', 'style' => "color: $color" ],
					"$color: $m_label" );

				$colorCount ++;
			}

			$result .= Html::rawElement( 'div', [ 'class' => 'graphlegend' ], "$itemsHtml" );

		}

		return $result;
	}

	/**
	 * Process a result row and create SRF\GraphNodes
	 *
	 * @since 2.5.0
	 *
	 * @param array $row
	 * @param $outputmode
	 * @param array $nodes
	 *
	 */
	protected function processResultRow( array /* of SMWResultArray */
										 $row, $outputmode, $nodes ) {

		// loop through all row fields
		foreach ( $row as $i => $resultArray ) {

			// loop through all values of a multivalue field
			while ( ( /* SMWDataValue */
					$object = $resultArray->getNextDataValue() ) !== false ) {

				// create SRF\GraphNode for column 0
				if ( $i == 0 ) {
					$node = new GraphNode( str_replace( '_', ' ', $object->getShortText( $outputmode ) ) );
					$this->nodes[] = $node;
				} else {
					// special handling for labels1-3, all other printout statements will add links to parent nodes
					$label = $resultArray->getPrintRequest()->getLabel();
					switch ( $label ) {
						// fixed to three labels
						case 'label1':
						case 'label2':
						case 'label3':
							$labelIndex = intval( explode( 'label', $label, 2 )[1] );
							if ( $object instanceof SMWWikiPageValue ) {
								$node->addLabel( $labelIndex, $object->getDisplayTitle() );
							} else {
								$node->addLabel( $labelIndex, $object->getShortText( $outputmode ) );
							}
							break;
						default:
							// add Object (Parent Node) and Predicate (Graph Label) to current node
							// <this node> <is part of> <other node>
							$node->addParentNode( $resultArray->getPrintRequest()->getLabel(),
								str_replace( '_', ' ', $object->getDBkey() ) );
							break;
					}
				}
			}
		}

		return true;
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
			'type'              => 'string',
			'default'           => '',
			'message'           => 'srf-paramdesc-graphsize',
			'manipulatedefault' => false,
		];

		$params['graphlegend'] = [
			'type'    => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-graphlegend',
		];

		$params['graphlabel'] = [
			'type'    => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-graphlabel',
		];

		$params['graphlink'] = [
			'type'    => 'boolean',
			'default' => false,
			'message' => 'srf-paramdesc-graphlink',
		];

		$params['graphcolor'] = [
			'type'    => 'boolean',
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
			'default'           => false,
			'message'           => 'srf-paramdesc-graph-nodeshape',
			'manipulatedefault' => false,
			'values'            => self::$NODE_SHAPES,
		];

		$params['relation'] = [
			'default'           => 'child',
			'message'           => 'srf-paramdesc-graph-relation',
			'manipulatedefault' => false,
			'values'            => [ 'parent', 'child' ],
		];

		$params['nameproperty'] = [
			'default'           => false,
			'message'           => 'srf-paramdesc-graph-nameprop',
			'manipulatedefault' => false,
		];

		$params['wordwraplimit'] = [
			'type'              => 'integer',
			'default'           => 25,
			'message'           => 'srf-paramdesc-graph-wwl',
			'manipulatedefault' => false,
		];

		$params['arrowhead'] = [
			'type'              => 'string',
			'default'           => 'normal',
			'message'           => 'srf-paramdesc-graph-arrowhead',
			'manipulatedefault' => false,
			'values'            => self::$ARROW_HEAD,
		];

		return $params;
	}
}
