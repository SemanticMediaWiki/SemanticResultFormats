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
	);
	
	protected $m_graphName = 'QueryResult';
	protected $m_graphLabel;
	protected $m_graphColor;
	protected $m_graphLegend;
	protected $m_graphLink;
	protected $m_rankdir = "LR";
	protected $m_graphSize = "";
	protected $m_labelArray = array();
	protected $m_graphColors = array( 'black', 'red', 'green', 'blue', 'darkviolet', 'gold', 'deeppink', 'brown', 'bisque', 'darkgreen', 'yellow', 'darkblue', 'magenta', 'steelblue2' );
	protected $m_nameProperty = false;
	protected $m_nodeShape = false;
	protected $m_parentRelation;
	protected $m_wordWrapLimit = 25;
	
	protected function readParameters( $params, $outputmode ) {
		SMWResultPrinter::readParameters( $params, $outputmode );

		if ( array_key_exists( 'graphname', $params ) ) {
			$this->m_graphName = trim( $params['graphname'] );
		}
		
		if ( array_key_exists( 'graphsize', $params ) ) {
			$this->m_graphSize = trim( $params['graphsize'] );
		}
		
		$this->m_graphLegend = array_key_exists( 'graphlegend', $params ) && strtolower( trim( $params['graphlegend'] ) ) == 'yes';
		$this->m_graphLabel = array_key_exists( 'graphlabel', $params ) && strtolower( trim( $params['graphlabel'] ) ) == 'yes';

		if ( array_key_exists( 'rankdir', $params ) ) {
			$this->m_rankdir = strtoupper( trim( $params['rankdir'] ) );
		}
		
		$this->m_graphLink = array_key_exists( 'graphlink', $params ) && strtolower( trim( $params['graphlink'] ) ) == 'yes';
		$this->m_graphColor = array_key_exists( 'graphcolor', $params ) && strtolower( trim( $params['graphcolor'] ) ) == 'yes';
		
		if ( array_key_exists( 'nameproperty', $params ) ) {
			$this->m_nameProperty = trim( $params['nameproperty'] );
		}
		
		$this->m_parentRelation = array_key_exists( 'relation', $params ) && strtolower( trim( $params['relation'] ) ) == 'parent';
		
		if ( array_key_exists( 'nodeshape', $params ) && in_array( trim( $params['nodeshape'] ), self::$NODE_SHAPES ) ) {
			$this->m_nodeShape = trim( $params['nodeshape'] );
		}
		
		if ( array_key_exists( 'wordwraplimit', $params ) && is_int( $params['wordwraplimit'] ) ) {
			$this->m_wordWrapLimit = (int)$params['wordwraplimit'];
		}
	}
	
	protected function getResultText( /* SMWQueryResult */ $res, $outputmode ) {
		$wgGraphVizSettings = new GraphVizSettings;
		$this->isHTML = true;
	
	    $key = 0;
		
		$legendInput = '';
		
		$graphInput = "digraph $this->m_graphName {";
		if ( $this->m_graphSize != '' ) $graphInput .= "size=\"$this->m_graphSize\";";
		if ( $this->m_nodeShape ) $graphInput .=  "node [shape=$this->m_nodeShape];";
		$graphInput .= "rankdir=$this->m_rankdir;";		
		
		while ( $row = $res->getNext() ) {
			$graphInput .= $this->getGVForItem( $row, $outputmode );
		}
		
		$graphInput .= "}";//var_dump($graphInput);exit;
		
		// Calls renderGraphViz function from MediaWiki GraphViz extension
		$result = renderGraphviz( $graphInput );
		
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
	 * Returns the GV for a single subject.
	 * 
	 * @since 1.5.4
	 * 
	 * @param array $row
	 * @param $outputmode
	 * 
	 * @return string
	 */
	protected function getGVForItem( array /* of SMWResultArray */ $row, $outputmode ) {	
		$segments = array();
		
		// Loop throught all fields of the record.
		foreach ( $row as $i => $resultArray ) {

			// Loop throught all the parts of the field value.
			while ( ( $object = $resultArray->getNextObject() ) !== false ) {
				$propName = $resultArray->getPrintRequest()->getLabel();
				$isName = $this->m_nameProperty ? ( $i != 0 && $this->m_nameProperty === $propName ) : $i == 0;
				
				if ( $isName ) {
					$name = $this->getWordWrappedText( $object->getShortText( $outputmode ), $this->m_wordWrapLimit );
				}
				
				if ( !( $this->m_nameProperty && $i == 0 ) ) {
					$segments[] = $this->getGVForDataValue( $object, $outputmode, $isName, $name, $propName );
				}
			}
		}

		return implode( "\n", $segments );
	}
	
	/**
	 * Returns the GV for a single SMWDataValue.
	 * 
	 * @since 1.5.4
	 * 
	 * @param SMWDataValue $object
	 * @param $outputmode
	 * @param boolean $isName Is this the name that should be used for the node?
	 * @param string $name
	 * @param string $labelName
	 * 
	 * @return string
	 */	
	protected function getGVForDataValue( SMWDataValue $object, $outputmode, $isName, $name, $labelName ) {
		$graphInput = '';
		$text = $object->getShortText( $outputmode );

		if ( $this->m_graphLink ) {
			$nodeLinkTitle = Title::newFromText( $text );
			$nodeLinkURL = $nodeLinkTitle->getLocalURL();
		}
		
		$text = $this->getWordWrappedText( $text, $this->m_wordWrapLimit );
		
		if ( $this->m_graphLink ) {
			$graphInput .= " \"$text\" [URL = \"$nodeLinkURL\"]; ";
		}

		if ( !$isName ) {
			$graphInput .= $this->m_parentRelation ? " \"$text\" -> \"$name\" " : " \"$name\" -> \"$text\" ";
			
			if ( $this->m_graphLabel && $this->m_graphColor ) {
				$graphInput .= ' [';

				if ( array_search( $labelName, $this->m_labelArray, true ) === false ) {
					$this->m_labelArray[] = $labelName;
				}
				
				$color = $this->m_graphColors[array_search( $labelName, $this->m_labelArray, true )];

				if ( $this->m_graphLabel ) {
					$graphInput .= "label=\"$labelName\"";
					if ( $this->m_graphColor ) $graphInput .= ",fontcolor=$color,";
				}
				
				if ( $this->m_graphColor ) {
					$graphInput .= "color=$color";
				}
				
				$graphInput .= ']';
	
			}
			
			$graphInput .= ';';
		}

		return $graphInput;
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
		return wfMsg( 'srf-printername-graph' );
	}	
	
	public function getParameters() {
		return array(
			array( 'name' => 'graphname', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_graphname' ) ),
			array( 'name' => 'graphsize', 'type' => 'int', 'description' => wfMsg( 'srf_paramdesc_graphsize' ) ),
			array( 'name' => 'graphlegend', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphlegend' ), 'values'=> array( 'yes', 'no' ) ),
			array( 'name' => 'graphlabel', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphlabel' ), 'values'=> array( 'yes', 'no' ) ),
			array( 'name' => 'rankdir', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_rankdir' ) ),
			array( 'name' => 'graphlink', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphlink' ), 'values'=> array( 'yes', 'no' ) ),
			array( 'name' => 'graphcolor', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphcolor' ), 'values'=> array( 'yes', 'no' ) ),
			array( 'name' => 'nodeshape', 'type' => 'enumeration', 'description' => wfMsg( 'srf-paramdesc-graph-nodeshape' ), 'values'=> self::$NODE_SHAPES ),
			array( 'name' => 'nameproperty', 'type' => 'text', 'description' => wfMsg( 'srf-paramdesc-graph-nameprop' ) ),
			array( 'name' => 'relation', 'type' => 'enumeration', 'description' => wfMsg( 'srf-paramdesc-graph-relation' ), 'values'=> array( 'parent', 'child' ) ),
			array( 'name' => 'wordwraplimit', 'type' => 'int', 'description' => wfMsg( 'srf-paramdesc-graph-wwl' ) ),
		);      
	}
	
}
