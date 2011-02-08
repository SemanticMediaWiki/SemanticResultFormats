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
			if ( $count < $minCount ) {
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
		// TODO
		$increaseFactor = 100;
		$maxTags = 1000;
		$minTagSize = 77;
		
		if ( count( $tags ) == 0 ) {
			return $tags;
		}		
		
		arsort( $tags, SORT_NUMERIC );
		
		if ( count( $tags ) > $maxTags ) {
			$tags = array_slice( $tags, 0, $maxTags, true );
		}
	
		$min = end( $tags ) or $min = 0;
		$max = reset( $tags ) or $max = 1;
		
		foreach ( $tags as &$tag ) {
			switch ( $this->sizeMode ) {
				case 'linear':
					$tag = $minTagSize + $increaseFactor * ( $tag -$min ) / ( $max -$min );
					break;
				case 'log' : default :
					$tag = $minTagSize + $increaseFactor * ( log( $tag ) -log( $min ) ) / ( log( $max ) -log( $min ) );
					break;
			}
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
