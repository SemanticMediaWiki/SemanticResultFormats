<?php

/**
 * Common libray of independent functions that are shared among different printers
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */
final class SRFUtils {

	/**
	 * Helper function that generates a html element, representing a
	 * processing/loading image as long as jquery is inactive
	 *
	 * @param boolean $isHtml
	 *
	 * @since 1.8
	 */
	public static function htmlProcessingElement( $isHtml = true ) {
		SMWOutputs::requireResource( 'ext.srf' );

		return Html::rawElement(
			'div',
			[ 'class' => 'srf-spinner mw-small-spinner' ],
			Html::element(
				'span',
				[ 'class' => 'srf-processing-text' ],
				wfMessage( 'srf-module-loading' )->inContentLanguage()->text()
			)
		);
	}

	/**
	 * Add JavaScript variables to the output
	 *
	 * @since 1.8
	 */
	public static function addGlobalJSVariables(){
		$options =  [
			'srfgScriptPath' => $GLOBALS['srfgScriptPath'],
			'srfVersion' => SRF_VERSION
		];

		$requireHeadItem =  [ 'srf.options' => $options ];
		SMWOutputs::requireHeadItem( 'srf.options', Skin::makeVariablesScript( $requireHeadItem, false ) );
	}

	/**
	 * @brief Returns semantic search link for the current query
	 *
	 * Generate a link to access the current ask query
	 *
	 * @since 1.8
	 *
	 * @param string $link
	 *
	 * @return $link
	 */
	public static function htmlQueryResultLink( $link ) {
		// Get linker instance
		$linker = class_exists( 'DummyLinker' ) ? new DummyLinker : new Linker;

		// Set caption
		$link->setCaption( '[+]' );

		// Set parameters
		$link->setParameter( '' , 'class' );
		$link->setParameter( '' , 'searchlabel' );
		return $link->getText( SMW_OUTPUT_HTML, $linker );
	}

}