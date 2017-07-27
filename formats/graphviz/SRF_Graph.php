<?php

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
 */
class SRFGraph extends SMWResultPrinter {

	public static $ARROW_HEAD = array(
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
	);

	public static $NODE_SHAPES = array(
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
	);

	protected $m_graphName;
	protected $m_graphLabel;
	protected $m_graphColor;
	protected $m_graphLegend;
	protected $m_graphLink;
	protected $m_rankdir;
	protected $m_graphSize;
	protected $m_labelArray = array();
	protected $m_graphColors = array(
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
	);
	protected $m_nameProperty;
	protected $m_nodeShape;
	protected $m_parentRelation;
	protected $m_wordWrapLimit;
	protected $m_arrowHead;

	protected $m_nodes = array();

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		$this->m_graphName = trim( $params['graphname'] );
		$this->m_graphSize = trim( $params['graphsize'] );
		$this->m_graphLegend = $params['graphlegend'];
		$this->m_graphLabel = $params['graphlabel'];
		$this->m_rankdir = strtoupper( trim( $params['arrowdirection'] ) );
		$this->m_graphLink = $params['graphlink'];
		$this->m_graphColor = $params['graphcolor'];
		$this->m_arrowHead = $params['arrowhead'];
		$this->m_nameProperty = $params['nameproperty'] === false ? false : trim( $params['nameproperty'] );
		$this->m_parentRelation = strtolower( trim( $params['relation'] ) ) == 'parent';
		$this->m_nodeShape = $params['nodeshape'];
		$this->m_wordWrapLimit = $params['wordwraplimit'];
	}

	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		if ( !is_callable( 'GraphViz::graphvizParserHook' ) ) {
			wfWarn( 'The SRF Graph printer needs the GraphViz extension to be installed.' );

			return '';
		}

		$this->isHTML = true;

		$graphInput = "digraph $this->m_graphName {";
		if ( $this->m_graphSize != '' ) {
			$graphInput .= "size=\"$this->m_graphSize\";";
		}
		if ( $this->m_nodeShape ) {
			$graphInput .= "node [shape=$this->m_nodeShape];";
		}
		$graphInput .= "rankdir=$this->m_rankdir;";

		///////////////////////////////////
		// Get all Nodes
		///////////////////////////////////

		while ( $row = $res->getNext() ) {
			$this->getGVForItem( $row, $outputmode, $this->m_nodes );
		}

		///////////////////////////////////
		// Rendering Nodes
		///////////////////////////////////
		foreach ( $this->m_nodes as $node ) {

			if ( $this->m_graphLink ) {
				$nodeLinkURL = "[[" . $node->getID() . "]]";
				$nodeName = ( empty( $node->getLabel1() ) ) ? $node->getID() : $node->getLabel1();
				$graphInput .= " \"" . $nodeName . "\" [URL = \"$nodeLinkURL\"]";
			}

			if ( ( $node->getLabel2() != "" || $node->getLabel3() != "" ) &&
			     ( $this->m_nodeShape == "record" || $this->m_nodeShape == "Mrecord" )
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
		// Rendering Node Links
		///////////////////////////////////
		foreach ( $this->m_nodes as $node ) {

			if ( count( $node->getParentNode() ) > 0 ) {

				$nodeName = ( empty( $node->getLabel1() ) ) ? $node->getID() : $node->getLabel1();

				//was handled with param "relation" type string, child/parent
				$i = 0;
				foreach ( $node->getParentNode() as $parentNode ) {

					$graphInput .= $this->m_parentRelation ? " \"" . $parentNode['object'] . "\" -> \"" . $nodeName .
					                                         "\""
						: " \"" . $nodeName . "\" -> \"" . $parentNode['object'] . "\" ";

					// Add ArrowHead for every Arrow of Node
					$graphInput .= "[arrowhead = " . $this->m_arrowHead . "]";

					if ( $this->m_graphLabel || $this->m_graphColor ) {
						$graphInput .= ' [';

						if ( array_search( $parentNode['predicate'], $this->m_labelArray, true ) === false ) {
							$this->m_labelArray[] = $parentNode['predicate'];
						}

						$color =
							$this->m_graphColors[array_search( $parentNode['predicate'], $this->m_labelArray, true )];

						if ( $this->m_graphLabel ) {
							$graphInput .= "label=\"" . $parentNode['predicate'] . "\"";
							if ( $this->m_graphColor ) {
								$graphInput .= ",fontcolor=$color,";
							}
						}

						if ( $this->m_graphColor ) {
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

		// Calls graphvizParserHook function from MediaWiki GraphViz extension
		$result = GraphViz::graphvizParserHook( $graphInput, "", $GLOBALS['wgParser'], true );

		if ( $this->m_graphLegend && $this->m_graphColor ) {
			$arrayCount = 0;
			$arraySize = count( $this->m_graphColors );
			$result .= "<P>";

			foreach ( $this->m_labelArray as $m_label ) {
				if ( $arrayCount >= $arraySize ) {
					$arrayCount = 0;
				}

				$color = $this->m_graphColors[$arrayCount];
				$result .= "<font color=$color>$color: $m_label </font><br />";

				$arrayCount += 1;
			}

			$result .= "</P>";
		}

		return $result;
	}

	/**
	 * Create an object for a Node.
	 *
	 * @since 2.5.0
	 *
	 * @param array $row
	 * @param $outputmode
	 * @param array $nodes
	 *
	 */
	protected function getGVForItem( array /* of SMWResultArray */
	$row, $outputmode, $nodes ) {

		// Loop through all fields of the record.
		foreach ( $row as $i => $resultArray ) {

			// Loop through all values of a multivalue field
			while ( ( /* SMWDataValue */
			        $object = $resultArray->getNextDataValue() ) !== false ) {

				if ( $i == 0 ) {
					$node = new SRFGraphNode( str_replace( '_', ' ', $object->getShortText( $outputmode ) ) );

					if ( !in_array( $node, $nodes, true ) ) {
						$this->m_nodes[] = $node;
					}
				} else {

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
							// Add Object (Parent Node) and Predicate (Graph Label) of Current Node
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
		$charLimit = max( array( $charLimit, 1 ) );
		$segments = array();

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
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getName()
	 */
	public function getName() {
		return wfMessage( 'srf-printername-graph' )->text();
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

		$params['graphname'] = array(
			'default' => 'QueryResult',
			'message' => 'srf_paramdesc_graphname',
		);

		$params['graphsize'] = array(
			'type'              => 'string',
			'default'           => '',
			'message'           => 'srf_paramdesc_graphsize',
			'manipulatedefault' => false,
		);

		$params['graphlegend'] = array(
			'type'    => 'boolean',
			'default' => false,
			'message' => 'srf_paramdesc_graphlegend',
		);

		$params['graphlabel'] = array(
			'type'    => 'boolean',
			'default' => false,
			'message' => 'srf_paramdesc_graphlabel',
		);

		$params['graphlink'] = array(
			'type'    => 'boolean',
			'default' => false,
			'message' => 'srf_paramdesc_graphlink',
		);

		$params['graphcolor'] = array(
			'type'    => 'boolean',
			'default' => false,
			'message' => 'srf_paramdesc_graphcolor',
		);

		$params['arrowdirection'] = array(
			'aliases' => 'rankdir',
			'default' => 'LR',
			'message' => 'srf_paramdesc_rankdir',
			'values'  => array( 'LR', 'RL', 'TB', 'BT' ),
		);

		$params['nodeshape'] = array(
			'default'           => false,
			'message'           => 'srf-paramdesc-graph-nodeshape',
			'manipulatedefault' => false,
			'values'            => self::$NODE_SHAPES,
		);

		$params['relation'] = array(
			'default'           => 'child',
			'message'           => 'srf-paramdesc-graph-relation',
			'manipulatedefault' => false,
			'values'            => array( 'parent', 'child' ),
		);

		$params['nameproperty'] = array(
			'default'           => false,
			'message'           => 'srf-paramdesc-graph-nameprop',
			'manipulatedefault' => false,
		);

		$params['wordwraplimit'] = array(
			'type'              => 'integer',
			'default'           => 25,
			'message'           => 'srf-paramdesc-graph-wwl',
			'manipulatedefault' => false,
		);

		$params['arrowhead'] = array(
			'type'              => 'string',
			'default'           => 'normal',
			'message'           => 'srf-paramdesc-graph-arrowhead',
			'manipulatedefault' => false
		);

		return $params;
	}
}

/*
 * Stores informations of a single Node
 *
 * @author Sebastian Schmid
 */

class SRFGraphNode {

	/**
	 * @var string $m_id : Node ID
	 * @example 'Article:Node1'
	 */
	private $m_id;

	/**
	 * @var string $m_label1 : Displaytitle of Node without NS
	 * @example 'Node1'
	 */
	private $m_label1;

	/**
	 * @var string $m_label2 : concatenate a String of Elements by Property
	 *                        and adds an '/l' for left align
	 *                        the label2 is displayed in the first row of a record
	 * @example 'Member/l'
	 */
	private $m_label2;

	/**
	 * @var string $m_label3 : concatenate a String of Elements by Property
	 *                        and adds an '/l' for left align
	 *                        the label3 is displayed in the second row of a record
	 * @example 'Sub Member/l'
	 */
	private $m_label3;

	/**
	 * @var string $m_parent : Parent node of current
	 *
	 * @example 'Main Node'
	 */
	private $m_parent = array();

	/**
	 * @var string $m_parent : Parent node of current
	 *
	 * @example 'Sub Member/l'
	 */
	//private $m_graphLabel = array(); // Part of team

	function __construct( $id ) {
		$this->m_id = $id;
	}

	public function addLabel1( $row ) {
		$this->m_label1 = $row;
	}

	public function addLabel2( $row ) {
		$this->m_label2 .= $row . "\l";
	}

	public function addLabel3( $row ) {
		$this->m_label3 .= $row . "\l";
	}

	public function addParentNode( $graphLabel, $parentNode ) {
		$this->m_parent[] = array(
			"predicate" => $graphLabel,
			"object"    => $parentNode
		);
	}

	//public function addGraphLabel( $graphLabel ) {
	//	$this->m_graphLabel[] = $graphLabel;
	//}

	public function getGraphLabel() {
		return $this->m_graphLabel;
	}

	public function getParentNode() {
		return $this->m_parent;
	}

	public function getLabel1() {
		return $this->m_label1;
	}

	public function getLabel2() {
		return $this->m_label2;
	}

	public function getLabel3() {
		return $this->m_label3;
	}

	public function getID() {
		return $this->m_id;
	}

}
