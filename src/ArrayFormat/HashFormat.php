<?php

declare( strict_types=1 );

namespace SRF\ArrayFormat;

use MediaWiki\MediaWikiServices;

/**
 * Query format for hash tables with features for Extension 'HashTables'
 *
 * @file
 * @ingroup SemanticResultFormats
 * @author Daniel Werner < danweetz@web.de >
 *
 * Doesn't require 'HashTables' extension but has additional features
 * ('name' parameter) if it is available.
 *
 * HashTables 1.0+ is recommended but not necessary.
 */
class HashFormat extends ArrayFormat {

	protected $mLastPageTitle;

	protected function deliverPageTitle( $value, $link = false ) {
		$this->mLastPageTitle = $this->deliverSingleValue( $value, $link );
		return null;
	}

	protected function deliverPageProperties( $perProperty_items ) {
		if ( count( $perProperty_items ) < 1 ) {
			return null;
		}
		return [ $this->mLastPageTitle, implode( $this->mPropSep, $perProperty_items ) ];
	}

	protected function deliverQueryResultPages( $perPage_items ) {
		$hash = [];
		foreach ( $perPage_items as $page ) {
			$hash[$page[0]] = $page[1];
		}
		return parent::deliverQueryResultPages( $hash );
	}

	protected function createArray( $hash ) {
		global $wgHashTables;

		$hashId = $this->mArrayName;
		$version = null;
		if ( defined( 'ExtHashTables::VERSION' ) ) {
			$version = ExtHashTables::VERSION;
		}
		if ( $version !== null && version_compare( $version, '0.999', '>=' ) ) {
			$parser = MediaWikiServices::getInstance()->getParser();
			ExtHashTables::get( $parser )->createHash( $hashId, $hash );
			return true;
		} elseif ( !isset( $wgHashTables ) ) {
			return false;
		} elseif ( $version !== null && version_compare( $version, '0.6', '>=' ) ) {
			$wgHashTables->createHash( $hashId, $hash );
		} else {
			$wgHashTables->mHashTables[trim( $hashId )] = $hash;
		}
		return true;
	}

	protected function applyArrayParameters( array $params ): void {
		parent::applyArrayParameters( $params );
		$this->mShowPageTitles = true;
	}

	public function getParamDefinitions( array $definitions ): array {
		$params = parent::getParamDefinitions( $definitions );
		unset( $params['titles'] );
		$params['name']['message'] = 'srf_paramdesc_hashname';

		return $params;
	}

}
