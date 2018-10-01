<?php

namespace SRF\Formats\Tree;

use Tree\Node\NodeInterface;
use Tree\Visitor\Visitor;

class TreeNodePrinter implements Visitor {

	private $depth = 0;
	private $rowNumber = 0;
	private $configuration = null;
	/**
	 * @var TreeResultPrinter
	 */
	private $resultPrinter = null;
	private $columnLabels = [];

	public function __construct( TreeResultPrinter $resultPrinter, $configuration ) {
		$this->configuration = $configuration;
		$this->resultPrinter = $resultPrinter;
	}

	public function visit( NodeInterface $node ) {

		$nodeTexts = [ $this->getTextForNode( $node ) ];

		$this->depth++;
		$this->rowNumber++;

		foreach ( $node->getChildren() as $child ) {
			$nodeTexts = array_merge(
				$nodeTexts,
				$child->accept( $this )
			);
		}

		$this->depth--;

		return $nodeTexts;
	}

	protected function getTextForNode( TreeNode $node ) {

		/** @var \SMWResultArray[]|null $row */
		$row = $node->getValue();

		if ( $row === null ) {
			return '';
		}

		$textForNode = str_repeat( ( $this->configuration['format'] === 'oltree' ) ? '#' : '*', $this->depth );

		if ( $this->configuration['template'] === '' ) {
			// build simple list
			$textForNode .= $this->getTextForRowNoTemplate( $row );
		} else {
			// build template code
			$textForNode .= $this->getTextForRowWithTemplate( $row );

		}

		return $textForNode;
	}

	/**
	 * @param \SMWResultArray[] $row
	 *
	 * @return string
	 */
	protected function getTextForRowNoTemplate( $row ) {

		$cellTexts = [];
		foreach ( $row as $columnNumber => $cell ) {

			$valuesText = $this->getValuesTextForCell( $cell, $columnNumber );

			if ( $valuesText === '' ) {
				continue;
			}

			$labelText = $this->getLabelForCell( $cell, $columnNumber );

			$cellTexts[] = $labelText . $valuesText;
		}

		if ( count( $cellTexts ) > 0 ) {
			$result = array_shift( $cellTexts );

			if ( count( $cellTexts ) > 0 ) {
				$result .= ' (' . join( $this->configuration['sep'], $cellTexts ) . ')';
			}

		} else {
			$result = '';
		}

		return $result;
	}

	/**
	 * @param \SMWResultArray[] $row
	 *
	 * @return string
	 */
	protected function getTextForRowWithTemplate( $row ) {

		$templateParams = [];
		foreach ( $row as $columnNumber => $cell ) {

			$valuesText = $this->getValuesTextForCell( $cell, $columnNumber );
			$paramName = $this->getParamNameForCell( $cell, $columnNumber );

			$templateParams[] = "$paramName=$valuesText ";
		}

		$templateParams[] = "#=$this->rowNumber ";

		return $this->resultPrinter->getTemplateCall( $this->configuration['template'], $templateParams );
	}

	/**
	 * @param \SMWResultArray $cell
	 * @param int $columnNumber
	 *
	 * @return string
	 */
	protected function getValuesTextForCell( \SMWResultArray $cell, $columnNumber ) {

		$cell->reset();
		$linker = $this->resultPrinter->getLinkerForColumn( $columnNumber );

		$valueTexts = [];

		while ( ( $text = $cell->getNextText( SMW_OUTPUT_WIKI, $linker ) ) !== false ) {
			$valueTexts[] = $text;
		}

		$valuesText = join( $this->configuration['sep'], $valueTexts );
		return $valuesText;
	}

	/**
	 * @param \SMWResultArray $cell
	 * @param int $columnNumber
	 *
	 * @return string
	 */
	protected function getParamNameForCell( $cell, $columnNumber ) {

		if ( !array_key_exists( $columnNumber, $this->columnLabels ) ) {

			$label = $cell->getPrintRequest()->getLabel();

			if ( $this->configuration[ 'named args' ] === true || ( $label === '' ) ) {
				$paramName = $columnNumber + 1;
			} else {
				$paramName = $label;
			}

			$this->columnLabels[$columnNumber] = $paramName;
		}

		return $this->columnLabels[$columnNumber];
	}

	/**
	 * @param \SMWResultArray $cell
	 *
	 * @return string
	 */
	protected function getLabelForCell( $cell, $columnNumber ) {

		if ( !array_key_exists( $columnNumber, $this->columnLabels ) ) {

			if ( $this->configuration['headers'] === 'hide' || $cell->getPrintRequest()->getLabel() === '' ) {
				$labelText = '';
			} elseif ( $this->configuration['headers'] === 'plain' ) {
				$labelText = $cell->getPrintRequest()->getText( SMW_OUTPUT_WIKI ) . ': ';
			} else { // $this->configuration[ 'headers' ] === 'link'
				$labelText = $cell->getPrintRequest()->getText(
						SMW_OUTPUT_WIKI,
						$this->resultPrinter->getLinker()
					) . ': ';
			}

			$this->columnLabels[$columnNumber] = $labelText;
		}

		return $this->columnLabels[$columnNumber];
	}

}
