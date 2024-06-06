<?php

namespace SRF\Outline;

/**
 * A tree structure for holding the outline data
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 */
class OutlineTree {

	public $tree;

	public $items;

	/**
	 * @var int
	 */
	public $itemCount = 0;

	/**
	 * @var int
	 */
	public $leafCount = 0;

	/**
	 * @since 3.1
	 *
	 * @param array $items
	 */
	public function __construct( $items = [] ) {
		$this->tree = [];
		$this->items = $items;
	}

	/**
	 * @since 3.1
	 *
	 * @param $item
	 */
	public function addItem( $item ) {
		$this->items[] = $item;
		$this->itemCount++;
	}

	/**
	 * @since 3.1
	 *
	 * @param $vals
	 * @param $item
	 */
	public function categorizeItem( $vals, $item ) {
		foreach ( $vals as $val ) {
			if ( array_key_exists( $val, $this->tree ) ) {
				$this->tree[$val]->items[] = $item;
				$this->tree[$val]->leafCount++;
			} else {
				$this->tree[$val] = new self( [ $item ] );
				$this->tree[$val]->leafCount++;
			}
		}
	}

	/**
	 * @since 3.1
	 *
	 * @param $property
	 */
	public function addProperty( $property ) {
		if ( $this->items !== null && count( $this->items ) > 0 ) {
			foreach ( $this->items as $item ) {
				$cur_vals = $item->getFieldValues( $property );
				$this->categorizeItem( $cur_vals, $item );
			}
			$this->items = null;
		} else {
			foreach ( $this->tree as $i => $node ) {
				$this->tree[$i]->addProperty( $property );
			}
		}
	}

}
