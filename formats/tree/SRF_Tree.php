<?php

/**
 * File holding the SRFTree class.
 * @author Stephan Gambke
 *
 */

/**
 * Result printer that prints query results as a tree (nested html lists).
 *
 * The available formats are 'tree', 'ultree', 'oltree'. 'tree' is an alias of
 * 'ultree'. In an #ask query the parameter 'parent' must be set to contain the
 * name of the property, that gives the parent page of the subject page.
 *
 */
class SRFTree extends SMWListResultPrinter {

	protected $mTreeProp = null;
	protected $mRoot = null;
	protected $mStartLevel = 1;

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getName()
	 */
	public function getName() {
		// Give grep a chance to find the usages:
		// srf_printername_tree, srf_printername_ultree, srf_printername_oltree
		return wfMessage( 'srf_printername_' . $this->mFormat )->text();
	}

	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

		//// Set in SMWResultPrinter:
		// $this->mIntro = $params['intro'];
		// $this->mOutro = $params['outro'];
		// $this->mSearchlabel = $params['searchlabel'] === false ? null : $params['searchlabel'];
		// $this->mLinkFirst = true | false;
		// $this->mLinkOthers = true | false;
		// $this->mDefault = str_replace( '_', ' ', $params['default'] );
		// $this->mShowHeaders = SMW_HEADERS_HIDE | SMW_HEADERS_PLAIN | SMW_HEADERS_SHOW;
		//// Set in SMWListResultPrinter:
		// $this->mSep = $this->isPlainlist() ? $params['sep'] : '';
		// $this->mTemplate = trim( $params['template'] );
		// $this->mNamedArgs = $params['named args'];
		// $this->mUserParam = trim( $params['userparam'] );
		// $this->mColumns = !$this->isPlainlist() ? $params['columns'] : 1;
		// $this->mIntroTemplate = $params['introtemplate'];
		// $this->mOutroTemplate = $params['outrotemplate'];

		// Don't support pagination in trees
		$this->mSearchlabel = null;

		// Trees are always ul or ol, never plainlists
		$this->mSep = '';

		// Trees support only one column
		$this->mColumns = 1;

		$this->mTreeProp = $params['parent'];
		$this->mRoot = $params['root'];
		$this->mStartLevel = $params['start level'];
	}

	/**
	 * Return serialised results in specified format.
	 */
	protected function getResultText( SMWQueryResult $res, $outputmode ) {

		if ( $this->mTreeProp === '' ) {
			$res->addErrors( array( wfMessage( 'srf-noparentprop' )->inContentLanguage()->text() ) );
			return '';
		}

		$store = $res->getStore();

		// put everything in a list and set parent hashes
		// elements appearing more than once will be inserted more than once and
		// elements with more than one parent will be cloned for each parent,
		// but only one instance will ever be inserted with the hash and
		// only this instance will later be considered as a parent element in the tree
		$tree = array( );

		while ( $row = $res->getNext() ) {

			$element = new SRFTreeElement( $row );

			$hash = $row[0]->getResultSubject()->getSerialization();
			if ( array_key_exists( $hash, $tree ) ) {
				$hash = null;
			}
			
			$parents = $store->getPropertyValues(
					$element->mRow[0]->getResultSubject(), SMWDIProperty::newFromUserLabel( $this->mTreeProp )
			);

			if ( empty( $parents ) ) {
				// no parents: copy into tree as root level item
				if ( $hash !== null ) {
					$tree[$hash] = $element;
				} else {
					$tree[] = $element;					
				}
			} else {
				// one or more parents: copy one copy per parent into tree

				foreach ( $parents as $parent ) {

					if ( $hash !== null ) {

						$tree[$hash] = $element;
						$hash = null;
					} else {

						$element = clone $element;
						$tree[] = $element;
					}

					$element->mParent = $parent->getSerialization();
				}
			}
		}

		$rootElements = array();

		// build pointers from parents to children and remove pointers to parents that don't exist in the tree
		foreach ( $tree as $hash => $element ) {
			if ( $element->mParent !== null ) {
				if ( array_key_exists( $element->mParent, $tree ) ) {
					$tree[$element->mParent]->mChildren[] = $element;
				} else {
					$element->mParent = null;
					$rootElements[$hash] = $element;
				}
			} else {
				$rootElements[$hash] = $element;
			}
		}

		$result = '';
		$rownum = 0;

		// if a specific page was specified as root element of the tree
		if ( $this->mRoot !== '' ) {

			// get the title object of the root page
			$rootTitle = Title::newFromText( $this->mRoot );

			if ( $rootTitle === null ) {
				$res->addErrors( array( wfMessage( 'srf-rootinvalid' )->params( $this->mRoot )->inContentLanguage()->text() ) );
				return '';
			}

			$rootSerialization = SMWDIWikiPage::newFromTitle( $rootTitle )->getSerialization();

			// find the root page in the tree and print it and its subtree
			if ( array_key_exists( $rootSerialization, $tree ) ) {
				$this->printElement( $result, $tree[$rootSerialization], $rownum, $this->mStartLevel );
			}

		} else {

			// iterate through all tree elements
			foreach ( $rootElements as $hash => $element ) {
				// print current root element and its subtree
				$this->printElement( $result, $element, $rownum, $this->mStartLevel );
			}

		}

		return $result;
	}

	protected function printElement( &$result, SRFTreeElement &$element, &$rownum, $level = 1 ) {

		$rownum++;

		$result .= str_pad( '', $level, ($this->mFormat == 'oltree')?'#':'*'  );

		if ( $this->mTemplate !== '' ) { // build template code
			$this->hasTemplates = true;
			$wikitext = ( $this->mUserParam ) ? "|userparam=$this->mUserParam" : '';

			foreach ( $element->mRow as $i => $field ) {
				$wikitext .= '|' . ( $this->mNamedArgs ? '?' . $field->getPrintRequest()->getLabel() : $i + 1 ) . '=';
				$first_value = true;

				while ( ( $text = $field->getNextText( SMW_OUTPUT_WIKI,
				$this->getLinker( $i == 0 ) ) ) !== false ) {

					if ( $first_value ) {
						$first_value = false;
					} else {
						$wikitext .= ', ';
					}
					$wikitext .= $text;
				}
			}

			$wikitext .= "|#=$rownum";
			$result .= '{{' . $this->mTemplate . $wikitext . '}}';
			// str_replace('|', '&#x007C;', // encode '|' for use in templates (templates fail otherwise) -- this is not the place for doing this, since even DV-Wikitexts contain proper "|"!
		} else {  // build simple list
			$first_col = true;
			$found_values = false; // has anything but the first column been printed?

			foreach ( $element->mRow as $field ) {
				$first_value = true;

				$field->reset();

				while ( ( $text = $field->getNextText( SMW_OUTPUT_WIKI, $this->getLinker( $first_col ) ) ) !== false ) {

					if ( !$first_col && !$found_values ) { // first values after first column
						$result .= ' (';
						$found_values = true;
					}

					if ( $first_value ) { // first value in any column, print header
						$first_value = false;

						if ( ( $this->mShowHeaders != SMW_HEADERS_HIDE ) &&
							( $field->getPrintRequest()->getLabel() !== '' ) ) {
							$result .= $field->getPrintRequest()->getText( SMW_OUTPUT_WIKI, ( $this->mShowHeaders == SMW_HEADERS_PLAIN ? null:$this->mLinker ) ) . ' ';
						}
					}

					$result .= $text; // actual output value

				}

				$first_col = false;
			}

			if ( $found_values ) $result .= ')';
		}

		$result .= "\n";

		foreach ( $element->mChildren as $hash => $treeElem ) {

			$this->printElement($result, $treeElem, $rownum, $level + 1);
		}
	}


	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['parent'] = array(
			'default' => '',
			'message' => 'srf-paramdesc-parent',
		);

		$params['root'] = array(
			'default' => '',
			'message' => 'srf-paramdesc-root',
		);

		$params['start level'] = array(
			'default' => 1,
			'message' => 'srf-paramdesc-startlevel',
			'type' => 'integer',
		);

		return $params;
	}

}

class SRFTreeElement {

	var $mChildren = array( );
	var $mParent = null;
	var $mRow = null;

	public function __construct( &$row ) {
		$this->mRow = $row;
	}

}

