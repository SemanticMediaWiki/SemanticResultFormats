<?php

namespace SRF\Graph;

use SMWDataValue;

/**
 * A single data value read from a result row, together with the printout
 * metadata processResultRow() needs to classify it as a node, edge, or field.
 * Captured once per value so the row can be classified in two passes -
 * determining the node first, then collecting edges/fields against it -
 * without re-reading ResultArray's single-pass value iterator.
 *
 * @since 4.0
 *
 * @license GPL-2.0-or-later
 */
class GraphRowValue {

	public SMWDataValue $object;
	public string $objectText;
	public bool $hasProperty;
	public string $type;
	public bool $isPageType;
	public bool $isThisPrintout;
	public string $label;
	public string $canonicalLabel;
	public int $pageTypeSeen;
	public bool $showAsEdge;
	public bool $includeAsEdge;
	public bool $includeAsField;

	public function __construct(
		SMWDataValue $object,
		string $objectText,
		bool $hasProperty,
		string $type,
		bool $isPageType,
		bool $isThisPrintout,
		string $label,
		string $canonicalLabel,
		int $pageTypeSeen,
		bool $showAsEdge,
		bool $includeAsEdge,
		bool $includeAsField
	) {
		$this->object = $object;
		$this->objectText = $objectText;
		$this->hasProperty = $hasProperty;
		$this->type = $type;
		$this->isPageType = $isPageType;
		$this->isThisPrintout = $isThisPrintout;
		$this->label = $label;
		$this->canonicalLabel = $canonicalLabel;
		$this->pageTypeSeen = $pageTypeSeen;
		$this->showAsEdge = $showAsEdge;
		$this->includeAsEdge = $includeAsEdge;
		$this->includeAsField = $includeAsField;
	}
}
