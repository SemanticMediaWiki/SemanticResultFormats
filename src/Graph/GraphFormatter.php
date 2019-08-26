<?php

namespace SRF\Graph;

use Html;

class GraphFormatter {

	private $graph = "";

	private $graphName;
	private $graphSize;
	private $nodeShape;
	private $nodeLabel;
	private $rankDir;
	private $wordWrapLimit;
	private $enableGraphLink;
	private $parentRelation;
	private $showGraphLabel;
	private $showGraphColor;
	private $showGraphLegend;

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
	private $legendItem = [];

	public function __construct( $options ){
		$this->graphName = $options["graphName"];
		$this->graphSize = $options["graphSize"];
		$this->nodeShape = $options["nodeShape"];
		$this->nodeLabel = $options["nodeLabel"];
		$this->rankDir = $options["rankDir"];
		$this->wordWrapLimit = $options["wordWrapLimit"];
		$this->enableGraphLink = $options["enableGraphLink"];
		$this->parentRelation = $options["parentRelation"];
		$this->showGraphLabel = $options["showGraphLabel"];
		$this->showGraphColor = $options["showGraphColor"];
		$this->showGraphLegend = $options["showGraphLegend"];
	}

	public function getGraph(){
		return $this->graph;
	}

	private function add( $line ){
		$this->graph .= $line;
	}

	public function buildGraph($nodes){
		$this->add("digraph $this->graphName {");

		// set fontsize and fontname of graph, nodes and edges
		$this->add("graph [fontsize=10, fontname=\"Verdana\"]\n");
		$this->add("node [fontsize=10, fontname=\"Verdana\"];\n");
		$this->add("edge [fontsize=10, fontname=\"Verdana\"];\n");

		// choose graphsize, nodeshapes and rank direction
		if ( $this->graphSize != '' ) {
			$this->add("size=\"$this->graphSize\";");
		}

		if ( $this->nodeShape != '' ) {
			$this->add("node [shape=$this->nodeShape];");
		}

		$this->add("rankdir=$this->rankDir;");

		/** @var \SRF\GraphNode $node */
		foreach ( $nodes as $node ) {

			// take "displaytitle" as node-label if it is set
			if ( $this->nodeLabel === GraphPrinter::NODELABEL_DISPLAYTITLE) {
				$objectDisplayTitle = $node->getLabel();
				if ( !empty( $objectDisplayTitle )) {
					$nodeLabel = $this->getWordWrappedText( $objectDisplayTitle,
						$this->wordWrapLimit );
				}
			}

			// add the node
			$this->add( "\"" . $node->getID() . "\"" );

			if ( $this->enableGraphLink ) {

				$nodeLinkURL = "[[" . $node->getID() . "]]";

				if( $nodeLabel === '' ) {
					$this->add( " [URL = \"$nodeLinkURL\"]" );
				} else {
					$this->add( " [URL = \"$nodeLinkURL\", label = \"$nodeLabel\"]" );
				}
			}
			$this->add( "; ");
		}

		foreach ( $nodes as $node ) {

			if ( count( $node->getParentNode() ) > 0 ) {

				foreach ( $node->getParentNode() as $parentNode ) {

					// handle parent/child switch (parentRelation)
					$this->add( $this->parentRelation ? " \"" . $parentNode['object'] . "\" -> \"" . $node->getID() . "\""
						: " \"" . $node->getID() . "\" -> \"" . $parentNode['object'] . "\" " );

					if ( $this->showGraphLabel || $this->showGraphColor ) {
						$this->add( ' [' );

						// add legend item only if missing
						if ( array_search( $parentNode['predicate'], $this->legendItem, true ) === false ) {
							$this->legendItem[] = $parentNode['predicate'];
						}

						// assign color
						$color = $this->graphColors[array_search( $parentNode['predicate'], $this->legendItem, true )];

						// show arrow label (graphLabel is misleading but kept for compatibility reasons)
						if ( $this->showGraphLabel ) {
							$this->add( "label=\"" . $parentNode['predicate'] . "\"" );
							if ( $this->showGraphColor ) {
								$this->add( ",fontcolor=$color," );
							}
						}

						// colorize arrow
						if ( $this->showGraphColor ) {
							$this->add( "color=$color" );
						}
						$this->add( ']' );
					}
					$this->add( ';' );
				}
			}
		}
		$this->add( "}" );
	}

	/**
	 * Creates the graph legend
	 *
	 * @return string Html::rawElement
	 *
	 */
	public function getGraphLegend(){
		$itemsHtml = '';
		$colorCount = 0;
		$arraySize = count( $this->graphColors );

		if ( $this->showGraphLegend && $this->showGraphColor ) {
			foreach ( $this->legendItem as $legendLabel ) {
				if ( $colorCount >= $arraySize ) {
					$colorCount = 0;
				}

				$color = $this->graphColors[$colorCount];
				$itemsHtml .= Html::rawElement( 'div', [ 'class' => 'graphlegenditem', 'style' => "color: $color" ],
					"$color: $legendLabel" );

				$colorCount ++;
			}
		}

		return Html::rawElement( 'div', [ 'class' => 'graphlegend' ], "$itemsHtml");
	}

	/**
	 * Returns the word wrapped version of the provided text.
	 *
	 * @param string $text
	 * @param integer $charLimit
	 *
	 * @return string
	 */
	private function getWordWrappedText( $text, $charLimit ) {
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
}