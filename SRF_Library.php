<?php

/**
 * Common libray of independent functions that are shared among different printers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file SRF_Library.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */
final class SRFLibrary {

	/**
	 * Generates proccessing spinner
	 *
	 * @since 1.8
	 */
	public static function htmlProcessingElement() {

		$attribs = array (
			'style' => 'vertical-align: middle;',
			'src'   => "{$GLOBALS['wgStylePath']}/common/images/spinner.gif",
			'title' => wfMsgForContent( 'srf-module-loading' )
		);

		$image = Html::rawElement( 'img', $attribs , null );

		# Attributes
		$attribs = array (
			'style' => 'vertical-align: middle; padding: 1em;',
		);

		$text = Html::rawElement( 'span', $attribs , wfMsgForContent( 'srf-module-loading' ) );

		$attribs = array (
			'class' => 'srf-processing',
			'style' => 'display:block; vertical-align: middle; padding: 1em; background-color: white; color: #666;'
		);

		return Html::rawElement( 'div', $attribs, $image . $text );
	}

	/**
	 * Set SRF global settings
	 *
	 * @since 1.8
	 */
	public static function setSRFGlobalSettings(){
		$options = array (
			'srfgScriptPath' => $GLOBALS['srfgScriptPath'],
			'srfVersion' => SRF_VERSION
		);

		$requireHeadItem = array ( 'srf.options' => $options );
		SMWOutputs::requireHeadItem( 'srf.options', Skin::makeVariablesScript( $requireHeadItem ) );
	}
}