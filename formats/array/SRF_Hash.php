<?php

use MediaWiki\MediaWikiServices;

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
class SRFHash extends SRFArray {

	protected $mLastPageTitle;

	protected function deliverPageTitle( $value, $link = false ) {
		// remember the page title
		$this->mLastPageTitle = $this->deliverSingleValue( $value, $link );
		// don't add page title into property list
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
			// name of page as key, Properties as value
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
			// Version 1.0+, doesn't use $wgHashTables anymore
			/** ToDo: is there a way to get the actual parser which has started the query? */
			$parser = MediaWikiServices::getInstance()->getParser();
			ExtHashTables::get( $parser )->createHash( $hashId, $hash );
		} elseif ( !isset( $wgHashTables ) ) {
			// Hash extension is not installed in this wiki
			return false;
		} elseif ( $version !== null && version_compare( $version, '0.6', '>=' ) ) {
			// HashTables 0.6 to 1.0
			$wgHashTables->createHash( $hashId, $hash );
		} else {
			// old HashTables, dirty way
			$wgHashTables->mHashTables[trim( $hashId )] = $hash;
		}
		return true;
	}

	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );
		$this->mShowPageTitles = true;
	}

	/**
	 * @see \SMW\Query\ResultPrinters\ResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param IParamDefinition[] $definitions
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );
		// page title is Hash key, otherwise, just use Array format!
		unset( $params['pagetitle'] );
		$params['name']['message'] = 'srf_paramdesc_hashname';

		return $params;
	}

}
