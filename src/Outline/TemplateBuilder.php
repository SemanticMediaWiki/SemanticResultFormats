<?php

namespace SRF\Outline;

use SMW\Query\PrintRequest;

/**
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class TemplateBuilder {

	/**
	 * @var []
	 */
	private $params = [];

	/**
	 * @var Linker
	 */
	private $linker;

	/**
	 * @var string
	 */
	private $template = '';

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
	public function build( $tree ) {
		$this->tree( $tree );

		return $this->template;
	}

	private function tree( $tree, $level = 0 ) {

		if ( $tree->mUnsortedItems !== null ) {
			foreach ( $tree->mUnsortedItems as $i => $item ) {
				$this->template .= $this->item( $i, $item );
			}
		}

		foreach ( $tree->mTree as $key => $node ) {
			$property = $this->params['outlineproperties'][$level];
			$class = $this->params['template'] . '-section-' . strtolower( str_replace( ' ', '-', $property ) );

			$this->template .= "<div class='$class'>";
			$this->template .= $this->open( $this->params['template'] . "-header" );
			$this->template .= $this->parameter( $property, $key );
			$this->template .= $this->parameter( "#outlinelevel", $level );
			$this->template .= $this->parameter( "#itemcount",  $node->leafCount );
			$this->template .= $this->parameter( "#userparam", $this->params['userparam'] );
			$this->template .= $this->close();
			$this->template .= "<div class='" . $this->params['template'] . "-items'>";
			$this->tree( $node, $level + 1 );
			$this->template .= "</div>";
			$this->template .= "</div>";
		}
	}

	private function item( $i, $item ) {

		$first_col = true;
		$template = '';
		$linker = $this->params['link'] === 'all' ? $this->linker : null;
		$itemnumber = 0;

		foreach ( $item->mRow as $resultArray ) {

			$printRequest = $resultArray->getPrintRequest();
			$val = $printRequest->getText( SMW_OUTPUT_WIKI, null );

			if ( in_array( $val, $this->params['outlineproperties'] ) ) {
				continue;
			}

			while ( ( $dv = $resultArray->getNextDataValue() ) !== false ) {
				$template .= $this->open( $this->params['template'] . '-item' );
				$template .= $this->parameter( "#itemsection", $i );

				$template .= $this->parameter( "#itemnumber", $itemnumber );
				$template .= $this->parameter( "#userparam", $this->params['userparam'] );

				$template .= $this->itemText( $dv, $linker, $printRequest, $first_col );
				$template .= $this->close();

				$itemnumber++;
			}
		}

		return "<div class='" . $this->params['template'] . "-item'>" . $template . '</div>';
	}

	private function itemText( $dv, $linker, $printRequest, &$first_col ) {

		if ( $first_col && $printRequest->isMode( PrintRequest::PRINT_THIS ) ) {
			$first_col = false;

			if ( $linker === null && ( $caption = $dv->getDisplayTitle() ) !== '' ) {
				$dv->setCaption( $caption );
			}

			$text = $dv->getShortText(
				SMW_OUTPUT_WIKI,
				$this->params['link'] === 'subject' ? $this->linker : $linker
			);

			return $this->parameter( "#itemsubject", $text );
		}

		$text = $dv->getShortText(
			SMW_OUTPUT_WIKI,
			$linker
		);

		return $this->parameter( $printRequest->getLabel(), $text );
	}

	private function open( $v ) {
		return "{{" . $v;
	}

	private function parameter( $k, $v ) {
		return " |$k=$v";
	}

	private function close() {
		return "}}";
	}

}