<?php

namespace SRF\Graph;

/**
 * Represents a set of options for the Graph Printer
 *
 *
 * @license GPL-2.0-or-later
 * @since 3.2
 *
 * @author Sebastian Schmid (gesinn.it)
 *
 */

class GraphOptions {

	/** @var string */
	private $graphName;

	/** @var string */
	private $graphSize;

	/** @var string */
	private $graphFontSize;

	/** @var string */
	private $nodeShape;

	/** @var string */
	private $nodeLabel;

	/** @var string */
	private $rankDir;

	/** @var string */
	private $arrowHead;

	/** @var string */
	private $wordWrapLimit;

	/** @var bool */
	private $parentRelation;

	/** @var string */
	private $enableGraphLink;

	/** @var string */
	private $showGraphLabel;

	/** @var string */
	private $showGraphColor;

	/** @var string */
	private $showGraphLegend;

	public function __construct( $options ) {
		$this->graphName = trim( $options['graphname'] );
		$this->graphSize = trim( $options['graphsize'] );
		$this->graphFontSize = trim( $options['graphfontsize'] );
		$this->nodeShape = trim( $options['nodeshape'] );
		$this->nodeLabel = trim( $options['nodelabel'] );
		$this->rankDir = strtoupper( trim( $options['arrowdirection'] ) );
		$this->arrowHead = trim( $options['arrowhead'] );
		$this->wordWrapLimit = trim( $options['wordwraplimit'] );
		$this->parentRelation = strtolower( trim( $options['relation'] ) ) == 'parent';
		$this->enableGraphLink = trim( $options['graphlink'] );
		$this->showGraphLabel = trim( $options['graphlabel'] );
		$this->showGraphColor = trim( $options['graphcolor'] );
		$this->showGraphLegend = trim( $options['graphlegend'] );
	}

	/**
	 * Returns a string representation of the graph name.
	 *
	 * @return string
	 */
	public function getGraphName(): string {
		// Remove all special characters from the string to prevent the digraph from being
		// invalid and causing an error.
		return preg_replace('/[^A-Za-z0-9]/', '', $this->graphName );
	}

	public function getGraphSize(): string {
		return $this->graphSize;
	}

	public function getGraphFontSize(): int {
		return $this->graphFontSize;
	}

	public function getNodeShape(): string {
		return $this->nodeShape;
	}

	public function getNodeLabel(): string {
		return $this->nodeLabel;
	}

	public function getRankDir(): string {
		return $this->rankDir;
	}

	public function getArrowHead(): string {
		return $this->arrowHead;
	}

	public function getWordWrapLimit(): int {
		return $this->wordWrapLimit;
	}

	public function getParentRelation(): string {
		return $this->parentRelation;
	}

	public function isGraphLink(): bool {
		return $this->enableGraphLink;
	}

	public function isGraphLabel(): bool {
		return $this->showGraphLabel;
	}

	public function isGraphColor(): bool {
		return $this->showGraphColor;
	}

	public function isGraphLegend(): bool {
		return $this->showGraphLegend;
	}
}
