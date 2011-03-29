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
	protected $m_graphName = 'QueryResult';
	protected $m_graphLabel;
	protected $m_graphColor;
	protected $m_graphLegend;
	protected $m_graphLink;
	protected $m_rankdir = "LR";
	protected $m_graphSize = "";
	protected $m_labelArray = array();
	protected $m_graphColors = array( 'black', 'red', 'green', 'blue', 'darkviolet', 'gold', 'deeppink', 'brown', 'bisque', 'darkgreen', 'yellow', 'darkblue', 'magenta', 'steelblue2' );

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
	}
	
	protected function getResultText( /* SMWQueryResult */ $res, $outputmode ) {
		$wgGraphVizSettings = new GraphVizSettings;
		$this->isHTML = true;
	
	    $key = 0;
		
		$legendInput = '';
		
		$graphInput = "digraph $this->m_graphName {";
		if ( $this->m_graphSize != '' ) $graphInput .= "size=\"$this->m_graphSize\";";
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
				if ( $i == 0 ) {
					$firstColValue = $object->getShortText( $outputmode );
					$labelName = $resultArray->getPrintRequest()->getLabel();
				}
							
				$segments[] = $this->getGVForDataValue( $object, $outputmode, $i == 0, $firstColValue, $labelName );
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
	 * @param boolean $isFirstColumn
	 * @param string $firstColValue
	 * @param string $labelName
	 * 
	 * @return string
	 */	
	protected function getGVForDataValue( SMWDataValue $object, $outputmode, $isFirstColumn, $firstColValue, $labelName ) {
		$graphInput = '';
		$text = $object->getShortText( $outputmode );

		if ( $this->m_graphLink ) {
			$nodeLinkTitle = Title::newFromText( $text );
			$nodeLinkURL = $nodeLinkTitle->getLocalURL();

			$graphInput .= " \"$text\" [URL = \"$nodeLinkURL\"]; ";
		}

		if ( !$isFirstColumn ) {
			$graphInput .= " \"$firstColValue\" -> \"$text\" "; // TODO
			
			if ( $this->m_graphLabel && $this->m_graphColor ) {
				$graphInput .= ' [';

				if ( array_search( $labelName, $this->m_labelArray, true ) === false ) {
					$this->m_labelArray[] = $labelName;
				}
				
				$key = array_search( $labelName, $this->m_labelArray, true );
				$color = $this->m_graphColors[$key];

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
	
	public function getParameters() {
		return array(
			array( 'name' => 'graphname', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_graphname' ) ),
			array( 'name' => 'graphsize', 'type' => 'int', 'description' => wfMsg( 'srf_paramdesc_graphsize' ) ),
			array( 'name' => 'graphlegend', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphlegend' ), 'values'=> array( 'yes', 'no' ) ),
			array( 'name' => 'graphlabel', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphlabel' ), 'values'=> array( 'yes', 'no' ) ),
			array( 'name' => 'rankdir', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_rankdir' ) ),
			array( 'name' => 'graphlink', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphlink' ), 'values'=> array( 'yes', 'no' ) ),
			array( 'name' => 'graphcolor', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_graphcolor' ), 'values'=> array( 'yes', 'no' ) )
		);      
	}
	
}
