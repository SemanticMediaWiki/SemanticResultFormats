<?php

namespace SRF;

class GraphNode {
	private $id;
	private $label1;
	private $label2;
	private $label3;
	private $parent = [];

	/**
	 * @var string $id : Node ID including namespace
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * @var string $label : A label, e.g. Display Title, used instead of $m_id
	 */
	public function addLabel1( $label ) {
		$this->label1 = $label;
	}

	/**
	 * @var string $label : append to label2 plus an '/l' for left align
	 *                     the label2 is displayed in the second row of a record shape
	 */
	public function addLabel2( $label ) {
		$this->label2 .= $label . "\l";
	}

	/**
	 * @var string $label : append to label3 plus an '/l' for left align
	 *                     the label3 is displayed in the third row of a record shape
	 */
	public function addLabel3( $label ) {
		$this->label3 .= $label . "\l";
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

	public function getParentNode() {
		return $this->parent;
	}

	public function getLabel1() {
		return $this->label1;
	}

	public function getLabel2() {
		return $this->label2;
	}

	public function getLabel3() {
		return $this->label3;
	}

	public function getID() {
		return $this->id;
	}

}