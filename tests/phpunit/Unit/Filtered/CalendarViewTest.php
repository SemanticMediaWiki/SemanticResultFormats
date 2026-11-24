<?php

namespace SRF\Tests\Filtered;

use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SRF\Filtered\Filtered;
use SRF\Filtered\ResultItem;
use SRF\Filtered\View\CalendarView;

/**
 * @covers \SRF\Filtered\View\CalendarView
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 */
class CalendarViewTest extends \PHPUnit\Framework\TestCase {

	public function testFirstDateColumnIsTheStartWhenNoParametersAreGiven() {
		$data = $this->newView( [] )->getJsDataForRow( $this->newRow() );

		$this->assertSame( '2024-05-02', $data['start'] );
		$this->assertArrayNotHasKey( 'end', $data );
	}

	public function testStartAndEndColumnsAreChosenByLabel() {
		$view = $this->newView( [ 'calendar view start' => 'Begin', 'calendar view end' => 'Ende' ] );

		$data = $view->getJsDataForRow( $this->newRow() );

		$this->assertSame( '2024-05-01', $data['start'], 'the column named by "calendar view start"' );
		$this->assertSame( '2024-05-02', $data['end'], 'the column named by "calendar view end"' );
	}

	public function testTitleColumnIsChosenByLabel() {
		$data = $this->newView( [ 'calendar view title' => 'Name' ] )->getJsDataForRow( $this->newRow() );

		$this->assertSame( 'Berlin Marathon', $data['title'] );
	}

	public function testTitleTemplateRendersTheTitle() {
		$parsed = [];
		$view = $this->newView( [ 'calendar view title template' => 'EventTitle' ], $parsed );

		$data = $view->getJsDataForRow( $this->newRow() );

		$this->assertSame( 'RENDERED', $data['title'] );
		$this->assertContains( '{{EventTitle|1=2024-05-02|2=2024-05-01|3=Berlin Marathon}}', $parsed );
	}

	/**
	 * @param array $overrides parameters of the view
	 * @param array &$parsed receives every wikitext handed to the parser
	 */
	private function newView( array $overrides, array &$parsed = [] ): CalendarView {
		$parser = $this->createStub( \Parser::class );
		$parser->method( 'recursiveTagParse' )->willReturnCallback(
			static function ( $text ) use ( &$parsed ) {
				$parsed[] = $text;

				// template calls are rendered, parameter values are returned as given
				return str_starts_with( $text, '{{' ) ? 'RENDERED' : $text;
			}
		);

		$printer = new class( null, $parser ) extends Filtered {
			public function __construct( $format, private $stubParser ) {
				parent::__construct( $format );
			}

			public function getParser() {
				return $this->stubParser;
			}

			public function getLinker( $firstcol = false, $force = false ): ?\MediaWiki\Linker\Linker {
				return null;
			}
		};

		$results = [];
		$params = $overrides + [
			'calendar view start' => '',
			'calendar view end' => '',
			'calendar view title' => '',
			'calendar view title template' => '',
		];

		return new CalendarView( $results, $params, $printer );
	}

	/**
	 * One row with the printouts Ende (date), Begin (date) and Name (text), in this order.
	 */
	private function newRow(): ResultItem {
		$row = $this->createStub( ResultItem::class );
		$row->method( 'getValue' )->willReturn( [
			$this->newDateField( 'Ende', '2024-05-02' ),
			$this->newDateField( 'Begin', '2024-05-01' ),
			$this->newTextField( 'Name', 'Berlin Marathon' ),
		] );

		return $row;
	}

	private function newField( string $label, $dataValue, string $text ): ResultArray {
		$printRequest = $this->createStub( PrintRequest::class );
		$printRequest->method( 'getLabel' )->willReturn( $label );
		$printRequest->method( 'getParameter' )->willReturn( false );

		$field = $this->createStub( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printRequest );
		$field->method( 'getNextDataValue' )->willReturn( $dataValue );
		$field->method( 'getNextText' )->willReturnOnConsecutiveCalls( $text, false );

		return $field;
	}

	private function newDateField( string $label, string $isoDate ): ResultArray {
		$value = $this->createStub( \SMWTimeValue::class );
		$value->method( 'getISO8601Date' )->willReturn( $isoDate );
		$value->method( 'getShortHTMLText' )->willReturn( $isoDate );

		return $this->newField( $label, $value, $isoDate );
	}

	private function newTextField( string $label, string $text ): ResultArray {
		$value = $this->createStub( \SMWDataValue::class );
		$value->method( 'getShortHTMLText' )->willReturn( $text );

		return $this->newField( $label, $value, $text );
	}
}
