<?php

/**
 * Result printer that prints query results as a tag cloud
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
 * @since 1.5.3
 * 
 * @file SRF_TagCloud.php
 * @ingroup SemanticResultFormats
 * 
 * @licence GNU GPL v3
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SRFTagCloud extends SMWResultPrinter {

	protected $tagsHtml = array();

	/**
	 * Get a human readable label for this printer.
	 *
	 * @return string
	 */
	public function getName() {
		return wfMsg( 'srf_printername_tagcloud' );
	}

	/**
	 * Return serialised results in specified format
	 * 
	 * @param SMWQueryResult $res
	 * @param $outputmode
	 * 
	 * @return string 	  
	 */
	public function getResultText( SMWQueryResult $results, $outputmode ) {
		$this->isHTML = $outputmode == SMW_OUTPUT_HTML;

		if ( $this->params['template'] !== '' ) {
			$this->hasTemplates = true;
			$this->isHTML = false;
		}

		return $this->getTagCloud( $this->getTagSizes( $this->getTags( $results, $outputmode ) ) );
	}

	/**
	 * Returns an array with the tags (keys) and the number of times they occur (values).
	 * 
	 * @since 1.5.3
	 * 
	 * @param SMWQueryResult $results
	 * @param $outputmode
	 * 
	 * @return array
	 */
	protected function getTags( SMWQueryResult $results, $outputmode ) {
		$tags = array();

		while ( /* array of SMWResultArray */ $row = $results->getNext() ) { // Objects (pages)
			for ( $i = 0, $n = count( $row ); $i < $n; $i++ ) { // SMWResultArray for a sinlge property 
				while ( ( /* SMWDataValue */ $dataValue = $row[$i]->getNextDataValue() ) !== false ) { // Data values
					
					$isSubject = $row[$i]->getPrintRequest()->getMode() == SMWPrintRequest::PRINT_THIS;
					
					// If the main object should not be included, skip it.
					if ( $i == 0 && !$this->params['includesubject'] && $isSubject ) {
						continue;
					}
					
					// Get the HTML for the tag content. Pages are linked, other stuff is just plaintext.
					if ( $dataValue->getTypeID() == '_wpg' ) {
						$value = $dataValue->getTitle()->getText();
						$html = $dataValue->getLongText( $outputmode, $this->getLinker( $isSubject ) );
					} else {
						$html = $dataValue->getShortText( $outputmode, $this->getLinker( false ) );
						$value = $html;
					}

					// Replace content with template inclusion  
					$html = $this->params['template'] !== '' ? $this->initTemplateOutput ( $value , $rownum ) : $html;

					if ( !array_key_exists( $value, $tags ) ) {
						$tags[$value] = 0;
						$this->tagsHtml[$value] = $html; // Store the HTML separetely, so sorting can be done easily.
					}
					
					$tags[$value]++;
				}
			}
		}

		foreach ( $tags as $name => $count ) {
			if ( $count < $this->params['mincount'] ) {
				unset( $tags[$name] );
			}
		}
		
		return $tags;
	}

	/**
	 * Determines the sizes of tags.
	 * This method is based on code from the FolkTagCloud extension by Katharina Wäschle.
	 * 
	 * @since 1.5.3
	 * 
	 * @param array $tags
	 * 
	 * @return array
	 */
	protected function getTagSizes( array $tags ) {
		if ( count( $tags ) == 0 ) {
			return $tags;
		}

		// If the original order needs to be kept, we need a copy of the current order.
		if ( $this->params['tagorder'] == 'unchanged' ) {
			$unchangedTags = array_keys( $tags );
		}
		
		arsort( $tags, SORT_NUMERIC );
		
		if ( count( $tags ) > $this->params['maxtags'] ) {
			$tags = array_slice( $tags, 0, $this->params['maxtags'], true );
		}

		$min = end( $tags ) or $min = 0;
		$max = reset( $tags ) or $max = 1;
		$maxSizeIncrease = $this->params['maxsize'] - $this->params['minsize'];
		
		// Loop over the tags, and replace their count by a size.
		foreach ( $tags as &$tag ) {
			switch ( $this->params['increase'] ) {
				case 'linear':
					$divisor = ($max == $min) ? 1 : $max - $min;
					$tag = $this->params['minsize'] + $maxSizeIncrease * ( $tag - $min ) / $divisor;
					break;
				case 'log' : default :
					$divisor = ($max == $min) ? 1 : log( $max ) - log( $min );
					$tag = $this->params['minsize'] + $maxSizeIncrease * ( log( $tag ) - log( $min ) ) / $divisor ;
					break;
			}
		}

		switch ( $this->params['tagorder'] ) {
			case 'desc' :
				// Tags are already sorted desc
				break;
			case 'asc' :
				asort( $tags );
				break;
			case 'alphabetical' :
				$tagNames = array_keys( $tags );
				natcasesort( $tagNames );
				$newTags = array();
				
				foreach ( $tagNames as $name ) {
					$newTags[$name] = $tags[$name];
				}
				
				$tags = $newTags;
				break;
			case 'random' :
				$tagSizes = $tags;
				shuffle( $tagSizes );
				$newTags = array();

				foreach ( $tagSizes as $size ) {
					foreach ( $tags as $tagName => $tagSize ) {
						if ( $tagSize == $size ) {
							$newTags[$tagName] =  $tags[$tagName];
							break;
						}
					}
				}

				$tags = $newTags;
				break;	
			case 'unchanged' : default : // Restore the original order.
				$changedTags = $tags;
				$tags = array();
				
				foreach ( $unchangedTags as $name ) {
					// Original tags might have been left out at this point, so only add remaining ones.
					if ( array_key_exists( $name, $changedTags ) ) {
						$tags[$name] = $changedTags[$name];
					}
				}
				break;
		}

		return $tags;
	}

	/**
	 * Returns the HTML for the tag cloud.
	 * 
	 * @since 1.5.3
	 * 
	 * @param array $tags
	 * 
	 * @return string
	 */
	protected function getTagCloud( array $tags ) {
		$htmlTags = array();

		foreach ( $tags as $name => $size ) {
			$htmlTags[] = Html::rawElement(
				'span',
				array( 'style' => "font-size:$size%" ),
				$this->tagsHtml[$name]
			);
		}
		
		return Html::rawElement(
			'div',
			array( 'align' => 'justify' ),
			implode( ' ', $htmlTags )
		);
	}

	/**
	 * Create a template output
	 * 
	 * @since 1.8
	 * 
	 * @param $value
	 * @return string 	  
	 */
	protected function initTemplateOutput( $value, &$rownum ) {
		$rownum++;
		$wikitext  = $this->params['userparam'] ? "|userparam=" . $this->params['userparam'] : '';
		$wikitext .= "|" . $value;
		$wikitext .= "|#=$rownum";
		return '{{' . trim ( $this->params['template'] ) . $wikitext . '}}';
	}

	/**
	 * @see SMWResultPrinter::getParameters
	 * 
	 * @since 1.5.3
	 * 
	 * @return array
	 */	
	public function getParameters() {
		$params = parent::getParameters();
		
		$params['template'] = new Parameter( 'template' );
		$params['template']->setDescription( wfMsg( 'smw-paramdesc-category-template' ) );
		$params['template']->setDefault( '' );

		$params['userparam'] = new Parameter( 'userparam' );
		$params['userparam']->setDescription( wfMsg( 'smw-paramdesc-category-userparam' ) );
		$params['userparam']->setDefault( '' );	
		
		$params['includesubject'] = new Parameter( 'includesubject', Parameter::TYPE_BOOLEAN );
		$params['includesubject']->setMessage( 'srf_paramdesc_includesubject' );
		$params['includesubject']->setDefault( false );

		$params['increase'] = new Parameter( 'increase' );
		$params['increase']->setMessage( 'srf_paramdesc_increase' );
		$params['increase']->addCriteria( new CriterionInArray( 'linear', 'log' ) );
		$params['increase']->setDefault( 'log' );

		$params['tagorder'] = new Parameter( 'tagorder' );
		$params['tagorder']->setMessage( 'srf_paramdesc_tagorder' );
		$params['tagorder']->addCriteria( new CriterionInArray( 'alphabetical', 'asc', 'desc', 'random', 'unchanged' ) );
		$params['tagorder']->setDefault( 'alphabetical' );

		$params['mincount'] = new Parameter( 'mincount', Parameter::TYPE_INTEGER );
		$params['mincount']->setMessage( 'srf_paramdesc_mincount' );
		$params['mincount']->setDefault( 1 );

		$params['maxtags'] = new Parameter( 'maxtags', Parameter::TYPE_INTEGER );
		$params['maxtags']->setMessage( 'srf_paramdesc_maxtags' );
		$params['maxtags']->setDefault( 1000 );

		$params['minsize'] = new Parameter( 'minsize', Parameter::TYPE_INTEGER );
		$params['minsize']->setMessage( 'srf_paramdesc_minsize' );
		$params['minsize']->setDefault( 77 );

		$params['maxsize'] = new Parameter( 'maxsize', Parameter::TYPE_INTEGER );
		$params['maxsize']->setMessage( 'srf_paramdesc_maxsize' );
		$params['maxsize']->setDefault( 242 );
		
		return $params;
	}		
}