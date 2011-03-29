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
	
	protected function getResultText( $res, $outputmode ) {
		$wgGraphVizSettings = new GraphVizSettings;
		$this->isHTML = true;
	
	    $key = 0;
		// Create text graph
		$graphInput = '';
		$legendInput = '';

		$graphInput .= "digraph $this->m_graphName {";
		if ( $this->m_graphSize != '' ) $graphInput .= "size=\"$this->m_graphSize\";";
		$graphInput .= "rankdir=$this->m_rankdir;";
	while ( $row = $res->getNext() ) {

			$firstcol = true;

			foreach ( $row as $field ) {

				while ( ( $object = $field->getNextObject() ) !== false ) {

					$text = $object->getShortText( $outputmode );

					if ( $firstcol ) {
						$firstcolvalue = $object->getShortText( $outputmode );

					}

					if ( $this->m_graphLink == true ) {
						$nodeLinkTitle = Title::newFromText( $text );
						$nodeLinkURL = $nodeLinkTitle->getLocalURL();

						$graphInput .= " \"$text\" [URL = \"$nodeLinkURL\"]; ";
					}

					if ( !$firstcol ) {
					$graphInput .= " \"$firstcolvalue\" -> \"$text\" ";
						if ( ( $this->m_graphLabel == true ) || ( $this->m_graphColor == true ) ) {
							$graphInput .= " [";
							$req = $field->getPrintRequest();
							$labelName = $req->getLabel();

							if ( array_search( $labelName, $this->m_labelArray, true ) === false ) {
								$this->m_labelArray[] = $labelName;
							}
								$key = array_search( $labelName, $this->m_labelArray, true );
								$color = $this->m_graphColors[$key];

							if ( $this->m_graphLabel == true ) {
								$graphInput .= "label=\"$labelName\"";
								if ( $this->m_graphColor == true ) $graphInput .= ",fontcolor=$color,";
							}
							if ( $this->m_graphColor == true ) {

								$graphInput .= "color=$color";
							}
							$graphInput .= "]";

						}
						$graphInput .= ";";

					}
				}

				$firstcol = false;
			}

		}

		$graphInput .= "}";
		// Calls renderGraphViz function from MediaWiki GraphViz extension
		$result = renderGraphviz( $graphInput );
		if ( ( $this->m_graphLegend == true ) && ( $this->m_graphColor == true ) ) {
			$arrayCount = 0;
			$result .= "<P>";
			foreach ( $this->m_labelArray as $m_label ) {
				$color = $this->m_graphColors[$arrayCount];
				$result .= "<font color=$color>$color: $m_label </font><br />";
				$arrayCount = $arrayCount + 1;
			}
			$result .= "</P>";
		}
		
		return $result;
	}
	
	function getParameters() {
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
