<?php

namespace SRF\Tests\Integration\Formats;

use MediaWiki\Title\Title;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SMW\Query\QueryProcessor;
use SMW\Tests\SMWIntegrationTestCase;
use SMW\Tests\Utils\UtilityFactory;
use SRF\SpreadsheetPrinter;

/**
 * Generates real spreadsheets through `format=spreadsheet` and reads them back with
 * PhpSpreadsheet, so that a change in a PhpSpreadsheet release (#1157) is caught by the
 * writers and the cell population code instead of by a failing export in production.
 *
 * @covers \SRF\SpreadsheetPrinter
 * @license GPL-2.0-or-later
 * @group semantic-result-formats
 * @group Database
 */
class SpreadsheetPrinterTest extends SMWIntegrationTestCase {

	private const TEMPLATE_FILE = 'SRFSpreadsheetTemplate.xlsx';

	/** @var string[] */
	private array $temporaryFiles = [];

	protected function setUp(): void {
		parent::setUp();

		if ( !class_exists( IOFactory::class ) ) {
			$this->markTestSkipped( 'phpoffice/phpspreadsheet is not installed.' );
		}
	}

	/**
	 * The pages are created once for all tests, the wiki and SMW tables are shared between them.
	 */
	public function addDBDataOnce(): void {
		parent::addDBDataOnce();

		$this->createProperty( 'Has SRFSpreadsheet number', 'Number' );
		$this->createProperty( 'Has SRFSpreadsheet quantity', 'Quantity', '[[Corresponds to::1 km]]' );
		$this->createProperty( 'Has SRFSpreadsheet date', 'Date' );
		$this->createProperty( 'Has SRFSpreadsheet text', 'Text' );
		$this->createProperty( 'Has SRFSpreadsheet page', 'Page' );

		// Full row: every value type, a link and a text that needs decoding
		$this->createPage(
			'SRFSpreadsheet/Full',
			'[[Has SRFSpreadsheet number::42]]'
			. '[[Has SRFSpreadsheet quantity::5 km]]'
			. '[[Has SRFSpreadsheet date::13 Feb 2021]]'
			. '[[Has SRFSpreadsheet text::Tom &amp; Jerry]]'
			. '[[Has SRFSpreadsheet page::SRFSpreadsheet/Target]]'
			. '[[Category:SRFSpreadsheet]]'
		);

		// Several dates and several linked pages are exported as one text cell (#557, #679)
		$this->createPage(
			'SRFSpreadsheet/Multiple',
			'[[Has SRFSpreadsheet date::1 Jan 2020]][[Has SRFSpreadsheet date::2 Feb 2021]]'
			. '[[Has SRFSpreadsheet page::SRFSpreadsheet/Target]][[Has SRFSpreadsheet page::SRFSpreadsheet/Other]]'
			. '[[Category:SRFSpreadsheet]]'
		);

		// Texts that a spreadsheet application would evaluate as a formula (CSV injection)
		$this->createPage(
			'SRFSpreadsheet/Formula/1',
			'[[Has SRFSpreadsheet text::=HYPERLINK("http://example.org","x")]][[Category:SRFSpreadsheetFormula]]'
		);
		$this->createPage(
			'SRFSpreadsheet/Formula/2',
			'[[Has SRFSpreadsheet text::-5 apples]][[Category:SRFSpreadsheetFormula]]'
		);

		// Row without any value
		$this->createPage( 'SRFSpreadsheet/Empty', '[[Category:SRFSpreadsheet]]' );
	}

	protected function tearDown(): void {
		foreach ( $this->temporaryFiles as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}

		parent::tearDown();
	}

	/**
	 * @dataProvider spreadsheetFormatProvider
	 */
	public function testHeaderAndCellValuesAreWrittenForReadableFormats( string $fileFormat ) {
		$sheet = $this->readSheet( $fileFormat, $this->queryAllPrintouts() );

		// Header row
		$this->assertSame(
			[ '', 'Has SRFSpreadsheet number', 'Has SRFSpreadsheet quantity', 'Has SRFSpreadsheet date',
				'Has SRFSpreadsheet text', 'Has SRFSpreadsheet page' ],
			$this->rowValues( $sheet, 1 )
		);

		// Rows are ordered by the page name: Empty, Full, Multiple
		$this->assertSame( 'SRFSpreadsheet/Full', $sheet->getCell( 'A3' )->getValue() );
		$this->assertSame( 4, $sheet->getHighestRow() );

		// Number and quantity are numeric cells, not text
		$this->assertSame( DataType::TYPE_NUMERIC, $sheet->getCell( 'B3' )->getDataType() );
		$this->assertEquals( 42, $sheet->getCell( 'B3' )->getValue() );
		$this->assertSame( DataType::TYPE_NUMERIC, $sheet->getCell( 'C3' )->getDataType() );
		$this->assertEquals( 5, $sheet->getCell( 'C3' )->getValue() );

		// A single date is a numeric date serial, not a text
		$dateCell = $sheet->getCell( 'D3' );
		$this->assertSame( DataType::TYPE_NUMERIC, $dateCell->getDataType() );
		$this->assertSame( '2021-02-13', Date::excelToDateTimeObject( $dateCell->getValue() )->format( 'Y-m-d' ) );

		// Text is decoded and kept as a string; a page value is its wiki text, not an HTML link
		$this->assertSame( DataType::TYPE_STRING, $sheet->getCell( 'E3' )->getDataType() );
		$this->assertSame( 'Tom & Jerry', $sheet->getCell( 'E3' )->getValue() );
		$this->assertSame( DataType::TYPE_STRING, $sheet->getCell( 'F3' )->getDataType() );
		$this->assertSame( 'SRFSpreadsheet/Target', $sheet->getCell( 'F3' )->getValue() );
	}

	public function testHeaderIsBoldAndQuantityAndDateCellsCarryTheirNumberFormat() {
		// Styles are only asserted for xlsx, the ods reader does not restore custom number formats
		$sheet = $this->readSheet( 'xlsx', $this->queryAllPrintouts() );

		$this->assertTrue( $sheet->getStyle( 'B1' )->getFont()->getBold() );
		$this->assertStringContainsString( 'km', $sheet->getStyle( 'C3' )->getNumberFormat()->getFormatCode() );
		$this->assertSame( 'dd/mm/yyyy', strtolower( $sheet->getStyle( 'D3' )->getNumberFormat()->getFormatCode() ) );
		$this->assertTrue( Date::isDateTime( $sheet->getCell( 'D3' ) ) );
	}

	/**
	 * @dataProvider spreadsheetFormatProvider
	 */
	public function testCellWithSeveralValuesIsWrittenAsCommaSeparatedText( string $fileFormat ) {
		$sheet = $this->readSheet( $fileFormat, $this->queryAllPrintouts() );

		$this->assertSame( 'SRFSpreadsheet/Multiple', $sheet->getCell( 'A4' )->getValue() );

		// Not a date serial: with several values the cell holds the formatted values as text
		$dates = $sheet->getCell( 'D4' );
		$this->assertSame( DataType::TYPE_STRING, $dates->getDataType() );
		$this->assertStringContainsString( '1 January 2020', $dates->getValue() );
		$this->assertStringContainsString( ', ', $dates->getValue() );
		$this->assertStringContainsString( '2 February 2021', $dates->getValue() );

		$pages = $sheet->getCell( 'F4' );
		$this->assertSame( DataType::TYPE_STRING, $pages->getDataType() );
		$this->assertEqualsCanonicalizing(
			[ 'SRFSpreadsheet/Other', 'SRFSpreadsheet/Target' ],
			explode( ', ', $pages->getValue() )
		);
	}

	/**
	 * @dataProvider spreadsheetFormatProvider
	 */
	public function testEmptyValueLeavesTheCellEmpty( string $fileFormat ) {
		$sheet = $this->readSheet( $fileFormat, $this->queryAllPrintouts() );

		$this->assertSame( 'SRFSpreadsheet/Empty', $sheet->getCell( 'A2' )->getValue() );

		foreach ( [ 'B2', 'C2', 'D2', 'E2', 'F2' ] as $coordinate ) {
			$this->assertNull( $sheet->getCell( $coordinate )->getValue(), $coordinate );
		}
	}

	public function testHeadersCanBeSwitchedOff() {
		$sheet = $this->readSheet( 'xlsx', $this->queryAllPrintouts( [ 'headers' => 'hide' ] ) );

		$this->assertSame( 'SRFSpreadsheet/Empty', $sheet->getCell( 'A1' )->getValue() );
		$this->assertSame( 3, $sheet->getHighestRow() );
	}

	public function testXlsWriterProducesAnOleDocument() {
		// The xls reader can not be relied on across PhpSpreadsheet lines for cell formats, but
		// the writer must still run and produce a document that is read back with the values
		$content = $this->export( 'xls', $this->queryAllPrintouts() );

		// OLE2 compound document signature
		$this->assertSame( "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1", substr( $content, 0, 8 ) );

		$sheet = $this->load( $content, 'xls' );
		$this->assertSame( 'SRFSpreadsheet/Full', $sheet->getCell( 'A3' )->getValue() );
		$this->assertEquals( 42, $sheet->getCell( 'B3' )->getValue() );
	}

	public function testCsvWriterWritesFormattedValues() {
		$content = $this->export( 'csv', $this->queryAllPrintouts() );

		$this->assertStringContainsString( '"Has SRFSpreadsheet number"', $content );
		$this->assertStringContainsString( '"SRFSpreadsheet/Full","42","5 km","13/02/2021","Tom & Jerry","SRFSpreadsheet/Target"', $content );
	}

	public function testDefaultFileFormatIsXlsx() {
		$query = $this->queryAllPrintouts();
		$printer = $this->newPrinter();
		$queryResult = $this->newQueryResult( $query );

		ob_start();
		$printer->outputAsFile( $queryResult, $this->processedParams( $query ) );
		$content = ob_get_clean();

		$this->assertSame( 'PK', substr( $content, 0, 2 ) );
		$this->assertSame(
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			$printer->getMimeType( $queryResult )
		);
		$this->assertStringEndsWith( '.xlsx', $printer->getFileName( $queryResult ) );
	}

	/**
	 * @dataProvider fileFormatProvider
	 */
	public function testMimeTypeAndFileNameFollowTheFileFormat( string $fileFormat, string $mimeType, string $extension ) {
		$params = $this->queryAllPrintouts( [ 'fileformat' => $fileFormat, 'filename' => 'export' ] );
		$printer = $this->newPrinter();
		$queryResult = $this->newQueryResult( $params );

		ob_start();
		$printer->outputAsFile( $queryResult, $this->processedParams( $params ) );
		ob_end_clean();

		$this->assertSame( $mimeType, $printer->getMimeType( $queryResult ) );
		$this->assertSame( 'export' . $extension, $printer->getFileName( $queryResult ) );
	}

	public function testFileNameCanNotBreakOutOfTheContentDispositionHeader() {
		$params = $this->queryAllPrintouts( [ 'filename' => "re\"port\\2021/..\r\n;x" ] );
		$printer = $this->newPrinter();
		$queryResult = $this->newQueryResult( $params );

		ob_start();
		$printer->outputAsFile( $queryResult, $this->processedParams( $params ) );
		ob_end_clean();

		$this->assertSame( 'report2021..;x.xlsx', $printer->getFileName( $queryResult ) );
	}

	public function testFileNameFallsBackToGeneratedNameWhenNothingRemains() {
		$params = $this->queryAllPrintouts( [ 'filename' => '"/\\' ] );
		$printer = $this->newPrinter();
		$queryResult = $this->newQueryResult( $params );

		ob_start();
		$printer->outputAsFile( $queryResult, $this->processedParams( $params ) );
		ob_end_clean();

		$this->assertMatchesRegularExpression( '/^[a-z0-9]+\.xlsx$/', $printer->getFileName( $queryResult ) );
	}

	/**
	 * @dataProvider spreadsheetFormatProvider
	 */
	public function testHeaderLabelIsNeverTurnedIntoAFormula( string $fileFormat ) {
		$query = $this->queryAllPrintouts();
		$query['printouts'][0] = '?Has SRFSpreadsheet number==HYPERLINK("http://example.org","x")';

		$sheet = $this->readSheet( $fileFormat, $query );

		$this->assertSame( DataType::TYPE_STRING, $sheet->getCell( 'B1' )->getDataType() );
		$this->assertSame( '=HYPERLINK("http://example.org","x")', $sheet->getCell( 'B1' )->getValue() );
		$this->assertCount( 0, $sheet->getHyperlinkCollection() );
	}

	public function testCsvPrefixesTextThatWouldBeEvaluatedAsFormula() {
		$content = $this->export( 'csv', $this->formulaQuery() );

		$this->assertStringContainsString( "\"'=HYPERLINK(\"\"http://example.org\"\",\"\"x\"\")\"\n", $content );
		$this->assertStringContainsString( "\"'-5 apples\"\n", $content );
	}

	public function testCsvPrefixesHeaderLabelThatWouldBeEvaluatedAsFormula() {
		$query = $this->formulaQuery();
		$query['printouts'] = [ '?Has SRFSpreadsheet text=@SUM(1+1)' ];

		$this->assertStringStartsWith( "\"'@SUM(1+1)\"\n", $this->export( 'csv', $query ) );
	}

	public function testCsvLeavesNumbersAndPlainTextUntouched() {
		$content = $this->export( 'csv', $this->queryAllPrintouts() );

		$this->assertStringContainsString( '"SRFSpreadsheet/Full","42","5 km","13/02/2021","Tom & Jerry"', $content );
		$this->assertStringNotContainsString( "\"'", $content );
	}

	public function testSpreadsheetFormatsKeepTheTextAsIs() {
		$sheet = $this->readSheet( 'xlsx', $this->formulaQuery() );

		$values = [ $sheet->getCell( 'A2' )->getValue(), $sheet->getCell( 'A3' )->getValue() ];
		$this->assertSame( DataType::TYPE_STRING, $sheet->getCell( 'A2' )->getDataType() );
		$this->assertContains( '=HYPERLINK("http://example.org","x")', $values );
		$this->assertContains( '-5 apples', $values );
	}

	public function testAutosizeWithSingleColumn() {
		$sheet = $this->readSheet( 'xlsx', [
			'query' => '[[Category:SRFSpreadsheet]]',
			'printouts' => [ '?Has SRFSpreadsheet number' ],
			'params' => [ 'mainlabel' => '-' ],
		] );

		$this->assertSame( 'Has SRFSpreadsheet number', $sheet->getCell( 'A1' )->getValue() );
		$this->assertEquals( 42, $sheet->getCell( 'A3' )->getValue() );
		$this->assertSame( 'A', $sheet->getHighestColumn() );
		$this->assertGreaterThan( 0, $sheet->getColumnDimension( 'A' )->getWidth() );
	}

	public function testAutosizeAdaptsEveryColumnToItsContent() {
		$sheet = $this->readSheet( 'xlsx', $this->queryAllPrintouts() );

		$this->assertSame( 'F', $sheet->getHighestColumn() );

		// The longest content per column grows from A (page name) over B and C (headers) to F
		// (two page names)
		$widths = [];
		foreach ( [ 'A', 'B', 'C', 'F' ] as $column ) {
			$widths[$column] = $sheet->getColumnDimension( $column )->getWidth();
		}

		$this->assertGreaterThan( 0, $widths['A'] );
		$this->assertGreaterThan( $widths['A'], $widths['B'] );
		$this->assertGreaterThan( $widths['B'], $widths['C'] );
		$this->assertGreaterThan( $widths['C'], $widths['F'] );
	}

	public function testTemplateFileIsUsedAndKeepsItsOwnFormatting() {
		$this->uploadTemplate();

		$sheet = $this->readSheet( 'xlsx', $this->queryAllPrintouts( [ 'templatefile' => self::TEMPLATE_FILE ] ) );

		// Content of the template outside of the result is kept
		$this->assertSame( 'from template', $sheet->getCell( 'H1' )->getValue() );

		// Results are written into the template ...
		$this->assertSame( 'SRFSpreadsheet/Full', $sheet->getCell( 'A3' )->getValue() );
		$this->assertEquals( 42, $sheet->getCell( 'B3' )->getValue() );

		// ... and a template is styled by its author, so the printer does not apply its own formats
		$this->assertSame( 'yyyy-mm-dd', $sheet->getStyle( 'D3' )->getNumberFormat()->getFormatCode() );
		$this->assertSame( '0.00', $sheet->getStyle( 'C3' )->getNumberFormat()->getFormatCode() );
	}

	public function testMissingTemplateFileFallsBackToAnEmptySpreadsheet() {
		$sheet = $this->readSheet( 'xlsx', $this->queryAllPrintouts( [ 'templatefile' => 'SRFSpreadsheetMissing.xlsx' ] ) );

		$this->assertNull( $sheet->getCell( 'H1' )->getValue() );
		$this->assertSame( 'SRFSpreadsheet/Full', $sheet->getCell( 'A3' )->getValue() );
		$this->assertSame( 'dd/mm/yyyy', strtolower( $sheet->getStyle( 'D3' )->getNumberFormat()->getFormatCode() ) );
	}

	public static function spreadsheetFormatProvider(): array {
		return [
			'xlsx' => [ 'xlsx' ],
			'ods' => [ 'ods' ],
		];
	}

	public static function fileFormatProvider(): array {
		return [
			'xlsx' => [ 'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '.xlsx' ],
			'ods' => [ 'ods', 'application/vnd.oasis.opendocument.spreadsheet', '.ods' ],
			'csv' => [ 'csv', 'text/csv', '.csv' ],
		];
	}

	private function formulaQuery(): array {
		return [
			'query' => '[[Category:SRFSpreadsheetFormula]]',
			'printouts' => [ '?Has SRFSpreadsheet text' ],
			'params' => [ 'mainlabel' => '-' ],
		];
	}

	private function queryAllPrintouts( array $params = [] ): array {
		return [
			'query' => '[[Category:SRFSpreadsheet]]',
			'printouts' => [
				'?Has SRFSpreadsheet number',
				'?Has SRFSpreadsheet quantity',
				'?Has SRFSpreadsheet date',
				'?Has SRFSpreadsheet text',
				'?Has SRFSpreadsheet page',
			],
			'params' => $params,
		];
	}

	/**
	 * Writes the result with the given file format and reads it back.
	 */
	private function readSheet( string $fileFormat, array $query ): Worksheet {
		return $this->load( $this->export( $fileFormat, $query ), $fileFormat );
	}

	private function export( string $fileFormat, array $query ): string {
		$query['params']['fileformat'] = $fileFormat;

		$printer = $this->newPrinter();

		ob_start();
		$printer->outputAsFile( $this->newQueryResult( $query ), $this->processedParams( $query ) );
		return ob_get_clean();
	}

	private function load( string $content, string $fileFormat ): Worksheet {
		$this->assertNotSame( '', $content, "The $fileFormat export is empty" );

		$path = tempnam( sys_get_temp_dir(), 'srf-spreadsheet-' );
		$this->temporaryFiles[] = $path;
		file_put_contents( $path, $content );

		return IOFactory::load( $path )->getSheet( 0 );
	}

	private function newPrinter(): SpreadsheetPrinter {
		$printer = QueryProcessor::getResultPrinter( 'spreadsheet', QueryProcessor::SPECIAL_PAGE );
		$this->assertInstanceOf( SpreadsheetPrinter::class, $printer );

		// No HTTP headers in CLI
		$printer->disableHttpHeader();

		return $printer;
	}

	private function processedParams( array $query ): array {
		return QueryProcessor::getProcessedParams(
			[ 'format' => 'spreadsheet' ] + $query['params'],
			$this->printRequests( $query )
		);
	}

	private function printRequests( array $query ): array {
		[ , $printouts ] = $this->components( $query );
		return $printouts;
	}

	private function components( array $query ): array {
		$rawParams = array_merge( [ $query['query'] ], $query['printouts'] );

		[ $queryString, , $printouts ] = QueryProcessor::getComponentsFromFunctionParams( $rawParams, false );
		QueryProcessor::addThisPrintout( $printouts, $query['params'] );

		return [ $queryString, $printouts ];
	}

	private function newQueryResult( array $query ) {
		[ $queryString, $printouts ] = $this->components( $query );
		$params = $this->processedParams( $query );

		$smwQuery = QueryProcessor::createQuery(
			$queryString,
			$params,
			QueryProcessor::SPECIAL_PAGE,
			'spreadsheet',
			$printouts
		);

		return $this->getStore()->getQueryResult( $smwQuery );
	}

	private function createProperty( string $name, string $type, string $extra = '' ): void {
		$this->createPage( $name, "[[Has type::$type]]$extra", SMW_NS_PROPERTY );
	}

	private function createPage( string $name, string $content, int $namespace = NS_MAIN ): void {
		UtilityFactory::getInstance()->newPageCreator()->createPage(
			Title::makeTitle( $namespace, $name ),
			$content
		);
	}

	/**
	 * @return array<int, mixed> values of the first six columns of a row
	 */
	private function rowValues( Worksheet $sheet, int $row ): array {
		$values = [];
		foreach ( [ 'A', 'B', 'C', 'D', 'E', 'F' ] as $column ) {
			$values[] = $sheet->getCell( $column . $row )->getValue() ?? '';
		}
		return $values;
	}

	/**
	 * Builds a small, styled template and uploads it to the wiki
	 */
	private function uploadTemplate(): void {
		$template = new Spreadsheet();
		$sheet = $template->getSheet( 0 );
		$sheet->setCellValue( 'H1', 'from template' );
		$sheet->getStyle( 'C3' )->getNumberFormat()->setFormatCode( '0.00' );
		$sheet->getStyle( 'D3' )->getNumberFormat()->setFormatCode( 'yyyy-mm-dd' );

		$path = sys_get_temp_dir() . '/' . self::TEMPLATE_FILE;
		$this->temporaryFiles[] = $path;
		IOFactory::createWriter( $template, 'Xlsx' )->save( $path );

		$file = $this->getServiceContainer()->getRepoGroup()->getLocalRepo()
			->newFile( Title::makeTitle( NS_FILE, self::TEMPLATE_FILE ) );
		$status = $file->upload( $path, 'SRF spreadsheet template', 'Template', 0, false, false, $this->getTestSysop()->getUser() );

		$this->assertTrue( $status->isOK(), $status->getWikiText() );
	}

}
