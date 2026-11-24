<?php

namespace SRF\Tests\Filtered;

use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;
use SRF\Filtered\View\TableView;

/**
 * @covers \SRF\Filtered\View\TableView
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 */
class TableViewTest extends \PHPUnit\Framework\TestCase {

	public function testCellsGetTheClassOfTheirOwnColumn() {
		$html = $this->renderTable( 'show', [ 'First' => false, 'Second Col' => false ] );

		$this->assertStringContainsString( '<td class="First">v-First</td>', $html );
		$this->assertStringContainsString( '<td class="Second-Col">v-Second Col</td>', $html );
	}

	public function testAHiddenColumnDoesNotShiftTheClassesOfTheFollowingColumns() {
		$html = $this->renderTable( 'show', [ 'Hidden' => true, 'First' => false, 'Second' => false ] );

		$this->assertStringNotContainsString( 'v-Hidden', $html, 'the hidden column is not shown' );
		$this->assertStringContainsString( '<td class="First">v-First</td>', $html );
		$this->assertStringContainsString( '<td class="Second">v-Second</td>', $html );
	}

	public function testCellsKeepTheirColumnClassWhenTheHeadersAreHidden() {
		$html = $this->renderTable( 'hide', [ 'First' => false, 'Second' => false ] );

		$this->assertStringNotContainsString( '<th', $html, 'no header cells' );
		$this->assertStringContainsString( '<td class="First">v-First</td>', $html );
		$this->assertStringContainsString( '<td class="Second">v-Second</td>', $html );
	}

	/**
	 * Renders a table of one row with the given printouts (label => hidden).
	 */
	private function renderTable( string $headers, array $printouts ): string {
		$fields = [];

		foreach ( $printouts as $label => $hidden ) {
			$fields[] = $this->newField( $label, $hidden );
		}

		$row = $this->createStub( ResultItem::class );
		$row->method( 'getValue' )->willReturn( $fields );

		$results = [ 'row1' => $row ];
		$params = [ 'headers' => $headers ];
		$printer = new class( null ) extends Filtered {
			public function getLinker( $firstcol = false, $force = false ): ?\MediaWiki\Linker\Linker {
				return null;
			}
		};

		return ( new TableView( $results, $params, $printer ) )->getResultText();
	}

	private function newField( string $label, bool $hidden ): ResultArray {
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getText' )->willReturn( $label );
		$printRequest->method( 'getMode' )->willReturn( PrintRequest::PRINT_PROP );
		$printRequest->method( 'getParameter' )->willReturnCallback(
			static fn ( $name ) => $name === 'hide' ? ( $hidden ? 'true' : 'false' ) : ''
		);

		$dataItem = $this->createStub( \SMWDIBlob::class );
		$dataItem->method( 'getSortKey' )->willReturn( "v-$label" );
		$dataValue = $this->createStub( \SMWDataValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $dataItem );
		$dataValue->method( 'getShortText' )->willReturn( "v-$label" );

		$field = $this->createStub( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printRequest );
		$field->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( $dataValue, false );

		return $field;
	}
}
