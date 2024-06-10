<?php

namespace SRF\Graph;

class GraphNode {
	private $id;
	private $parent = [];
	private $label;
	private $fields = [];

	/**
	 * @param string $id : Node ID including namespace
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	public function getID() {
		return $this->id;
	}

	/**
	 * @param string $predicate : the "predicate" linking an object to a subject
	 * @param string $object : the object, linked to this node
	 */
	public function addParentNode( $predicate, $object ) {
		$this->parent[] = [
			'predicate' => $predicate,
			'object'    => $object
		];
	}

	/**
	 * @param string $name : Field name
	 * @param string $value : Field value
	 * @param string $type : Type of the field, for aligning
	 * @param string $page : Property page
	 */
	public function addField( $name, $value, $type, $page ) {
		$this->fields[] = [ 'name' => $name ?: $page, 'value' => $value, 'type' => $type, 'page' => $page ];
	}

	/**
	 * Get all fields.
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @return array Of parent nodes
	 */
	public function getParentNode() {
		return $this->parent;
	}

	/**
	 * @param string $label : a label, e.g. Display Title, used instead of $id.
	 */
	public function setLabel( $label ) {
		$this->label = $label;
	}

	public function getLabel() {
		return $this->label;
	}
}
