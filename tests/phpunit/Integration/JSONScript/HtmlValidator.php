<?php

namespace SRF\Tests\Integration\JSONScript;

use DOMDocument;
use Symfony\Component\CssSelector\CssSelectorConverter;

/**
 * @license GNU GPL v2+
 * @since   2.5
 *
 * @author Stephan Gambke
 */
class HtmlValidator extends \PHPUnit_Framework_Assert {

	private $documentCache = [];

	/**
	 * @param string $actual
	 * @param string $message
	 */
	public function assertThatHtmlIsValid( $actual, $message = '' ) {

		$document = $this->loadHTML( $actual );
		self::assertTrue( $document !== false, "Failed test `{$message}` (assertion HtmlIsValid) for $actual" );
	}

	/**
	 * @param string | string[] $expected
	 * @param string $actual
	 * @param string $message
	 */
	public function assertThatHtmlContains( $expected, $actual, $message = '' ) {

		$this->doAssert( $expected, $actual, function ( $rule, $count, $expectedCount = false ) use ( $message, $actual ) {
			$expectedCountText = $expectedCount === false? '' : ( $expectedCount . 'x ' );
			$message = "Failed test `{$message}` for assertion HtmlContains: $expectedCountText`$rule` for \n=====\n$actual\n=====";
			self::assertTrue( ($expectedCount === false && $count > 0 ) || ( $count === $expectedCount ), $message );
		} );
	}

	/**
	 * @param string $fragment
	 * @return bool|DOMDocument
	 */
	protected function loadHTML( $fragment ) {

		$cacheKey = md5( $fragment );

		if ( !isset( $this->documentCache[ $cacheKey ] ) ) {

			$fragment = self::wrapHtmlFragment( $fragment );

			$document = new DOMDocument();
			$document->preserveWhiteSpace = false;

			libxml_use_internal_errors( true );
			$result = $document->loadHTML( $fragment );
			libxml_use_internal_errors( false );

			if ( $result === true ) {
				$this->documentCache[ $cacheKey ] = $document;
			} else {
				$this->documentCache[ $cacheKey ] = false;
			}
		}

		return $this->documentCache[ $cacheKey ];
	}

	/**
	 * @param string $fragment
	 * @return string
	 */
	protected static function wrapHtmlFragment( $fragment ) {
		return "<!DOCTYPE html><html><head><meta charset='utf-8'/><title>SomeTitle</title></head><body>$fragment</body></html>";
	}

	/**
	 * @param string[] $rules
	 * @param string $actual
	 * @param callable $cb
	 */
	private function doAssert( $rules, $actual, $cb ) {

		$document = $this->loadHTML( $actual );
		$xpath = new \DOMXPath( $document );
		$converter = new CssSelectorConverter();

		foreach ( $rules as $key => $rule ) {

			if ( is_array( $rule ) ) {
				$expectedCount = array_pop( $rule );
				$rule = array_shift( $rule );
			} else {
				$expectedCount = false;
			}

			$query = $converter->toXPath( $rule );
			$entries = $xpath->evaluate( $query );

			call_user_func( $cb, $rule, $entries->length, $expectedCount );
		}
	}

}
