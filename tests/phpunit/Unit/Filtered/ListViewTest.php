<?php

namespace SRF\Tests\Filtered;

use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;
use SRF\Filtered\View\ListView;

/**
 * @covers \SRF\Filtered\View\ListView
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 */
class ListViewTest extends \PHPUnit\Framework\TestCase {

	public function testHeadersAreShownByDefault() {
		$text = $this->renderList( 'show' );

		$this->assertStringContainsString( '[[Role]]', $text );
		$this->assertStringContainsString( 'Lead', $text );
	}

	public function testHeadersParameterHideLeavesOutThePrintoutLabels() {
		$text = $this->renderList( 'hide' );

		$this->assertStringNotContainsString( 'Role', $text );
		$this->assertStringContainsString( 'Lead', $text );
	}

	public function testHeadersParameterPlainShowsTheLabelsWithoutLinks() {
		$text = $this->renderList( 'plain' );

		$this->assertStringContainsString( 'plain:Role', $text );
		$this->assertStringNotContainsString( '[[Role]]', $text );
	}

	public function testTemplateReceivesTheRowNumberAsRownumberStartingAtOne() {
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getLabel' )->willReturn( 'Role' );
		$printRequest->method( 'getParameter' )->willReturn( false );

		$rows = [];
		foreach ( [ 'row1' => 'Lead', 'row2' => 'Cameo' ] as $id => $value ) {
			$field = $this->createStub( ResultArray::class );
			$field->method( 'getPrintRequest' )->willReturn( $printRequest );
			$field->method( 'getNextText' )->willReturnOnConsecutiveCalls( $value, false );

			$row = $this->createStub( ResultItem::class );
			$row->method( 'getValue' )->willReturn( [ $field ] );
			$rows[$id] = $row;
		}

		$params = [
			'list view type' => 'list', 'list view template' => 'RoleTemplate', 'list view introtemplate' => '',
			'list view outrotemplate' => '', 'list view named args' => false, 'list view userparam' => '',
			'headers' => 'show', 'sep' => ', ',
		];
		$printer = new class( null ) extends Filtered {
			public function getLinker( $firstcol = false, $force = false ): ?\MediaWiki\Linker\Linker {
				return null;
			}
		};

		$text = ( new ListView( $rows, $params, $printer ) )->getResultText();

		$this->assertStringContainsString( '{{RoleTemplate|1=Lead|#=0|#rownumber=1}}', $text );
		$this->assertStringContainsString( '{{RoleTemplate|1=Cameo|#=1|#rownumber=2}}', $text );
	}

	/**
	 * Renders a list view without template for one row with one printout.
	 */
	private function renderList( string $headers ): string {
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getLabel' )->willReturn( 'Role' );
		$printRequest->method( 'getParameter' )->willReturn( false );
		// getText( $outputMode, $linker ): a linker gives a link, null gives plain text
		$printRequest->method( 'getText' )->willReturnCallback(
			static fn ( $outputMode, $linker ) => $linker === null ? 'plain:Role' : '[[Role]]'
		);

		$field = $this->createStub( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printRequest );
		$field->method( 'getNextText' )->willReturnOnConsecutiveCalls( 'Lead', false );

		$row = $this->createStub( ResultItem::class );
		$row->method( 'getValue' )->willReturn( [ $field ] );

		$results = [ 'row1' => $row ];
		$params = [
			'list view type' => 'list',
			'list view template' => '',
			'list view introtemplate' => '',
			'list view outrotemplate' => '',
			'list view named args' => false,
			'list view userparam' => '',
			'headers' => $headers,
			'sep' => ', ',
		];
		$linker = $this->createStub( \MediaWiki\Linker\Linker::class );
		$printer = new class( null, $linker ) extends Filtered {
			public function __construct( $format, private \MediaWiki\Linker\Linker $linker ) {
				parent::__construct( $format );
			}

			public function getLinker( $firstcol = false, $force = false ): ?\MediaWiki\Linker\Linker {
				// a linked header needs a linker, plain output none
				return $force ? $this->linker : null;
			}
		};

		return ( new ListView( $results, $params, $printer ) )->getResultText();
	}
}
