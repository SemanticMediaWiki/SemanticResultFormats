<?php

namespace SRF\Graph;

use Html;
use SRF\Graph\GraphNode;

/**
 *
 *
 * @see https://www.semantic-mediawiki.org/wiki/Help:Graph_format
 *
 * @license GNU GPL v2+
 * @since 3.2
 *
 * @author Sebastian Schmid (gesinn.it)
 *
 */
class GraphFormatter {

	private $graph = "";

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
	private $options;

	public function __construct( GraphOptions $options ) {
		$this->options = $options;
	}

	public function getGraph() {
		return $this->graph;
	}

	/*
	 * Add a single string to graph
	 *
	 * @param string $line
	 */
	private function add( $line ) {
		$this->graph .= $line;
	}

	/*
	* Creates the DOT (graph description language) which can be processed by the graphviz lib
	*
	* @see https://www.graphviz.org/
	* @since 3.2
	*
	* @param SRF\Graph\GraphNodes[] $nodes
	*/
	public function buildGraph( $nodes ) {
		$this->add( "digraph " . $this->options->getGraphName() . " {" );

		// set fontsize and fontname of graph, nodes and edges
		$this->add( "graph [fontsize=" . $this->options->getGraphFontSize() . ", fontname=\"Verdana\"]\n" );
		$this->add( "node [fontsize=" . $this->options->getGraphFontSize() . ", fontname=\"Verdana\"];\n" );
		$this->add( "edge [fontsize=" . $this->options->getGraphFontSize() . ", fontname=\"Verdana\"];\n" );

		// choose graphsize, nodeshapes and rank direction
		if ( $this->options->getGraphSize() != '' ) {
			$this->add( "size=\"" . $this->options->getGraphSize() . "\";" );
		}

		if ( $this->options->getNodeShape() != '' ) {
			$this->add( "node [shape=" . $this->options->getNodeShape() . "];" );
		}

		$this->add( "rankdir=" . $this->options->getRankDir() . ";" );

		/** @var GraphNode $node */
		foreach ( $nodes as $node ) {

			$nodeLabel = '';

			// take "displaytitle" as node-label if it is set
			if ( $this->options->getNodeLabel() === GraphPrinter::NODELABEL_DISPLAYTITLE ) {
				$objectDisplayTitle = $node->getLabel();
				if ( !empty( $objectDisplayTitle ) ) {
					$nodeLabel = $this->getWordWrappedText( $objectDisplayTitle, $this->options->getWordWrapLimit() );
				}
			}

			/**
			 * Add nodes to the graph
			 *
			 * @var \SRF\Graph\GraphNode $node
			 */
			$this->add( "\"" . $node->getID() . "\"" );

			if ( $this->options->isGraphLink() ) {

				$nodeLinkURL = "[[" . $node->getID() . "]]";

				if ( $nodeLabel === '' ) {
					$this->add( " [URL = \"$nodeLinkURL\"]" );
				} else {
					$this->add( " [URL = \"$nodeLinkURL\", label = \"$nodeLabel\"]" );
				}
			}
			$this->add( "; " );
		}

		/**
		 * Add edges to the graph
		 *
		 * @var \SRF\Graph\GraphNode $node
		 */
		foreach ( $nodes as $node ) {

			if ( count( $node->getParentNode() ) > 0 ) {

				foreach ( $node->getParentNode() as $parentNode ) {

					// handle parent/child switch (parentRelation)
					$this->add( $this->options->getParentRelation() ? " \"" . $parentNode['object'] . "\" -> \"" .
																	  $node->getID() . "\""
						: " \"" . $node->getID() . "\" -> \"" . $parentNode['object'] . "\" " );

					if ( $this->options->isGraphLabel() || $this->options->isGraphColor() ) {
						$this->add( ' [' );

						// add legend item only if missing
						if ( array_search( $parentNode['predicate'], $this->legendItem, true ) === false ) {
							$this->legendItem[] = $parentNode['predicate'];
						}

						// assign color
						$color = $this->graphColors[array_search( $parentNode['predicate'], $this->legendItem, true )];

						// show arrow label (graphLabel is misleading but kept for compatibility reasons)
						if ( $this->options->isGraphLabel() ) {
							$this->add( "label=\"" . $parentNode['predicate'] . "\"" );
							if ( $this->options->isGraphColor() ) {
								$this->add( ",fontcolor=$color," );
							}
						}

						// change arrowhead of edges
						if ( $this->options->getArrowHead() ) {
							$this->add( "arrowhead=" . $this->options->getArrowHead() . "," );
						}

						// colorize arrow
						if ( $this->options->isGraphColor() ) {
							$this->add( "color=$color" );
						}
						$this->add( "]" );
					}
					$this->add( ";" );
				}
			}
		}
		$this->add( "}" );
	}

	/**
	 * Creates the graph legend
	 *
	 * @return string Html::rawElement
	 */
	public function getGraphLegend() {
		$itemsHtml = '';
		$colorCount = 0;
		$arraySize = count( $this->graphColors );

		if ( $this->options->isGraphLegend() && $this->options->isGraphColor() ) {
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

		return Html::rawElement( 'div', [ 'class' => 'graphlegend' ], "$itemsHtml" );
	}

	/**
	 * Returns the word wrapped version of the provided text.
	 *
	 * @param string $text
	 * @param integer $charLimit
	 *
	 * @return string
	 */
	public static function getWordWrappedText( $text, $charLimit ) {
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