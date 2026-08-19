<?php

namespace SRF\Tests\Filtered;

use MediaWiki\MediaWikiServices;
use SMW\Query\PrintRequest;
use SRF\Filtered\Filter\ValueFilter;
use SRF\Filtered\Filtered;

/**
 * @covers \SRF\Filtered\Filter\ValueFilter
 * @covers \SRF\Filtered\Filter\Filter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 *
 * Regression test for issue #802: result formats relying on a Parser that has
 * not been initialised via Parser::parse()/startExternalParse() (e.g. because
 * it was obtained from MediaWikiServices::getInstance()->getParser() in a
 * non-index.php-request context such as the REST API) must not crash with
 * "Call to a member function getMaxIncludeSize() on null" when their
 * ParserOptions are unset.
 *
 * @see https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/802
 */
class ValueFilterTest extends \PHPUnit\Framework\TestCase {

	public function testGetJsConfig_doesNotCrash_whenParserHasNoParserOptions() {
		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();

		// Simulate the state described in #802: a Parser instance that was
		// obtained from the service container but never had parse()/
		// startExternalParse() called on it, so its ParserOptions are unset.
		$this->assertNull(
			$parser->getOptions(),
			'Precondition: a freshly created Parser must not have ParserOptions set yet.'
		);

		$queryPrinter = new Filtered( null );
		$queryPrinter->setParser( $parser );

		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getParameters' )->willReturn( [
			'value filter values' => 'Foo, Bar',
		] );

		$results = [];
		$filter = new ValueFilter( $results, $printRequest, $queryPrinter );

		$jsConfig = $filter->getJsConfig();

		$this->assertSame( [ 'Foo', 'Bar' ], $jsConfig['values'] );
	}
}
