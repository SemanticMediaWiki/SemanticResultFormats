<?php

namespace SRF;

use Html;
use RequestContext;
use SMWOutputs as ResourceManager;
use SMWQueryResult as QueryResult;
use SMW\Query\PrintRequest;
use SRFUtils;

/**
 * @since 3.0
 *
 * @license GPL-2.0-or-later
 * @author mwjames
 * @contributor thomas-topway-it
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
		return SRFUtils::htmlProcessingElement();
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
			\ResourceLoader::makeInlineScript(
				\ResourceLoader::makeConfigSetScript(
					[
						$id => json_encode( $data )
					]
				),
				RequestContext::getMain()->getOutput()->getCSP()->getNonce()
			)
		);
	}

	/**
	 * @param PrintRequest[] $printRequests
	 * @param array $ask
	 * @return array
	 */
	private static function appendPreferredPropertyLabel( $printRequests, $ask ) {

		// @see formats/calendar/resources/ext.srf.formats.eventcalendar.js
		// method "init"
		// 
		// var datePropertyList = _calendar.api.query.printouts.search.type(
		// 	data.query.ask.printouts,
		// 	data.query.result.printrequests,
		// 	['_dat'] );
		// 
		// and search.type.normalize in resources/ext.srf.api.query.js
		// which calls getTypeId in resources/ext.srf.api.results.js
		// and expects that the printrequest label and printouts custom label
		// retrieved from the below, match
		// 
		// @TODO all this method can be removed as long as the issue
		// can be fixed at SMW level: PrintRequest's Serializer -> doSerializeProp

		// map canonical labels to labels
		$mapLabels = [];
		foreach ( $printRequests as $key => $printRequest ) {
			$mapLabels[$printRequest->getCanonicalLabel()] = $printRequest->getLabel();
		}

		// @see resources/ext.srf.api.query.js
		foreach ( $ask['printouts'] as $key => $value ) {
			// *** the regex reflects a similar regex in the method
			// ext.srf.api.query.js -> toList
			preg_match( '/^\s*[?&]\s*(.*?)\s*(#.+)?\s*(=.+)?\s*$/', $value, $match );

			// add custom label if added through Preferred property label
			// rather than in the ask query itself
			if ( empty( $match[3] ) && $mapLabels[$match[1]] !== array_search( $mapLabels[$match[1]], $mapLabels ) ) {
				$ask['printouts'][$key] .= '=' . $mapLabels[$match[1]];
			}
		}

		return $ask;
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
			if ( is_string( $value ) || is_int( $value ) || is_bool( $value ) ) {
				$ask['parameters'][$key] = $value;
			}
		}

		$ask = self::appendPreferredPropertyLabel( $queryResult->getPrintRequests(), $ask );

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

