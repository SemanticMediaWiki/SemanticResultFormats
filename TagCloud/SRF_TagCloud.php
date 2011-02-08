<?php

/**
 * Result printer that prints query results as a tag cloud.
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

	protected $sizeMode;
	protected $tagOrder;
	protected $minCount;
	protected $increaseFactor;
	protected $maxTags;
	protected $minTagSize;	
	
	public function getName() {
		return wfMsg( 'srf_printername_tagcloud' );
	}

	public function getResult( /* SMWQueryResult */ $results, /* array */ $params, $outputmode ) {
		// skip checks, results with 0 entries are normal
		$this->readParameters( $params, $outputmode );
		return $this->getResultText( $results, SMW_OUTPUT_HTML );
	}
	
	protected function readParameters( $params, $outputmode ) {
		parent::readParameters( $params, $outputmode );
		
		if ( !array_key_exists( 'increase', $params ) || !in_array( $params['increase'], array( 'linear', 'log' ) ) ) {
			$params['increase'] = 'log';
		}
		
		$this->sizeMode = $params['increase'];
		
		if ( !array_key_exists( 'tagorder', $params ) || !in_array( $params['tagorder'], array( 'alphabetic', 'asc', 'desc', 'random', 'unchanged' ) ) ) {
			$params['tagorder'] = 'unchanged';
		}
		
		$this->tagOrder = $params['tagorder'];		
		
		if ( !array_key_exists( 'mincount', $params ) || !ctype_digit( (string)$params['mincount'] ) ) {
			$params['mincount'] = 1;
		}
		
		$this->minCount = $params['mincount'];

		if ( !array_key_exists( 'increase', $params ) || !ctype_digit( (string)$params['increase'] ) ) {
			$params['increase'] = 100;
		}
		
		$this->increaseFactor = $params['increase'];

		if ( !array_key_exists( 'maxtags', $params ) || !ctype_digit( (string)$params['maxtags'] ) ) {
			$params['maxtags'] = 1000;
		}
		
		$this->maxTags = $params['maxtags'];		
		
		if ( !array_key_exists( 'minsize', $params ) || !ctype_digit( (string)$params['minsize'] ) ) {
			$params['minsize'] = 77;
		}
		
		$this->minTagSize = $params['minsize'];		
	}

	public function getResultText( /* SMWQueryResult */ $results, $outputmode ) {
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
		$minCount = 1; // TODO
		
		$tags = array();
		
		while ( /* array of SMWResultArray */ $row = $results->getNext() ) { // Objects (pages)
			for ( $i = 0, $n = count( $row ); $i < $n; $i++ ) { // Properties
				while ( ( $obj = $row[$i]->getNextObject() ) !== false ) { // Property values
					$value = $obj->getTypeID() == '_wpg' ? $obj->getTitle()->getText() : $obj->getShortText( $outputmode );

					if ( !array_key_exists( $value, $tags ) ) {
						$tags[$value] = 0;
					}
					
					$tags[$value]++;
				}					
			}
		}
		
		foreach ( $tags as $name => $count ) {
			if ( $count < $this->minCount ) {
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
		if ( $this->tagOrder == 'unchanged' ) {
			$unchangedTags = array_keys( $tags );
		}
		
		arsort( $tags, SORT_NUMERIC );
		
		if ( count( $tags ) > $this->maxTags ) {
			$tags = array_slice( $tags, 0, $this->maxTags, true );
		}
	
		$min = end( $tags ) or $min = 0;
		$max = reset( $tags ) or $max = 1;
		
		// Loop over the tags, and replace their count by a size.
		foreach ( $tags as &$tag ) {
			switch ( $this->sizeMode ) {
				case 'linear':
					$tag = $this->minTagSize + $this->increaseFactor * ( $tag -$min ) / ( $max -$min );
					break;
				case 'log' : default :
					$tag = $this->minTagSize + $this->increaseFactor * ( log( $tag ) -log( $min ) ) / ( log( $max ) -log( $min ) );
					break;
			}
		}

		switch ( $this->tagOrder ) {
			case 'desc' :
				// Tags are already sorted desc
				break;
			case 'asc' :
				asort( $tags );
				break;
			case 'alphabetic' :
				natcasesort( $tags );
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
			$htmlTags[] = Html::element(
				'span',
				array( 'style' => "font-size:$size%" ),
				$name
			);
		}
		
		return Html::rawElement(
			'div',
			array( 'align' => 'justify' ),
			implode( ' ', $htmlTags )
		);
	}
	
}
