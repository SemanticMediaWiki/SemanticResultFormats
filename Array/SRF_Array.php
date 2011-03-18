<?php
/**
 * Query format for arrays with features for Extensions ArrayExtension and HashTables
 * @file
 * @ingroup SemanticResultFormats
 * @author Daniel Werner
 */

/**
 * Array format
 */
if( !defined('MEDIAWIKI') ) die();

class SRFArray extends SMWResultPrinter {
	protected $mSep;
	protected $mPropSep;
	protected $mManySep;
	protected $mRecordSep;
	protected $mArrayName = null;
	protected $mDeliverPageTitle = true;
	
	protected $mHideRecordGaps = false;
	protected $mHidePropertyGaps = false;

	public function __construct( $format, $inline ) {
		parent::__construct( $format, $inline );
		//overwrite default behavior for linking:
		$this->mLinkFirst = false;
		$this->mLinkOthers = false;
		
		//initialize user configuration from localsettings or load default values:
		$this->initializeUserConfig();
	}
	
	protected function initializeUserConfig() {
		global $srfgArraySep,        $srfgArrayPropSep,        $srfgArrayManySep,        $srfgArrayRecordSep,
		       $srfgArraySepDefault, $srfgArrayPropSepDefault, $srfgArrayManySepDefault, $srfgArrayRecordSepDefault;

		//Sep:
		if( ! isset( $srfgArraySepDefault ) ) {
			$srfgArraySepDefault = self::initializeDefaultSepText( $srfgArraySep );
		} $this->mSep = $srfgArraySepDefault;
		//PropSep:
		if( ! isset( $srfgArrayPropSepDefault ) ) {
			$srfgArrayPropSepDefault = self::initializeDefaultSepText( $srfgArrayPropSep );
		} $this->mPropSep = $srfgArrayPropSepDefault;
		//ManySep:
		if( ! isset( $srfgArrayManySepDefault ) ) {
			$srfgArrayManySepDefault = self::initializeDefaultSepText( $srfgArrayManySep );
		} $this->mManySep = $srfgArrayManySepDefault;
		//Sep:
		if( ! isset( $srfgArrayRecordSepDefault ) ) {
			$srfgArrayRecordSepDefault = self::initializeDefaultSepText( $srfgArrayRecordSep );
		} $this->mRecordSep = $srfgArrayRecordSepDefault;
		
	}	
	
	static function initializeDefaultSepText( $obj ) {
		if( is_array( $obj ) ) {
			if( ! array_key_exists( 0, $obj ) )
				return '';			
			$obj = Title::newFromText( $obj[0], ( array_key_exists( 1, $obj ) ? $obj[1] : NS_MAIN ) );
		}		
		if( $obj instanceof Title ) {
			$article = new Article( $obj );
		} elseif( $obj instanceof Article ) {
			$article = obj;
		} else {
			return $obj; //only text
		}
		global $wgParser;
		return trim( $wgParser->recursiveTagParse( $article->getRawText() ) ); //return rendered text from page
	}

	protected function readParameters( $params, $outputmode ) {
		SMWResultPrinter::readParameters( $params, $outputmode );
		
		//separators:
		if( array_key_exists('sep', $params) )       $this->mSep       = trim( $params['sep'] );
		if( array_key_exists('propsep', $params) )   $this->mPropSep   = trim( $params['propsep'] );
		if( array_key_exists('manysep', $params) )   $this->mManySep   = trim( $params['manysep'] );
		if( array_key_exists('recordsep', $params) ) $this->mRecordSep = trim( $params['recordsep'] );
		
		if( array_key_exists( 'name', $params ) )
			$this->mArrayName = trim( $params['name'] );
		
		if( array_key_exists( 'pagetitle', $params ) )
			$this->mDeliverPageTitle = !( trim( strtolower( $params['pagetitle'] ) ) == 'hide' );
		
		if( array_key_exists( 'hidegaps', $params ) ) {
			switch( trim( strtolower( $params['hidegaps'] ) ) ) {
				case 'none':
					$this->mHideRecordGaps = false;
					$this->mHidePropertyGaps = false;
					break;
				case 'all':
					$this->mHideRecordGaps = true;
					$this->mHidePropertyGaps = true;
					break;
				case 'property': case 'prop': case 'attribute': case 'attr':
					$this->mHideRecordGaps = false;
					$this->mHidePropertyGaps = true;
					break;
				case 'record': case 'rec': case 'n-ary': case 'nary':
					$this->mHideRecordGaps = true;
					$this->mHidePropertyGaps = false;
					break;					
			}
		}
	}

	public function getQueryMode($context) {
		return SMWQuery::MODE_INSTANCES;
	}

	public function getName() {
		wfLoadExtensionMessages('SemanticResultFormats');
		return wfMsg('srf_printername_' . $this->mFormat);
	}

	protected function getResultText( $res, $outputmode ) {
		/*
		 * @ToDo:
		 * labels of requested properties could define default values. Seems not possible at the moment because
		 * SMWPrintRequest::getLable() always returns the property name even if no specific label is defined.
		 */
		 
		$perPage_items = array();
		
		//for each page:
		while( $row = $res->getNext() ) {
			$perProperty_items = array();
			$isPageTitle = true; //first field is always the page title;
			
			//for each property on that page:
			foreach( $row as $field ) { // $row is array(), $field of type SMWResultArray
				$manyValue_items = array();
				$missingProperty = false;
				
				$manyValues = $field->getContent();
				
				//If property is not set (has no value) on a page:
				if( count( $manyValues ) < 1 ) {
					$delivery = $this->deliverMissingProperty( $field );
					$manyValue_items = $this->fillDeliveryArray( $manyValue_items, $delivery );
					$missingProperty = true;
				} else
				//otherwise collect property value (potentially many values)
				foreach( $manyValues as $obj ) { // $manyValues of type SMWResultArray, contains many values (or just one) of one property of type SMWDataValue
					
					$value_items = array();					
					
					if( $isPageTitle ) {
						$isPageTitle = false;
						if( ! $this->mDeliverPageTitle ) {							
							continue 2; //next property
						}
						$value_items = $this->fillDeliveryArray( $value_items, $this->deliverPageTitle( $obj, $this->mLinkFirst ) );						
						$isRecord = false;						
					} elseif( $obj instanceof SMWRecordValue ) {		
						$record = $obj->getDVs();
						$recordLength = count( $obj->getTypeValues() );
						for( $i = 0; $i < $recordLength; $i++ ) {
							$recordField = $record[$i];
							$value_items = $this->fillDeliveryArray( $value_items, $this->deliverRecordField( $recordField, $this->mLinkOthers ) );							
						}
						$isRecord = true;
					} else {						
						$value_items = $this->fillDeliveryArray( $value_items, $this->deliverSingleValue( $obj, $this->mLinkOthers ) );
						$isRecord = false;
					}
					$delivery = $this->deliverSingleManyValuesData( $value_items, $isRecord );
					$manyValue_items = $this->fillDeliveryArray( $manyValue_items, $delivery );
				} // foreach...
				$delivery = $this->deliverPropertiesManyValues( $manyValue_items, $missingProperty );
				$perProperty_items = $this->fillDeliveryArray( $perProperty_items, $delivery );
			} // foreach...			
			$delivery = $this->deliverPageProperties( $perProperty_items );
			$perPage_items = $this->fillDeliveryArray( $perPage_items, $delivery );
		} // while...

		$output = $this->deliverQueryResultPages( $perPage_items );

		return $output;
	}
	
	protected function fillDeliveryArray( $array = array(), $value = null ) {
		if( ! is_null( $value ) ) { //don't create any empty entries
			$array[] = $value;
		}
		return $array;
	}

	protected function deliverPageTitle( $value, $link = false ) {
		return $this->deliverSingleValue( $value, $link );
	}
	protected function deliverRecordField( $value, $link = false ) {
		if( $value !== null ) //void value (null)
			return $this->deliverSingleValue( $value, $link );
		elseif( $this->mHideRecordGaps )
			return null; //hide empty entry
		else
			return ''; //empty string will make sure that array separator will be generated (for record separators)
	}
	protected function deliverSingleValue( $value, $link = false ) {
		return trim( Sanitizer::decodeCharReferences( $value->getShortWikiText( $link ) ) ); // decode: better for further processing with array extension
	}
	// Property not declared on a page:
	protected function deliverMissingProperty( $field ) {
		if( $this->mHidePropertyGaps )
			return null;
		else
			return ''; //empty string will make sure that array separator will be generated
			//@ToDo: System for Default values...
	}
	//represented by an array of record fields or just a single array value:
	protected function deliverSingleManyValuesData( $value_items, $containsRecord = false ) {
		if( count( $value_items ) < 1 ) //happens when one of the higher functions delivers null
			return null;
		return implode( $this->mRecordSep, $value_items );
	}
	protected function deliverPropertiesManyValues( $manyValue_items, $propertyIsMissing = false ) {
		if( count( $manyValue_items ) < 1 )
			return null;
		return implode( $this->mManySep, $manyValue_items );
	}
	protected function deliverPageProperties( $perProperty_items ) {
		if( count( $perProperty_items ) < 1 )
			return null;
		return implode( $this->mPropSep, $perProperty_items );
	}
	protected function deliverQueryResultPages( $perPage_items ) {
		if( $this->mArrayName !== null ) {
			$this->createArray( $perPage_items ); //create Array
			return '';
		} else {
			return implode( $this->mSep, $perPage_items );
		}
	}
	
	protected function createArray( $arr ) {
		global $wgArrayExtension;
		if( ! isset( $wgArrayExtension ) ) //Hash extension is not installed in this wiki		
			return false;
			
		$arrExtClass = new ReflectionClass( get_class( $wgArrayExtension ) );
		
		if( $arrExtClass->hasConstant( 'VERSION' ) && version_compare( $wgArrayExtension::VERSION, '1.3.2', '>=' ) ) {
			$wgArrayExtension->createArray( $this->mArrayName, $arr );  //requires Extension:ArrayExtension 1.3.2 or higher
		} else {
			$wgArrayExtension->mArrayExtension[ $this->mArrayName ] = $arr; //dirty way
		}
		return true;
	}
	
	public function getParameters() {
		return array (
			array( 'name' => 'limit',     'type' => 'int', 'description' => wfMsg( 'smw_paramdesc_limit' ) ),
			
			array( 'name' => 'link',      'type' => 'enumeration', 'description' => wfMsg( 'smw_paramdesc_link' ),      'values' => array( 'all', 'subject', 'none' ) ),
			array( 'name' => 'pagetitle', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_pagetitle' ), 'values' => array( 'show', 'hide' ) ),
			array( 'name' => 'hidegaps',  'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_hidegaps' ),  'values' => array( 'none', 'all', 'property', 'record' ) ),
								
			array( 'name' => 'name',      'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_arrayname' ) ),
			array( 'name' => 'sep',       'type' => 'string', 'description' => wfMsg( 'smw_paramdesc_sep' ) ),
			array( 'name' => 'propsep',   'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_propsep' ) ),
			array( 'name' => 'manysep',   'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_manysep' ) ),
			array( 'name' => 'recordsep', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_recordsep' ) ),
		);
	}
}


class SRFHash extends SRFArray {
	protected $mLastPageTitle;
	
	protected function readParameters( $params, $outputmode ) {
		parent::readParameters( $params, $outputmode );
		//if( array_key_exists('sep', $params) ) $this->mSep = trim( $params['sep'] );
		$this->mDeliverPageTitle = true;
	}
	protected function deliverPageTitle( $value ) {
		$this->mLastPageTitle = $this->deliverSingleValue( $value, false ); //remember the page title
		return null; //don't add page title into property list
	}
	protected function deliverPageProperties( $perProperty_items ) {
		if( count( $perProperty_items ) < 1 )
			return null;
		return array( $this->mLastPageTitle, implode( $this->mPropSep, $perProperty_items ) );
	}
	protected function deliverQueryResultPages( $perPage_items ) {
		foreach( $perPage_items as $page ) {
			$hash[ $page[0] ] = $page[1];  //name of page as key, Properties as value
		}
		return parent::deliverQueryResultPages( $hash );
	}
	protected function createArray( $hash ) {
		global $wgHashTables;
		if( ! isset( $wgHashTables ) ) //Hash extension is not installed in this wiki
			return false;

		$hashExtClass = new ReflectionClass( get_class( $wgHashTables ) );
		
		if( $hashExtClass->hasConstant( 'VERSION' ) && version_compare( $wgHashTables::VERSION, '0.6', '>=' ) ) {
			$wgHashTables->createHash( $this->mArrayName, $hash );  //requires Extension:HashTables 0.6 or higher		
		} else {
			$wgHashTables->mHashTables[ $this->mArrayName ] = $hash; //dirty way
		}
		return true;
	}
	
	public function getParameters() {
		return array (
			array( 'name' => 'limit',     'type' => 'int', 'description' => wfMsg( 'smw_paramdesc_limit' ) ),
			
			array( 'name' => 'link',      'type' => 'enumeration', 'description' => wfMsg( 'smw_paramdesc_link' ),      'values' => array( 'all', 'subject', 'none' ) ),
			array( 'name' => 'hidegaps',  'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_hidegaps' ),  'values' => array( 'none', 'all', 'property', 'record' ) ),
								
			array( 'name' => 'name',      'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_hashname' ) ),
			array( 'name' => 'sep',       'type' => 'string', 'description' => wfMsg( 'smw_paramdesc_sep' ) ),
			array( 'name' => 'propsep',   'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_propsep' ) ),
			array( 'name' => 'manysep',   'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_manysep' ) ),
			array( 'name' => 'recordsep', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_recordsep' ) ),
		);
	}
}
