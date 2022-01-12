<?php

namespace SRF\Graph;

use Html;

/**
 *
 *
 * @see https://www.semantic-mediawiki.org/wiki/Help:Graph_format
 *
 * @license GPL-2.0-or-later
 * @since 3.2
 *
 * @author Sebastian Schmid (gesinn.it)
 *
 */
class GraphFormatter {

	private $graph = '';

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
		if ( $this->options->getGraphSize() !== '' ) {
			$this->add( "size=\"" . $this->options->getGraphSize() . "\";" );
		}

		if ( $this->options->getNodeShape() != '' ) {
			$this->add( "node [shape=" . $this->options->getNodeShape() . "];" );
		}

		$this->add( "rankdir=" . $this->options->getRankDir() . ";\n" );

		/** @var GraphNode $node */
		foreach ( $nodes as $node ) {

			$nodeLabel = $node->getLabel();

			// take "displaytitle" as node-label if it is set
			if ( $this->options->getNodeLabel() === GraphPrinter::NODELABEL_DISPLAYTITLE ) {
				if ( !empty( $nodeLabel ) ) {
					$nodeLabel = $this->getWordWrappedText( $nodeLabel, $this->options->getWordWrapLimit() );
				}
			}

			// URL.
			$nodeLinkURL = $this->options->isGraphLink() ? "[[" . $node->getID() . "]]" : null;

			// Display fields, if any.
			$fields = $node->getFields();
			if ( count( $node->getFields() ) > 0 ) {
				$label = $nodeLabel
					?: strtr( $this->getWordWrappedText( $node->getID(), $this->options->getWordWrapLimit() ),
					          [ '\n' => '<br/>' ] );
				$nodeTooltip = $nodeLabel ?: $node->getID();
				// Label in HTML form enclosed with <>.
				$nodeLabel = "<\n" . '<table border="0" cellborder="0" cellspacing="1" columns="*" rows="*">' . "\n"
							. '<tr><td colspan="2" href="' . $nodeLinkURL . '">' . $label . "</td></tr><hr/>\n"
							. implode( "\n", array_map( static function ( $field ) {
								$alignment = in_array( $field['type'], [ '_num', '_qty', '_dat', '_tem' ] )
									? 'right'
									: 'left';
								return '<tr><td align="left" href="[[Property:' . $field['page'] . ']]">'
									. $field['name'] . '</td>'
									. '<td align="' . $alignment . '">' . $field['value'] . '</td></tr>';
							}, $fields ) ) . "\n</table>\n>";
				$nodeLinkURL = null; // the value at the top is already hyperlinked.
			} else {
				if ( $nodeLabel ) {
					// Label, if any, is enclosed with "".
					$nodeLabel = '"' . htmlspecialchars( $nodeLabel ) . '"';
				}
				$nodeTooltip = null;
			}

			/**
			 * Add nodes to the graph
			 *
			 * @var \SRF\Graph\GraphNode $node
			 */
			$this->add( '"' . htmlspecialchars( $node->getID() ) . '"' );

			$inBrackets = [];
			if ( $nodeLinkURL ) {
				$inBrackets[] = 'URL = "' . $nodeLinkURL . '"';
			}
			if ( $nodeLabel ) {
				$inBrackets[] = 'label = ' . $nodeLabel;
			}
			if ( $nodeTooltip ) {
				$inBrackets[] = 'tooltip = "' . htmlspecialchars( $nodeTooltip ) . '"';
			}
			if ( count( $inBrackets ) > 0 ) {
				$this->add( ' [' . implode( ', ', $inBrackets ) . ']' );
			}

			$this->add( ";\n" );
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
					$this->add( $this->options->getParentRelation()
						? '"' . $parentNode['object'] . '" -> "' . $node->getID() . '"'
						: '"' . $node->getID() . '" -> "' . $parentNode['object'] . '"' );

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
							$this->add( 'label="' . $parentNode['predicate'] . '"' );
							if ( $this->options->isGraphColor() ) {
								$this->add( ",fontcolor=$color," );
							}
						}

						// change arrowhead of edges
						if ( $this->options->getArrowHead() ) {
							$this->add( "arrowhead=" . $this->options->getArrowHead() . ',' );
						}

						// colorize arrow
						if ( $this->options->isGraphColor() ) {
							$this->add( "color=$color" );
						}
						$this->add( ']' );
					}
					$this->add( ";\n" );
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
	 * @param int $charLimit
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
