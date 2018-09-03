<?php

namespace SRF\Formats\Tree;

use Cdb\Exception;
use Tree\Node\Node;
use Tree\Node\NodeInterface;
use Tree\Node\NodeTrait;

class TreeNode extends Node {

	/**
	 * SRFTreeElement constructor.
	 *
	 * @param \SMWResultArray[] | null $row
	 */
	public function __construct( $row = null ) {
		parent::__construct( $row );
	}

	/**
	 * @return string
	 */
	public function getHash() {

		$resultSubject = $this->getResultSubject();

		if ( $resultSubject !== null ) {
			return $resultSubject->getSerialization();
		}

		return '';
	}

	/**
	 * @return null|\SMWDIWikiPage
	 */
	public function getResultSubject() {
		/** @var \SMWResultArray[] | null $row */
		$row = $this->getValue();

		if ( $row !== null ) {
			return $row[0]->getResultSubject();
		}

		return null;
	}

	/**
	 * @param NodeInterface $child
	 *
	 * @return NodeTrait
	 * @throws Exception
	 */
	public function addChild( NodeInterface $child ) {

		foreach ( $this->getAncestorsAndSelf() as $ancestor ) {
			if ( $ancestor->getHash() === $child->getHash() ) {
				throw new Exception( 'srf-tree-circledetected' );
			}
		}

		return parent::addChild( $child );
	}

}

