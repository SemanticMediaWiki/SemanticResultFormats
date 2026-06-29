<?php

declare( strict_types=1 );

namespace SRF\ArrayFormat;

use MediaWiki\MediaWikiServices;
use SMW\DataValueFactory;
use SMW\Query\QueryResult;
use SMW\Query\Result\ResultArray;
use SMW\Query\ResultPrinters\ResultPrinter;
use SMWQuery;
use SMWRecordValue;

/**
 * Query format for arrays with features for Extensions 'Arrays' and 'HashTables'
 *
 * Named ArrayPrinter instead of Array because 'Array' is a reserved keyword in PHP.
 *
 * @file
 * @ingroup SemanticResultFormats
 * @author Daniel Werner < danweetz@web.de >
 *
 * Doesn't require 'Arrays' nor 'HashTables' extensions but has additional features
 * ('name' parameter in either result format) if they are available.
 *
 * Arrays 2.0+ and HashTables 1.0+ are recommended but not necessary.
 */
class ArrayPrinter extends ResultPrinter {

	protected static $mDefaultSeps = [];
	protected $mSep;
	protected $mPropSep;
	protected $mManySep;
	protected $mRecordSep;
	protected $mHeaderSep;
	protected $mArrayName = null;
	protected $mShowPageTitles = true;

	protected $mHideRecordGaps = false;
	protected $mHidePropertyGaps = false;

	/** @var bool true if 'mainlabel' parameter is set to '-' */
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

	protected function getResultText( QueryResult $res, $outputmode ) {
		$perPage_items = [];

		$row = $res->getNext();
		while ( $row !== false ) {
			$perProperty_items = [];

			$isPageTitle = !$this->mMainLabelHack;

			foreach ( $row as $field ) {
				$manyValue_items = [];
				$isMissingProperty = false;

				$manyValues = $field->getContent();

				if ( empty( $manyValues ) ) {
					$delivery = $this->deliverMissingProperty( $field );
					$manyValue_items = $this->fillDeliveryArray( $manyValue_items, $delivery );
					$isMissingProperty = true;
				} else {
					$obj = $field->getNextDataValue();
					while ( $obj !== false ) {

						$value_items = [];
						$isRecord = false;

						if ( $isPageTitle ) {
							if ( !$this->mShowPageTitles ) {
								$isPageTitle = false;
								continue;
							}
							$value_items = $this->fillDeliveryArray(
								$value_items,
								$this->deliverPageTitle( $obj, $this->mLinkFirst )
							);
						} elseif ( $obj instanceof SMWRecordValue ) {
							$recordItems = $obj->getDataItems();
							foreach ( $recordItems as $dataItem ) {
								$recordField = $dataItem !== null ? DataValueFactory::getInstance()->newDataValueByItem( $dataItem, null ) : null;
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
						$obj = $field->getNextDataValue();
					}
				}
				$delivery = $this->deliverPropertiesManyValues(
					$manyValue_items,
					$isMissingProperty,
					$isPageTitle,
					$field
				);
				$perProperty_items = $this->fillDeliveryArray( $perProperty_items, $delivery );
				$isPageTitle = false;
			}
			$delivery = $this->deliverPageProperties( $perProperty_items );
			$perPage_items = $this->fillDeliveryArray( $perPage_items, $delivery );
			$row = $res->getNext();
		}

		return $this->deliverQueryResultPages( $perPage_items );
	}

	protected function fillDeliveryArray( $array = [], $value = null ) {
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
		return trim(
			\Sanitizer::decodeCharReferences( $value->getShortWikiText( $link ) )
		);
	}

	protected function deliverMissingProperty( ResultArray $field ) {
		if ( $this->mHidePropertyGaps ) {
			return null;
		} else {
			return '';
		}
	}

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

		if ( $this->mShowHeaders !== SMW_HEADERS_HIDE && !$isPageTitle ) {
			$linker = $this->mShowHeaders === SMW_HEADERS_PLAIN ? null : $this->mLinker;
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
			$this->createArray( $perPage_items );
			return '';
		} else {
			return implode( $this->mSep, $perPage_items );
		}
	}

	protected function createArray( $array ) {
		global $wgArrayExtension;

		$arrayId = $this->mArrayName;

		if ( defined( 'ExtArrays::VERSION' ) || class_exists( 'ExtArrays' ) ) {
			$parser = MediaWikiServices::getInstance()->getParser();
			ExtArrays::get( $parser )->createArray( $arrayId, $array );
			return true;
		}

		if ( !isset( $wgArrayExtension ) ) {
			return false;
		}
		$version = null;
		if ( defined( 'ArrayExtension::VERSION' ) ) {
			$version = ArrayExtension::VERSION;
		} elseif ( defined( 'ExtArrayExtension::VERSION' ) ) {
			$version = ExtArrayExtension::VERSION;
		}
		if ( $version !== null && version_compare( $version, '1.3.2', '>=' ) ) {
			$wgArrayExtension->createArray( $arrayId, $array );
		} else {
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
				global $wgSrfgArraySepTextualFallbacks;
				$cache = $wgSrfgArraySepTextualFallbacks[$dfltCacheKey] ?? '';
			}
		}
		return $cache;
	}

	protected function getCfgSepText( $obj ) {
		if ( is_array( $obj ) ) {
			if ( !array_key_exists( 0, $obj ) ) {
				return null;
			}

			if ( array_key_exists( 'args', $obj ) && is_array( $obj['args'] ) ) {
				$params = $obj['args'];
			} else {
				$params = [];
			}

			$obj = \Title::newFromText( $obj[0], ( array_key_exists( 1, $obj ) ? $obj[1] : NS_MAIN ) );
		}
		if ( $obj instanceof \Title ) {
			$article = new \Article( $obj );
		} elseif ( $obj instanceof \Article ) {
			$article = $obj;
		} else {
			return $obj;
		}

		$parser = MediaWikiServices::getInstance()->getParser();
		if ( $parser->getOptions() === null ) {
			return null;
		}

		$frame = $parser->getPreprocessor()->newCustomFrame( $params );
		$content = $article->getContent( \Revision::RAW )->getNativeData();
		$text = $parser->preprocessToDom( $content, \Parser::PTD_FOR_INCLUSION );
		$text = trim( $frame->expand( $text ) );

		return $text;
	}

	protected function handleParameters( array $params, $outputmode ): void {
		parent::handleParameters( $params, $outputmode );
		$this->applyArrayParameters( $params );
	}

	protected function applyArrayParameters( array $params ): void {
		$this->mSep = $params['sep'];
		$this->mPropSep = $params['propsep'];
		$this->mManySep = $params['manysep'];
		$this->mRecordSep = $params['recordsep'];
		$this->mHeaderSep = $params['headersep'];

		if ( $params['name'] !== false && ( $this->mInline || trim( $params['name'] ) !== '' ) ) {
			$this->mArrayName = trim( $params['name'] );
			$this->createArray( [] );
		}

		$this->mMainLabelHack = trim( $params['mainlabel'] ) === '-';
		$this->mShowPageTitles = strtolower( $params['titles'] ) !== 'hide';

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

	public function getParamDefinitions( array $definitions ): array {
		$params = parent::getParamDefinitions( $definitions );

		$definitions['limit']->setDefault( $GLOBALS['smwgQMaxInlineLimit'] );
		$definitions['link']->setDefault( 'none' );
		$definitions['headers']->setDefault( 'hide' );

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
