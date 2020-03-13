<?php

namespace SRF;

use Html;
use SMWOutputs as ResourceManager;
use SMWQueryResult as QueryResult;

/**
 * @since 3.0
 *
 * @license GNU GPL v2 or later
 * @author mwjames
 */
class ResourceFormatter {

	/**
	 * @since 3.0
	 *
	 * @param array $modules
	 * @param array $styleModules
	 */
	public static function registerResources( array $modules = [], array $styleModules = [] ) {

		foreach ( $modules as $module ) {
			ResourceManager::requireResource( $module );
		}

		foreach ( $styleModules as $styleModule ) {
			ResourceManager::requireStyle( $styleModule );
		}
	}

	/**
	 * @since 3.0
	 *
	 * @return string
	 */
	public static function session() {
		return 'smw-' . uniqid();
	}

	/**
	 * Convenience method generating a visual placeholder before any
	 * JS is registered to indicate that resources (JavaScript, CSS)
	 * are being loaded and once ready ensure to set
	 * ( '.smw-spinner' ).hide()
	 *
	 * @since 3.0
	 */
	public static function placeholder() {
		self::registerResources( [], [ 'ext.smw.style' ] );

		return Html::rawElement(
			'div',
			[ 'class' => 'srf-loading-dots' ]
		);
	}

	/**
	 *
	 * @since 3.0
	 *
	 * @param string $id
	 * @param array $data
	 */
	public static function encode( $id, $data ) {
		ResourceManager::requireHeadItem(
			$id,
			\Skin::makeVariablesScript(
				[
					$id => json_encode( $data )
				],
				false
			)
		);
	}

	/**
	 * @param QueryResult $queryResult
	 * @param $outputMode
	 *
	 * @return string
	 */
	public static function getData( QueryResult $queryResult, $outputMode, $parameters = [] ) {

		// Add parameters that are only known to the specific printer
		$ask = $queryResult->getQuery()->toArray();

		foreach ( $parameters as $key => $value ) {
			if ( is_string( $value ) || is_integer( $value ) || is_bool( $value ) ) {
				$ask['parameters'][$key] = $value;
			}
		}

		// Combine all data into one object
		$data = [
			'query' => [
				'result' => $queryResult->toArray(),
				'ask' => $ask
			]
		];

		return $data;
	}

}
