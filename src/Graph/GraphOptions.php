<?php


namespace SRF\Graph;

/**
 * Represents a set of Options for the Graph Printer
 *
 *
 * @license GNU GPL v2+
 * @since 3.2
 *
 * @author Sebastian Schmid (gesinn.it)
 *
 */

class GraphOptions {

	private $graphName;

	private $graphSize;

	private $nodeShape;

	private $nodeLabel;

	private $rankDir;

	private $wordWrapLimit;

	private $parentRelation;

	private $enableGraphLink;

	private $showGraphLabel;

	private $showGraphColor;

	private $showGraphLegend;

	public function __construct( $options ) {
		
		$this->graphName = trim( $options['graphname'] );
		$this->graphSize = trim( $options['graphsize'] );
		$this->nodeShape = trim( $options['nodeshape'] );
		$this->nodeLabel = trim( $options['nodelabel'] );
		$this->rankDir = strtoupper( trim( $options['arrowdirection'] ) );
		$this->wordWrapLimit = trim( $options['wordwraplimit'] );
		$this->parentRelation = strtolower( trim( $options['relation'] ) ) == 'parent';
		$this->enableGraphLink = trim($options['graphlink']);
		$this->showGraphLabel = trim($options['graphlabel']);
		$this->showGraphColor = trim($options['graphcolor']);
		$this->showGraphLegend = trim( $options['graphlegend'] );
	}

	/**
	 * @return string
	 */
	public function getGraphName(): string {
		return $this->graphName;
	}

	/**
	 * @return string
	 */
	public function getGraphSize(): string {
		return $this->graphSize;
	}

	/**
	 * @return string
	 */
	public function getNodeShape(): string {
		return $this->nodeShape;
	}

	/**
	 * @return string
	 */
	public function getNodeLabel(): string {
		return $this->nodeLabel;
	}

	/**
	 * @return string
	 */
	public function getRankDir(): string {
		return $this->rankDir;
	}

	/**
	 * @return int
	 */
	public function getWordWrapLimit(): int {
		return $this->wordWrapLimit;
	}

	/**
	 * @return string
	 */
	public function getParentRelation(): string {
		return $this->parentRelation;
	}

	/**
	 * @return bool
	 */
	public function isGraphLink(): bool {
		return $this->enableGraphLink;
	}

	/**
	 * @return bool
	 */
	public function isGraphLabel(): bool {
		return $this->showGraphLabel;
	}

	/**
	 * @return bool
	 */
	public function isGraphColor(): bool {
		return $this->showGraphColor;
	}

	/**
	 * @return bool
	 */
	public function isGraphLegend(): bool {
		return $this->showGraphLegend;
	}
}