<?php

namespace SRF\Outline;

use SMW\Query\PrintRequest;
use SRF\Outline\OutlineTree;
use SMWDataItem as DataItem;

/**
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class ListTreeBuilder {

	/**
	 * @var []
	 */
	private $params = [];

	/**
	 * @var Linker
	 */
	private $linker;

	/**
	 * @param array $params
	 */
	public function __construct( array $params ) {
		$this->params = $params;
	}

	/**
	 * @since 3.1
	 *
	 * @param Linker|null|false $linker
	 */
	public function setLinker( $linker ) {
		$this->linker = $linker;
	}

	/**
	 * @since 3.1
	 *
	 * @param OutlineTree $tree
	 *
	 * @return string
	 */
	public function build( OutlineTree $outlineTree ) {
		return $this->tree( $outlineTree );
	}

	private function tree( $outline_tree, $level = 0 ) {
		$text = "";

		if ( !is_null( $outline_tree->items ) ) {
			$text .= "<ul>\n";
			foreach ( $outline_tree->items as $item ) {
				$text .= "<li>{$this->item($item)}</li>\n";
			}
			$text .= "</ul>\n";
		}

		if ( $level > 0 ) {
			$text .= "<ul>\n";
		}

		$num_levels = count( $this->params['outlineproperties'] );
		// set font size and weight depending on level we're at
		$font_level = $level;

		if ( $num_levels < 4 ) {
			$font_level += ( 4 - $num_levels );
		}

		if ( $font_level == 0 ) {
			$font_size = 'x-large';
		} elseif ( $font_level == 1 ) {
			$font_size = 'large';
		} elseif ( $font_level == 2 ) {
			$font_size = 'medium';
		} else {
			$font_size = 'small';
		}

		if ( $font_level == 3 ) {
			$font_weight = 'bold';
		} else {
			$font_weight = 'regular';
		}

		foreach ( $outline_tree->tree as $key => $node ) {
			$text .= "<p style=\"font-size: $font_size; font-weight: $font_weight;\">$key</p>\n";
			$text .= $this->tree( $node, $level + 1 );
		}

		if ( $level > 0 ) {
			$text .= "</ul>\n";
		}

		return $text;
	}

	private function item( $item ) {
		$first_col = true;
		$found_values = false; // has anything but the first column been printed?
		$result = "";

		foreach ( $item->row as $resultArray ) {

			$printRequest = $resultArray->getPrintRequest();
			$val = $printRequest->getText( SMW_OUTPUT_WIKI, null );
			$first_value = true;

			if ( in_array( $val, $this->params['outlineproperties'] ) ) {
				continue;
			}

			$linker = $this->params['link'] === 'all' ? $this->linker : null;

			if ( $this->params['link'] === 'subject' && $printRequest->isMode( PrintRequest::PRINT_THIS ) ) {
				$linker = $this->linker;
			}

			while ( ( $dv = $resultArray->getNextDataValue() ) !== false ) {

				if ( !$first_col && !$found_values ) { // first values after first column
					$result .= ' (';
					$found_values = true;
				} elseif ( $found_values || !$first_value ) {
					// any value after '(' or non-first values on first column
					$result .= ', ';
				}

				if ( $first_value ) { // first value in any column, print header
					$first_value = false;
					if ( $this->params['showHeaders'] && ( '' != $printRequest->getLabel() ) ) {
						$result .= $printRequest->getText( SMW_OUTPUT_WIKI, $linker ) . ' ';
					}
				}

				$dataItem = $dv->getDataItem();

				if ( $linker === null && $dataItem->getDIType() === DataItem::TYPE_WIKIPAGE && ( $caption = $dv->getDisplayTitle() ) !== '' ) {
					$dv->setCaption( $caption );
				}

				$result .= $dv->getShortText( SMW_OUTPUT_WIKI, $linker );
			}

			$first_col = false;
		}

		if ( $found_values ) {
			$result .= ')';
		}

		return $result;
	}

}