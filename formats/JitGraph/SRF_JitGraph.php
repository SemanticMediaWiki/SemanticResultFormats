<?php
/**
 * Print query results in interactive graph using the
 * JavaScript InfoVis Toolkit (http://thejit.org)
 * 
 * @since 1.9
 * 
 * @file SRF_JitGraph.php
 * @ingroup SemanticResultFormats
 */
class SRFJitGraph extends SMWResultPrinter {

	public static $NODE_SHAPES = array('circle', 'rectangle', 'square', 'ellipse', 'triangle', 'star');
			
	protected $m_settings = array(
	    "divID" => "infovis",
	    "edgeColor" => "#23A4FF",
	    "edgeWidth" => 2,
	    "edgeLength" => 150,
	    "navigation" => true,
	    "zooming" => 20,
	    "panning" => "avoid nodes",
	    "labelColor" => "#000000"
	);

	
	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srf_printername_' . $this->mFormat )->text();
	}

	
	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $res, $outputMode ) {
		$this->includeJS();
		$this->isHTML = true;
		
		/*  create div id to draw into */ 
		$this->m_settings['d_id'] = rand(1000,9999);
		$this->m_settings['divID'] = 'infovis-' . $this->m_settings['d_id'];
		
		/* bundle the settings and node data */
		$graphData = array(
			'settings' => $this->m_settings,
			'data' => $this->getResultData($res, $outputMode)
		);

		$result = '';
		if($this->params['graphname'] != '') {
			$result .= '<h3>'. $this->params['graphname'] .'</h3>';
		}
		
		$result .= '<div class="center-container"><span class="progressBar" id="progress-'.$this->m_settings['d_id'].'">0%</span><div id="'. $this->m_settings['divID'] .'" class="infovis"></div></div>';
		$result .= '<script type="text/json" class="infovis-data">';
		$result .= json_encode( $graphData );
		$result .= '</script>';		
		return $result;
	}	
	
	
	/* This prepares the data for the force directed visualisation*/
	private function getResultData(SMWQueryResult $result, $outputMode) {
		global $wgTitle;
		$adjacencyRegistry = array();
		$nodes = array();
		while ( $rows = $result->getNext() ) { // Objects (pages)
			$firstcol = true;
			foreach ( $rows as $field ) {
				while ( ( $object = $field->getNextObject() ) !== false ) {

					$currentNode = $object->getShortText( $outputMode );
					$edgeLabel = trim( $field->getPrintRequest()->getLabel());

					if($firstcol) {
						$subjectNode = $object->getShortText( $outputMode );
					}
					
					if(!array_key_exists($currentNode, $nodes)) {
						$nodes[$currentNode] = $this->createNode($currentNode, $edgeLabel, (strcmp($currentNode,$wgTitle->getPrefixedText())==0 && "Yes" == $this->params['graphrootnode']));
					}
					
					if(!$firstcol) { 
						if(!array_key_exists($subjectNode, $adjacencyRegistry) || (!in_array($currentNode, $adjacencyRegistry[$subjectNode]) && $subjectNode != $currentNode)) {
							$nodes[$subjectNode]['adjacencies'][] = $this->createAdjacency($subjectNode, $currentNode);
							$adjacencyRegistry[$subjectNode][] = $currentNode;
						}
					}
				}
				$firstcol = false;
			}
		}
		
		/* create the final array (non-associative)*/
		$nodesToReturn = array();
		foreach ($nodes as $key => $value) {
			$nodesToReturn[] = $value;
		}
		return $nodesToReturn;
	}
	
	
	/* Creates a node for the force directed layout */
	private function createNode($currentNode, $edgeLabel, $isRootNode) {
		return array(
			'id' => $currentNode,
			'name' => $currentNode,
			'data' => array(
				'$color' => $isRootNode ? $this->params['rootnodecolor'] : $this->params['graphnodecolor'],
				'$type' => $this->params['graphnodetype'],
				'$dim' => $isRootNode ? $this->params['graphnodesize'] + 5 : $this->params['graphnodesize'],
				'$url' => Title::newFromText($currentNode) ->getFullURL(),
				'$edgeType' => $edgeLabel
			),
			'adjacencies' => array()
		);
	}

	
	/* Creates an adjacency within a force directed layout between the subject (from) and current (to) node*/
	private function createAdjacency($subjectNode, $currentNode) {
		return array(
			'nodeFrom' => $subjectNode,
			'nodeTo' => $currentNode,
			'data' => array (
				'$color' => $this->params['rootnodecolor'],
				'$url' => Title::newFromText($currentNode) ->getFullURL()
			)
		);
	}
	
	
	/**
	 * Includes Javascript and CSS resources
	 *
	 */
	protected function includeJS() {
		
		SMWOutputs::requireHeadItem( SMW_HEADER_STYLE );
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			SMWOutputs::requireResource( 'ext.srf.jitgraph' );
		}
		else {
			global $srfgScriptPath;
			SMWOutputs::requireHeadItem(
				'smw_jgcanvas',
				'<script type="text/javascript" src="' . $srfgScriptPath . 
					'/JitGraph/resources/Jit/Extras/excanvas.js"></script>'
			);	
			SMWOutputs::requireHeadItem(
				'smw_jgcss',
				'<link rel="stylesheet" type="text/css" href="' . $srfgScriptPath . 
					'/JitGraph/resources/base.css"></link>'
			);
			SMWOutputs::requireHeadItem(
				'smw_jgloader',
				'<script type="text/javascript" src="' . $srfgScriptPath . 
					'/JitGraph/resources/jquery.progressbar.js"></script>'
			);
			SMWOutputs::requireHeadItem(
				'smw_jg',
				'<script type="text/javascript" src="' . $srfgScriptPath . 
					'/JitGraph/resources/Jit/jit.js"></script>'
			);
			SMWOutputs::requireHeadItem(
				'smw_jghelper',
				'<script type="text/javascript" src="' . $srfgScriptPath . 
					'/JitGraph/resources/ext.srf.jitgraph.js"></script>'
			);				
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
		
		$params[] = array(
			'name' => 'graphname',
			'default' => '',
			'message' => 'srf_paramdesc_graphname',
		);
		$params[] = array(
			'name' => 'graphnodetype',
			'default' => 'circle',
			'message' => 'srf-paramdesc-graph-graphnodetype',
			'values' => self::$NODE_SHAPES,
		);
		$params[] = array(
			'name' => 'graphnodesize',
			'type' => 'integer',
			'default' => 12,
			'message' => 'srf-paramdesc-graph-graphnodesize',
		);
		$params[] = array(
			'name' => 'graphrootnode',
			'default' => 'No',
			'message' => 'srf_paramdesc_graphrootnode',
		);
		$params[] = array(
			'name' => 'rootnodecolor',
			'default' => '#CF2A2A',
			'message' => 'srf_paramdesc_rootnodecolor',
		);
		$params[] = array(
			'name' => 'graphnodecolor',
			'default' => '#005588',
			'message' => 'srf_paramdesc_graphnodecolor',
		);
		return $params;
	}

}
