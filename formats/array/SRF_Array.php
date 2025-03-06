<?php
/**
 * Query format for arrays with features for Extensions 'Arrays' and 'HashTables'
 *
 * @file
 * @ingroup SemanticResultFormats
 * @author Daniel Werner < danweetz@web.de >
 *
 * Doesn't require 'Arrays' nor 'HashTables' exytensions but has additional features
 * ('name' parameter in either result format) if they are available.
 *
 * Arrays 2.0+ and HashTables 1.0+ are recommended but not necessary.
 */

use MediaWiki\MediaWikiServices;
use SMW\DataValueFactory;
use SMW\Query\QueryResult;
use SMW\Query\Result\ResultArray;
use SMW\Query\ResultPrinters\ResultPrinter;

/**
 * Array format
 */
class SRFArray extends ResultPrinter {

	protected static $mDefaultSeps = [];
	protected $mSep;
	protected $mPropSep;
	protected $mManySep;
	protected $mRecordSep;
	protected $mHeaderSep;
	protected $mArrayName = null;
	protected $mShowPageTitles;

	protected $mHideRecordGaps;
	protected $mHidePropertyGaps;

	/**
	 * @var bool true if 'mainlabel' parameter is set to '-'
	 */
	protected $mMainLabelHack = false;

	public function __construct( $format, $inline = true ) {
		parent::__construct( $format, $inline );
		// overwrite parent default behavior for linking:
		$this->mLinkFirst = false;
		$this->mLinkOthers = false;
	}

	public function getQueryMode( $context ) {
		return SMWQuery::MODE_INSTANCES;
	}

	public function getName() {
		// Give grep a chance to find the usages:
		// srf_printername_array, srf_printername_hash
		return wfMessage( 'srf_printername_' . $this->mFormat )->text();
	}

	/**
	 * By overwriting this function, we disable default searchlabel handling?
	 * public function getResult( QueryResult $results, array $params, $outputmode ) {
	 * $this->handleParameters( $params, $outputmode );
	 * return $this->getResultText( $results, $outputmode );
	 * }
	 */
	protected function getResultText( QueryResult $res, $outputmode ) {
		/*
		 * @todo
		 * labels of requested properties could define default values. Seems not possible at the moment because
		 * \SMW\Query\PrintRequest::getLable() always returns the property name even if no specific label is defined.
		 */

		$perPage_items = [];

		// for each page:
		while ( $row = $res->getNext() ) {
			$perProperty_items = [];

			/**
			 * first field is always the page title, except, mainlabel is set to '-'
			 *
			 * @todo Is there some other way to check the data value directly for being the
			 *		  page title or not? SMWs behavior could change on mainlabel handling...
			 */
			$isPageTitle = !$this->mMainLabelHack;

			// for each property on that page:
			// $row is array(), $field of type ResultArray
			foreach ( $row as $field ) {
				$manyValue_items = [];
				$isMissingProperty = false;

				$manyValues = $field->getContent();

				// If property is not set (has no value) on a page:
				if ( empty( $manyValues ) ) {
					$delivery = $this->deliverMissingProperty( $field );
					$manyValue_items = $this->fillDeliveryArray( $manyValue_items, $delivery );
					$isMissingProperty = true;
				} else {
					while ( $obj = $field->getNextDataValue() ) {

						$value_items = [];
						$isRecord = false;

						// handle page Title:
						if ( $isPageTitle ) {
							if ( !$this->mShowPageTitles ) {
								$isPageTitle = false;
								continue 2;
							}
							$value_items = $this->fillDeliveryArray(
								$value_items,
								$this->deliverPageTitle( $obj, $this->mLinkFirst )
							);
						} elseif ( $obj instanceof SMWRecordValue ) {
							$recordItems = $obj->getDataItems();
							// walk all single values of the record set:
							foreach ( $recordItems as $dataItem ) {
								$recordField = $dataItem !== null ? DataValueFactory::getInstance(
								)->newDataValueByItem( $dataItem, null ) : null;
								$value_items = $this->fillDeliveryArray(
									$value_items,
									$this->deliverRecordField( $recordField, $this->mLinkOthers )
								);
							}
							$isRecord = true;
						} else {
												$value_items = $this->fillDeliveryArray(
												$value_items,
												$this->deliverSingleValue( $obj, $this->mLinkOthers )
													);
						}
						$delivery = $this->deliverSingleManyValuesData( $value_items, $isRecord, $isPageTitle );
						$manyValue_items = $this->fillDeliveryArray( $manyValue_items, $delivery );
					}
				}
				$delivery = $this->deliverPropertiesManyValues(
					$manyValue_items,
					$isMissingProperty,
					$isPageTitle,
					$field
				);
				$perProperty_items = $this->fillDeliveryArray( $perProperty_items, $delivery );
				// next one could be record or normal value
				$isPageTitle = false;
			}
			$delivery = $this->deliverPageProperties( $perProperty_items );
			$perPage_items = $this->fillDeliveryArray( $perPage_items, $delivery );
		}

		$output = $this->deliverQueryResultPages( $perPage_items );

		return $output;
	}

	/**
	 * Method fillDeliveryArray
	 *
	 */
	protected function fillDeliveryArray( $array = [], $value = null ) {
		// don't create any empty entries
		if ( $value !== null ) {
			$array[] = $value;
		}
		return $array;
	}

	protected function deliverPageTitle( $value, $link = false ) {
		return $this->deliverSingleValue( $value, $link );
	}

	protected function deliverRecordField( $value, $link = false ) {
		if ( $value !== null ) {
			return $this->deliverSingleValue( $value, $link );
		} elseif ( $this->mHideRecordGaps ) {
			return null;
		} else {
			return '';
		}
	}

	protected function deliverSingleValue( $value, $link = false ) {
		// return trim( $value->getShortWikiText( $link ) );
		return trim(
			Sanitizer::decodeCharReferences( $value->getShortWikiText( $link ) )
		);
	}

	/**
	 * Method deliverMissingProperty
	 *
	 * @param ResultArray $field
	 */
	protected function deliverMissingProperty( ResultArray $field ) {
		if ( $this->mHidePropertyGaps ) {
			return null;
		} else {
			return '';
		}
		/** @ToDo: System for Default values?... * */
	}

	/**
	 * Method deliverSingleManyValuesData
	 *
	 * @param $value_items
	 * @param $containsRecord
	 * @param $isPageTitle
	 */
	protected function deliverSingleManyValuesData( $value_items, $containsRecord, $isPageTitle ) {
		if ( empty( $value_items ) ) {
			return null;
		}
		return implode( $this->mRecordSep, $value_items );
	}

	protected function deliverPropertiesManyValues( $manyValue_items, $isMissingProperty, $isPageTitle, ResultArray $data ) {
		if ( empty( $manyValue_items ) ) {
			return null;
		}

		$text = implode( $this->mManySep, $manyValue_items );

		// if property names should be displayed and this is not the page titles value:
		if ( $this->mShowHeaders != SMW_HEADERS_HIDE && !$isPageTitle ) {
			$linker = $this->mShowHeaders == SMW_HEADERS_PLAIN ? null : $this->mLinker;
			$text = $data->getPrintRequest()->getText( SMW_OUTPUT_WIKI, $linker ) . $this->mHeaderSep . $text;
		}
		return $text;
	}

	protected function deliverPageProperties( $perProperty_items ) {
		if ( empty( $perProperty_items ) ) {
			return null;
		}
		return implode( $this->mPropSep, $perProperty_items );
	}

	protected function deliverQueryResultPages( $perPage_items ) {
		if ( $this->mArrayName !== null ) {
			// create Array
			$this->createArray( $perPage_items );
			return '';
		} else {
			return implode( $this->mSep, $perPage_items );
		}
	}

	/**
	 * Helper function to create a new Array within 'Arrays' extension. Takes care of different versions
	 * as well as the old 'ArrayExtension'.
	 */
	protected function createArray( $array ) {
		global $wgArrayExtension;

		$arrayId = $this->mArrayName;

		if ( defined( 'ExtArrays::VERSION' ) ) {
			// 'Arrays' extension 2+
			/** ToDo: is there a way to get the actual parser which has started the query? */
			$parser = MediaWikiServices::getInstance()->getParser();
			ExtArrays::get( $parser )->createArray( $arrayId, $array );
			return true;
		}

		// compatbility to 'ArrayExtension' extension before 2.0:

		if ( !isset( $wgArrayExtension ) ) {
			// Hash extension is not installed in this wiki
			return false;
		}
		$version = null;
		if ( defined( 'ArrayExtension::VERSION' ) ) {
			$version = ArrayExtension::VERSION;
		} elseif ( defined( 'ExtArrayExtension::VERSION' ) ) {
			$version = ExtArrayExtension::VERSION;
		}
		if ( $version !== null && version_compare( $version, '1.3.2', '>=' ) ) {
			// ArrayExtension 1.3.2+
			$wgArrayExtension->createArray( $arrayId, $array );
		} else {
			// dirty way
			$wgArrayExtension->mArrays[trim( $arrayId )] = $array;
		}
		return true;
	}

	protected function initializeCfgValue( $dfltVal, $dfltCacheKey ) {
		if ( !isset( self::$mDefaultSeps ) || !is_array( self::$mDefaultSeps ) ) {
			self::$mDefaultSeps = [];
		}

		$cache = &self::$mDefaultSeps[$dfltCacheKey];

		if ( !isset( $cache ) ) {
			$cache = $this->getCfgSepText( $dfltVal );
			if ( $cache === null ) {
				// cache can't be initialized, probably function-reference in user config
				// but format is not used in inline context, use fallback in this case:
				global $wgSrfgArraySepTextualFallbacks;
				$cache = $wgSrfgArraySepTextualFallbacks[$dfltCacheKey] ?? ''; // Default to empty string
			}
		}
		return $cache;
	}

	protected function getCfgSepText( $obj ) {
		if ( is_array( $obj ) ) {
			// invalid definition:
			if ( !array_key_exists( 0, $obj ) ) {
				return null;
			}

			// check for config-defined arguments to pass to the page before processing it:
			if ( array_key_exists( 'args', $obj ) && is_array( $obj['args'] ) ) {
				$params = $obj['args'];
			} else {
				$params = [];
			}

			// create title of page whose text should be used as separator:
			$obj = Title::newFromText( $obj[0], ( array_key_exists( 1, $obj ) ? $obj[1] : NS_MAIN ) );
		}
		if ( $obj instanceof Title ) {
			$article = new Article( $obj );
		} elseif ( $obj instanceof Article ) {
			$article = $obj;
		} else {
			return $obj;
		}

		/*
		 * Feature to use page value as separator only works if Parser::parse() is running!
		 * That's not the case on semantic search special page for example!
		 */
		// can't use $this->mInline here since SMW 1.6.2 had a bug setting it to false in most cases!
		$parser = MediaWikiServices::getInstance()->getParser();
		if ( $parser->getOptions() === null ) {
			// if( ! $this->mInline ) {
			return null;
		}

		/*
		 * parse page as if it were included like a template. Never use Parser::recursiveTagParse() or similar
		 * for this since it would call hooks we don't want to call and won't return wiki text for inclusion!
		 */
		$frame = $parser->getPreprocessor()->newCustomFrame( $params );
		$content = $article->getContent( Revision::RAW )->getNativeData();
		$text = $parser->preprocessToDom( $content, Parser::PTD_FOR_INCLUSION );
		$text = trim( $frame->expand( $text ) );

		return $text;
	}

	protected function handleParameters( array $params, $outputmode ) {
		// does the link parameter:
		parent::handleParameters( $params, $outputmode );

		// separators:
		$this->mSep = $params['sep'];
		$this->mPropSep = $params['propsep'];
		$this->mManySep = $params['manysep'];
		$this->mRecordSep = $params['recordsep'];
		$this->mHeaderSep = $params['headersep'];

		// only use this in inline mode, if text is given. Since SMW 1.6.2 '' is given, so if
		// we wouldn't check, we would always end up with an array instead of visible output
		if ( $params['name'] !== false && ( $this->mInline || trim( $params['name'] ) !== '' ) ) {
			$this->mArrayName = trim( $params['name'] );
			$this->createArray(
				[]
			);
			// create empty array in case we get no result so we won't have an undefined array in the end.
		}

		// if mainlabel set to '-', this will cause the titles not to appear, so make sure we catch this!
		$this->mMainLabelHack = trim( $params['mainlabel'] ) === '-';

		// whether or not to display the page title:
		$this->mShowPageTitles = strtolower( $params['titles'] ) != 'hide';

		switch ( strtolower( $params['hidegaps'] ) ) {
			case 'none':
				$this->mHideRecordGaps = false;
				$this->mHidePropertyGaps = false;
				break;
			case 'all':
				$this->mHideRecordGaps = true;
				$this->mHidePropertyGaps = true;
				break;
			case 'property':
			case 'prop':
			case 'attribute':
			case 'attr':
				$this->mHideRecordGaps = false;
				$this->mHidePropertyGaps = true;
				break;
			case 'record':
			case 'rec':
			case 'rcrd':
			case 'n-ary':
			case 'nary':
				$this->mHideRecordGaps = true;
				$this->mHidePropertyGaps = false;
				break;
		}
	}

	/**
	 * @see ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		### adjusted basic SMW params: ###

		$definitions['limit']->setDefault( $GLOBALS['smwgQMaxInlineLimit'] );
		$definitions['link']->setDefault( 'none' );
		$definitions['headers']->setDefault( 'hide' );

		### new params: ###

		$params['titles'] = [
			'message' => 'srf_paramdesc_pagetitle',
			'values' => [ 'show', 'hide' ],
			'aliases' => [ 'pagetitle', 'pagetitles' ],
			'default' => 'show',
		];

		$params['hidegaps'] = [
			'message' => 'srf_paramdesc_hidegaps',
			'values' => [ 'none', 'all', 'property', 'record' ],
			'default' => 'none',
		];

		$params['name'] = [
			'message' => 'srf_paramdesc_arrayname',
			'default' => false,
			'manipulatedefault' => false,
		];

		// separators (default values are defined in the following globals:)
		global $srfgArraySep, $srfgArrayPropSep, $srfgArrayManySep, $srfgArrayRecordSep, $srfgArrayHeaderSep;

		$params['sep'] = [
			'message' => 'smw-paramdesc-sep',
			'default' => $this->initializeCfgValue( $srfgArraySep, 'sep' ),
		];

		$params['propsep'] = [
			'message' => 'srf_paramdesc_propsep',
			'default' => $this->initializeCfgValue( $srfgArrayPropSep, 'propsep' ),
		];

		$params['manysep'] = [
			'message' => 'srf_paramdesc_manysep',
			'default' => $this->initializeCfgValue( $srfgArrayManySep, 'manysep' ),
		];

		$params['recordsep'] = [
			'message' => 'srf_paramdesc_recordsep',
			'default' => $this->initializeCfgValue( $srfgArrayRecordSep, 'recordsep' ),
			'aliases' => [ 'narysep', 'rcrdsep', 'recsep' ],
		];

		$params['headersep'] = [
			'message' => 'srf_paramdesc_headersep',
			'default' => $this->initializeCfgValue( $srfgArrayHeaderSep, 'headersep' ),
			'aliases' => [ 'narysep', 'rcrdsep', 'recsep' ],
		];

		return $params;
	}

}
