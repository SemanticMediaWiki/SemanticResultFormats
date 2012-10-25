<?php

/**
 * Boilerplate query printer
 *
 * Add your description here ...
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
 * @see http://www.semantic-mediawiki.org/wiki/Writing_result_printers
 *
 * @file
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v2 or later
 *
 * @since 1.8
 *
 * @author mwjames
 */

/**
 * Description ... this part is used for the doxygen processor
 *
 * @ingroup SemanticResultFormats
 */
class SRFBoilerplate extends SMWResultPrinter {

	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		// Add your result printer name here
		return wfMessage( 'srf-printername-boilerplate' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {

		// Data processing
		// It is advisable to separate data processing from output logic
		$data = $this->getResultData( $result, $outputMode );

		// Check if the data processing returned any results otherwise just bailout
		if ( $data === array() ) {
			// Add an error message to return method
			return $result->addErrors( array( wfMessage( 'srf-no-results' )->inContentLanguage()->text() ) );
		} else {
			// Add options if needed to format the output
			$options = array();

			// Return formatted results
			return $this->getFormatOutput( $data, $options );
		}
	}

	/**
	 * Returns an array with numerical data
	 *
	 * @since 1.8
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return array
	 */
	protected function getResultData( SMWQueryResult $result, $outputMode ) {

		while ( $rows = $result->getNext() ) { // Objects (pages)
			/**
			 * @var SMWResultArray $field
			 * @var SMWDataValue $dataValue
			 */
			foreach ( $rows as $field ) {

				while ( ( $dataValue = $field->getNextDataValue() ) !== false ) {

					// This is an example, please do your data selection here

					// If the data value type is of type number, set the output
					// according to this entered typed
					if ( $dataValue->getDataItem()->getDIType() == SMWDataItem::TYPE_NUMBER ){

						// Set unit if available
						$dataValue->setOutputFormat( $this->params['unit'] );

						// Check if unit is available and return the converted value otherwise
						// just return a plain number
						$data[] = $dataValue->getUnit() !== '' ? $dataValue->getShortWikiText() : $dataValue->getNumber() ;
					} else {

						// For all other data types collect the values in an array
						$data[] = $dataValue->getWikiValue();
					}
				}
			}
		}

		// Return selected data
		return $data;
	}

	/**
	 * Prepare data for the output
	 *
	 * @since 1.8
	 *
	 * @param array $data
	 * @param array $options
	 *
	 * @return string
	 */
	protected function getFormatOutput( $data, $options ) {

		// The generated ID is to distinguish similar instances of the same
		// printer that can appear within one page
		static $statNr = 0;
		$ID = 'srf-boilerplate-' . ++$statNr;

		// Used to set that the output is being treated as HTML (opposed to wiki text))
		$this->isHTML = true;

		// Correct escaping is vital to minimize possibilites of malicious code snippets
		// and also a coherent string evalution therefore it is recommended
		// that data transferred to the JS plugin is JSON encoded

		// Assign the ID to make a data instance readly available and distinguishable
		// from other content within the same page
		$requireHeadItem = array ( $ID => FormatJson::encode( $data ) );
		SMWOutputs::requireHeadItem( $ID, Skin::makeVariablesScript( $requireHeadItem ) );

		// Add resource definitions that has been registered with SRF_Resource.php
		// Resource definitions contain scripts, styles, messages etc.
		SMWOutputs::requireResource( 'ext.srf.boilerplate' );

		// Prepares an HTML element showing a rotating spinner indicating that something
		// will appear at this placeholder. The element will be visible as for as
		// long as jquery is not loaded and the JS plugin did not hide/removed the element.
		$processing = SRFUtils::htmlProcessingElement();

		// Add two elements a outer wrapper that is assigned a class which the JS plugin
		// can select and will fetch all instances of the same result printer and an innner
		// container which is set invisible (display=none) for as long as the JS plugin
		// holds the content hidden. It is normally the place where the "hard work"
		// is done hidden from the user until it is ready.
		// The JS plugin can prepare the output within this container without presenting
		// unfinished visual content, to avoid screen clutter and improve user experience.
		return Html::rawElement(
			'div',
			array(
				'class' => 'srf-boilerplate'
			),
			$processing . Html::element(
				'div',
				array(
					'id' => $ID,
					'class' => 'container',
					'style' => 'display:none;'
				),
				null
			)
		);
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

		// Add your parameters here

		// Example of a unit paramter
		$params['unit'] = array(
			'message' => 'srf-paramdesc-unit',
			'default' => '',
		);

		return $params;
	}
}