<?php

namespace SRF\Graph;

class GraphNode {
	private $id;
	private $label = [];
	private $parent = [];

	/**
	 * @var string $id : Node ID including namespace
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * @param integer $labelIndex : label index
	 * @param string $label : a label, e.g. Display Title, used instead of $id. Left align (\l) from label2 onwards
	 */
	public function addLabel( $labelIndex, $label ) {

		if ( $labelIndex == 1 ) {
			// label1 is always single value!
			$this->label[$labelIndex] = $label;
		} else {
			// append to support multivalue
			// (a simple ".=" failed with offset error during testing)
			$this->label[$labelIndex] = array_key_exists($labelIndex, $this->label) ? $this->label[$labelIndex] . $label . "\l" : $label . "\l";
		}
	}

	/**
	 * @var string $predicate : the "predicate" linking an object to a subject
	 * @var srting $object : the object, linked to this node
	 */
	public function addParentNode( $predicate, $object ) {
		$this->parent[] = [
			"predicate" => $predicate,
			"object"    => $object
		];
	}

	/**
	 * @return array Of parent nodes
	 */
	public function getParentNode() {
		return $this->parent;
	}

	/**
	 * @return array: of labels
	 */
	public function getLabels() {
		return $this->label;
	}

	public function getLabel( $labelIndex ) {
		if ( array_key_exists( $labelIndex, $this->label ) ) {
			return $this->label[$labelIndex];
		} else {
			return '';
		}
	}

	public function getID() {
		return $this->id;
	}

}