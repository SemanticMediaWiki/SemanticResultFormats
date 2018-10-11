<?php

namespace SRF\Formats\Tree;

/**
 * File holding the Tree class.
 *
 * @author Stephan Gambke
 */

use Exception;
use Html;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\ListResultPrinter;
use SMWQueryResult;
use Title;

/**
 * Result printer that prints query results as a tree (nested html lists).
 *
 * The available formats are 'tree', 'ultree', 'oltree'. 'tree' is an alias of
 * 'ultree'. In an #ask query the parameter 'parent' must be set to contain the
 * name of the property, that gives the parent page of the subject page.
 *
 */
class TreeResultPrinter extends ListResultPrinter {

	private $standardTemplateParameters;

	/**
	 * @var SMWQueryResult | null
	 */
	private $queryResult = null;

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getName()
	 */
	public function getName() {
		// Give grep a chance to find the usages:
		// srf-printername-tree, srf-printername-ultree, srf-printername-oltree
		return \Message::newFromKey( 'srf-printername-' . $this->mFormat )->text();
	}

	/**
	 * @return SMWQueryResult
	 * @throws Exception
	 */
	public function getQueryResult() {

		if ( $this->queryResult === null ) {
			throw new Exception( __METHOD__ . ' called outside of ' . __CLASS__ . '::getResultText().' );
		}

		return $this->queryResult;
	}

	/**
	 * @param SMWQueryResult | null $queryResult
	 */
	public function setQueryResult( $queryResult ) {
		$this->queryResult = $queryResult;
	}

	/**
	 * @see ResultPrinter::postProcessParameters()
	 */
	protected function postProcessParameters() {

		parent::postProcessParameters();

		// Don't support pagination in trees
		$this->mSearchlabel = null;

		// Allow "_" for encoding spaces, as documented
		$this->params['sep'] = str_replace( '_', ' ', $this->params['sep'] );

		if ( !ctype_digit( strval( $this->params['start level'] ) ) || $this->params['start level'] < 1 ) {
			$this->params['start level'] = 1;
		}

	}

	/**
	 * Return serialised results in specified format.
	 *
	 * @param SMWQueryResult $queryResult
	 * @param $outputmode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $queryResult, $outputmode ) {

		$this->setQueryResult( $queryResult );

		if ( $this->params['parent'] === '' ) {
			$this->addError( 'srf-tree-noparentprop' );
			return '';
		}

		$rootHash = $this->getRootHash();

		if ( $rootHash === false ) {
			$this->addError( 'srf-tree-rootinvalid', $this->params['root'] );
			return '';
		}

		$this->hasTemplates =
			$this->params['introtemplate'] !== '' ||
			$this->params['outrotemplate'] !== '' ||
			$this->params['template'] !== '';

		if ( $this->hasTemplates ) {
			$this->initalizeStandardTemplateParameters();
		}

		$tree = $this->buildTreeFromQueryResult( $rootHash );
		$lines = $this->buildLinesFromTree( $tree );

		// Display default if the result is empty
		if ( count( $lines ) === 0 ) {
			return $this->params['default'];
		}

		// FIXME: Linking to further events ($this->linkFurtherResults())
		// does not make sense for tree format. But maybe display a warning?

		$resultText = join(
			"\n",
			array_merge(
				[ $this->getTemplateCall( $this->params['introtemplate'] ) ],
				$lines,
				[ $this->getTemplateCall( $this->params['outrotemplate'] ) ]
			)
		);

		$this->setQueryResult( null );

		return Html::rawElement( 'div', [ 'class' => 'srf-tree' ], $resultText );
	}

	/**
	 * @param string $templateName
	 * @param string[] $params
	 *
	 * @return string
	 */
	public function getTemplateCall( $templateName, $params = [] ) {

		if ( $templateName === '' ) {
			return '';
		}

		return '{{' . $templateName . '|' . join( '|', $params ) . $this->standardTemplateParameters . '}}';
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 * @throws Exception
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['parent'] = [
			'default' => '',
			'message' => 'srf-paramdesc-tree-parent',
		];

		$params['root'] = [
			'default' => '',
			'message' => 'srf-paramdesc-tree-root',
		];

		$params['start level'] = [
			'default' => 1,
			'message' => 'srf-paramdesc-tree-startlevel',
			'type' => 'integer',
		];

		$params['sep'] = [
			'default' => ', ',
			'message' => 'smw-paramdesc-sep',
		];

		$params['template arguments'] = [
			'default' => '',
			'message' => 'smw-paramdesc-template-arguments',
		];

		return $params;
	}

	/**
	 * @param string $rootHash
	 *
	 * @return TreeNode
	 */
	protected function buildTreeFromQueryResult( $rootHash ) {

		$nodes = $this->getHashOfNodes();

		if ( $rootHash !== '' && !array_key_exists( $rootHash, $nodes ) ) {
			return new TreeNode();
		}

		return $this->buildTreeFromNodeList( $rootHash, $nodes );
	}

	/**
	 * @return string | false
	 */
	protected function getRootHash() {

		if ( $this->params['root'] === '' ) {
			return '';
		}

		// get the title object of the root page
		$rootTitle = Title::newFromText( $this->params['root'] );

		if ( $rootTitle !== null ) {
			return DIWikiPage::newFromTitle( $rootTitle )->getSerialization();
		}

		return false;

	}

	/**
	 * @return TreeNode[]
	 */
	protected function getHashOfNodes() {

		/** @var TreeNode[] $nodes */
		$nodes = [];

		$queryResult = $this->getQueryResult();

		$row = $queryResult->getNext();
		while ( $row !== false ) {
			$node = new TreeNode( $row );
			$nodes[$node->getHash()] = $node;
			$row = $queryResult->getNext();
		}

		return $nodes;
	}

	/**
	 * Returns a linker object for making hyperlinks
	 *
	 * @return \Linker
	 */
	public function getLinker( $firstcol = false ) {
		return $this->mLinker;
	}

	/**
	 * Depending on current linking settings, returns a linker object
	 * for making hyperlinks or NULL if no links should be created.
	 *
	 * @param int $column Column number
	 *
	 * @return \Linker|null
	 */
	public function getLinkerForColumn( $column ) {
		return parent::getLinker( $column === 0 );
	}

	private function initalizeStandardTemplateParameters() {

		$query = $this->getQueryResult()->getQuery();
		$userparam = trim( $this->params[ 'userparam' ] );

		$this->standardTemplateParameters =
			( $userparam !== '' ? ( '|userparam=' . $userparam ) : '' ) .
			'|smw-resultquerycondition=' . $query->getQueryString() .
			'|smw-resultquerylimit=' . $query->getLimit() .
			'|smw-resultqueryoffset=' . $query->getOffset();

	}

	/**
	 * @param string $rootHash
	 * @param TreeNode[] $nodes
	 *
	 * @return TreeNode
	 * @throws \Exception
	 */
	protected function buildTreeFromNodeList( $rootHash, $nodes ) {

		$isRootSpecified = $rootHash !== '';

		$root = new TreeNode();
		if ( $isRootSpecified ) {
			$root->addChild( $nodes[$rootHash] );
		}

		$store = $this->getQueryResult()->getStore();
		$parentPointerProperty = DIProperty::newFromUserLabel( $this->params['parent'] );

		foreach ( $nodes as $hash => $node ) {

			$parents = $store->getPropertyValues(
				$node->getResultSubject(),
				$parentPointerProperty
			);

			if ( empty( $parents ) && !$isRootSpecified ) {

				$root->addChild( $node );

			} else {

				foreach ( $parents as $parent ) {

					$parentHash = $parent->getSerialization();

					try {
						if ( array_key_exists( $parentHash, $nodes ) ) {
							$nodes[$parentHash]->addChild( $node );
						} elseif ( !$isRootSpecified ) {
							$root->addChild( $node );
						}
					}
					catch ( Exception $e ) {
						$this->addError( $e->getMessage(), $node->getResultSubject()->getTitle()->getPrefixedText() );
					}
				}
			}
		}
		return $root;
	}

	/**
	 * @param TreeNode $tree
	 *
	 * @return mixed
	 */
	protected function buildLinesFromTree( $tree ) {
		$nodePrinterConfiguration = [
			'format' => trim( $this->params['format'] ),
			'template' => trim( $this->params['template'] ),
			'headers' => $this->params['headers'],
			'named args' => $this->params['named args'],
			'sep' => $this->params['sep'],
		];

		$visitor = new TreeNodePrinter( $this, $nodePrinterConfiguration );
		$lines = $tree->accept( $visitor );
		return $lines;
	}

	/**
	 * @param string $msgkey
	 * @param string | string[] $params
	 */
	protected function addError( $msgkey, $params = [] ) {

		parent::addError(
			\Message::newFromKey( $msgkey )
				->params( $params )
				->inContentLanguage()->text()
		);
	}

}

