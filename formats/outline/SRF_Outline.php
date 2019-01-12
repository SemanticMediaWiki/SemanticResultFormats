<?php

use SRF\Outline\TemplateBuilder;
use SRF\Outline\ListTreeBuilder;
use SRF\Outline\OutlineTree;
use SRF\Outline\OutlineItem;

/**
 * A class to print query results in an outline format, along with some
 * helper classes to handle the aggregation
 *
 * @license GNU GPL v2+
 * @since 1.4.3
 *
 * @author Yaron Koren
 */
class SRFOutline extends SMWResultPrinter {

	/**
	 * @see ResultPrinter::getName
	 *
	 * {@inheritDoc}
	 */
	public function getName() {
		return wfMessage( 'srf_printername_outline' )->text();
	}

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * {@inheritDoc}
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['outlineproperties'] = [
			'islist' => true,
			'default' => [],
			'message' => 'srf_paramdesc_outlineproperties',
		];

		$params[] = [
			'name' => 'template',
			'message' => 'smw-paramdesc-template',
			'default' => '',
		];

		$params[] = [
			'name' => 'userparam',
			'message' => 'smw-paramdesc-userparam',
			'default' => '',
		];

		$params[] = [
			'name' => 'named args',
			'type' => 'boolean',
			'message' => 'smw-paramdesc-named_args',
			'default' => true,
		];

		return $params;
	}

	/**
	 * @see ResultPrinter::getResultText
	 *
	 * {@inheritDoc}
	 */
	protected function getResultText( SMWQueryResult $res, $outputMode ) {

		// for each result row, create an array of the row itself
		// and all its sorted-on fields, and add it to the initial
		// 'tree'
		$outlineTree = new OutlineTree();
		while ( $row = $res->getNext() ) {
			$outlineItem = new OutlineItem( $row );

			foreach ( $row as $field ) {
				$field_name = $field->getPrintRequest()->getText( SMW_OUTPUT_HTML );

				if ( in_array( $field_name, $this->params['outlineproperties'] ) ) {
					while ( ( $object = $field->getNextDataValue() ) !== false ) {
						$field_val = $object->getLongWikiText( $this->getLinker() );
						$outlineItem->addFieldValue( $field_name, $field_val );
					}
				}
			}

			$outlineTree->addItem( $outlineItem );
		}

		// now, cycle through the outline properties, creating the
		// tree
		foreach ( $this->params['outlineproperties'] as $property ) {
			$outlineTree->addProperty( $property );
		}

		if ( $this->params['template'] !== '' ) {
			$this->hasTemplates = true;
			$templateBuilder = new TemplateBuilder(
				$this->params
			);

			$templateBuilder->setLinker( $this->mLinker );
			$result = $templateBuilder->build( $outlineTree );
		} else {
			$listTreeBuilder = new ListTreeBuilder(
				$this->params + [ 'showHeaders' => $this->mShowHeaders ]
			);

			$listTreeBuilder->setLinker( $this->mLinker );
			$result = $listTreeBuilder->build( $outlineTree );
		}

		if ( $this->linkFurtherResults( $res ) ) {
			$link = $this->getFurtherResultsLink( $res, $outputMode );

			$result .= $link->getText( $outputMode, $this->mLinker ) . "\n";
		}

		return $result;
	}

}
