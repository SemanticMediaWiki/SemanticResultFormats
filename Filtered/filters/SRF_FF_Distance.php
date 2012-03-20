<?php

/**
 * File holding the SRF_FF_Distance class
 *
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

/**
 * The SRF_FF_Distance class.
 *
 * Available parameters for this filter:
 *   distance filter origin: the point from which the distance is measured (address or geo coordinate)
 *   distance filter property: the property containing the point to which distance is measured - not implemented yet
 *   distance filter unit: the unit in which the distance is measured
 *
 * @ingroup SemanticResultFormats
 */
class SRF_FF_Distance extends SRF_Filtered_Filter {
	
	private $mUnit = null;
	private $mMaxDistance = 1;

	public function __construct( &$results, SMWPrintRequest $printRequest, SRFFiltered &$queryPrinter ) {
		parent::__construct($results, $printRequest, $queryPrinter);
		
		if ( !defined('Maps_VERSION') || version_compare( Maps_VERSION, '1.0', '<' ) ) {
			throw new FatalError('You need to have the <a href="http://www.mediawiki.org/wiki/Extension:Maps">Maps</a> extension version 1.0 or higher installed in order to use the distance filter.<br />');
		}
		
		MapsGeocoders::init();
		
		$params = $this->getActualParameters();

		if (  array_key_exists( 'distance filter origin', $params ) ) {
			$origin = MapsGeocoders::attemptToGeocode( $params['distance filter origin'] );
		} else {
			$origin = array( 'lat'=>'0', 'lon' => '0' );
		}

		if ( array_key_exists( 'distance filter unit', $params ) ) {
			$this->mUnit = MapsDistanceParser::getValidUnit( $params['distance filter unit'] );
		} else {
			$this->mUnit = MapsDistanceParser::getValidUnit();
		}

		$targetLabel = $printRequest->getLabel();
		
		foreach ( $this->getQueryResults() as $id => $filteredItem ) {

			$row = $filteredItem->getValue();
			
			// $filteredItem is of class SRF_Filtered_Item
			// $row is an array of SMWResultArray
			
			foreach ( $row as $field ) {
				
				// $field is an SMWResultArray
				
				$label = $field->getPrintRequest()->getLabel();
				
				if ($label === $targetLabel) {
					$field->reset();
					$dataValue = $field->getNextDataValue(); // only use first value

					if ( $dataValue !== false ) {
						
						$posText = $dataValue->getShortText( SMW_OUTPUT_WIKI, false );
						$pos = MapsGeocoders::attemptToGeocode( $posText );
						
						if ( is_array( $pos ) ){
							$distance = round( MapsGeoFunctions::calculateDistance( $origin, $pos ) / MapsDistanceParser::getUnitRatio( $this->mUnit ) );
							
							if ( $distance > $this->mMaxDistance ) {							
								$this->mMaxDistance = $distance;
							}
							
						} else {
							$distance = -1;
						}
						
					} else {
						$distance = -1;  // no location given
					}
					$filteredItem->setData( 'distance-filter', $distance );
					break;
				}
				
			}
		}
		
		if ( $this->mMaxDistance > 1 ) {
			$base = pow( 10, floor( log10( $this->mMaxDistance ) ) );
			$this->mMaxDistance = ceil ( $this->mMaxDistance / $base ) * $base;
		}
		
	}

	/**
	 * Returns the name (string) or names (array of strings) of the resource
	 * modules to load.
	 *
	 * @return string|array
	 */
	public function getResourceModules() {
		return 'ext.srf.filtered.distance-filter';
	}

	/**
	 * Returns an array of config data for this filter to be stored in the JS
	 * @return null
	 */
	public function getJsData() {
		$params = $this->getActualParameters();
		
		$ret = array();

		$ret['unit'] = $this->mUnit;
		$ret['max'] = $this->mMaxDistance;

		if ( array_key_exists( 'distance filter collapsible', $params ) ) {
			$ret['collapsible'] = trim($params['distance filter collapsible']);
		}

		return $ret;
	}

}
