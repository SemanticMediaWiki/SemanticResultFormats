<?php

namespace SRF;

use SMW\ResultPrinter;
use SMWQueryResult;
use SMWWikiPageValue;
use GraphViz;

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
class Graph extends ResultPrinter {

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
	protected $labelArray = [];
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
		$this->parentRelation = strtolower( trim( $params['relation'] ) ) == 'parent';
		$this->nodeShape = $params['nodeshape'];
		$this->wordWrapLimit = $params['wordwraplimit'];
	}


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

		// iterate query result
		while ( $row = $res->getNext() ) {
			$this->processResultRow( $row, $outputmode, $this->nodes );
		}

		///////////////////////////////////
		// NODES
		///////////////////////////////////

		foreach ( $this->nodes as $node ) {

			// take node ID (title) if we don't have a label1
			$nodeName = ( empty( $node->getLabel1() ) ) ? $node->getID() : $node->getLabel1();

			// add the node
			$graphInput .= "\"" . $nodeName . "\"";

			if ( $this->graphLink ) {
				$nodeLinkURL = "[[" . $node->getID() . "]]";
				$graphInput .= "[URL = \"$nodeLinkURL\"] ";
			}

			// build the additional labels only for record or Mrecord
			if ( ( $node->getLabel2() != "" || $node->getLabel3() != "" ) &&
				 ( $this->nodeShape == "record" || $this->nodeShape == "Mrecord" )
			) {

				$label = ( empty( $node->getLabel1() ) ) ? $node->getID() : $node->getLabel1();
				$graphInput .= "[label=\"{" . $label;

				if ( $node->getLabel2() != "" ) {
					$graphInput .= "|" . $node->getLabel2();
				}

				if ( $node->getLabel3() != "" ) {
					$graphInput .= "|" . $node->getLabel3();
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

				$nodeName = ( empty( $node->getLabel1() ) ) ? $node->getID() : $node->getLabel1();

				//was handled with param "relation" type string, child/parent
				$i = 0;
				foreach ( $node->getParentNode() as $parentNode ) {

					$graphInput .= $this->parentRelation ? " \"" . $parentNode['object'] . "\" -> \"" . $nodeName . "\""
						: " \"" . $nodeName . "\" -> \"" . $parentNode['object'] . "\" ";

					// Add ArrowHead for every Arrow of Node
					$graphInput .= "[arrowhead = " . $this->arrowHead . "]";

					if ( $this->graphLabel || $this->graphColor ) {
						$graphInput .= ' [';

						if ( array_search( $parentNode['predicate'], $this->labelArray, true ) === false ) {
							$this->labelArray[] = $parentNode['predicate'];
						}

						$color = $this->graphColors[array_search( $parentNode['predicate'], $this->labelArray, true )];

						if ( $this->graphLabel ) {
							$graphInput .= "label=\"" . $parentNode['predicate'] . "\"";
							if ( $this->graphColor ) {
								$graphInput .= ",fontcolor=$color,";
							}
						}

						if ( $this->graphColor ) {
							$graphInput .= "color=$color";
						}
						$graphInput .= ']';
					}
					$i ++;
				}
				$graphInput .= ';';
			}
		}
		$graphInput .= "}";


		// calls graphvizParserHook from GraphViz extension
		$result = GraphViz::graphvizParserHook( $graphInput, "", $GLOBALS['wgParser'], true );


		// append legend
		if ( $this->graphLegend && $this->graphColor ) {
			$arrayCount = 0;
			$arraySize = count( $this->graphColors );
			$result .= "<P>";

			foreach ( $this->labelArray as $m_label ) {
				if ( $arrayCount >= $arraySize ) {
					$arrayCount = 0;
				}

				$color = $this->graphColors[$arrayCount];
				$result .= "<font color=$color>$color: $m_label </font><br />";

				$arrayCount += 1;
			}

			$result .= "</P>";
		}

		return $result;
	}

	/**
	 * Process a result row and create SRFGraphNodes
	 *
	 * @since 2.5.0
	 *
	 * @param array of SMWResultArray $row
	 * @param $outputmode
	 * @param array $nodes
	 *
	 */
	protected function processResultRow( array $row, $outputmode, $nodes ) {

		// loop through all row fields
		foreach ( $row as $i => $resultArray ) {

			// loop through all values of a multivalue field
			while ( ( /* SMWDataValue */ $object = $resultArray->getNextDataValue() ) !== false ) {

				$node = new GraphNode( str_replace( '_', ' ', $object->getShortText( $outputmode ) ) );
				// create SRFGraphNode for column 0
				if ( $i == 0 ) {
					if ( !in_array( $node, $nodes, true ) ) {
						$this->nodes[] = $node;
					}
				} else {
					// special handling for labels, all other printout statements will add links to parent nodes
					switch ( $resultArray->getPrintRequest()->getLabel() ) {
						case 'label1':
							if ( $object instanceof SMWWikiPageValue ) {
								$node->addLabel1( $object->getDisplayTitle() );
							} else {
								$node->addLabel1( $object->getShortText( $outputmode ) );
							}
							break;
						case 'label2':
							if ( $object instanceof SMWWikiPageValue ) {
								$node->addLabel2( $object->getDisplayTitle() );
							} else {
								$node->addLabel2( $object->getShortText( $outputmode ) );
							}
							break;
						case 'label3':
							if ( $object instanceof SMWWikiPageValue ) {
								$node->addLabel3( $object->getDisplayTitle() );
							} else {
								$node->addLabel3( $object->getShortText( $outputmode ) );
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
			'manipulatedefault' => false
		];

		return $params;
	}
}
