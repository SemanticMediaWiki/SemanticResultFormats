<?php


namespace SRF\Graph;

/**
 * Represents a set of options for the Graph Printer
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
	private $graphFontSize;
	private $nodeShape;
	private $nodeLabel;
	private $rankDir;
	private $arrowHead;
	private $wordWrapLimit;
	private $parentRelation;
	private $enableGraphLink;
	private $showGraphLabel;
	private $showGraphColor;
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

	public function getGraphName(): string {
		return $this->graphName;
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