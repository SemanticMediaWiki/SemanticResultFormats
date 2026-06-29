<?php

namespace SRF\Tests\vCard;

use MediaWiki\Title\Title;
use ReflectionClass;
use SMW\DataItems\WikiPage as SMWWikiPage;
use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SMWDataValue;
use SRF\Tests\ResultPrinterReflector;
use SRF\vCard\Address;
use SRF\vCard\vCard;
use SRF\vCard\vCardFileExportPrinter;

/**
 * @covers \SRF\vCard\vCardFileExportPrinter
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class vCardFileExportPrinterTest extends \PHPUnit\Framework\TestCase {

	private $queryResult;
	private $resultPrinterReflector;

	protected function setUp(): void {
		parent::setUp();

		$this->resultPrinterReflector = new ResultPrinterReflector();

		$this->queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
			->disableOriginalConstructor()
			->getMock();
	}

	private function callMapField( vCardFileExportPrinter $printer, $field, vCard $vCard, array &$tels, array &$addresses, array &$emails ): void {
		$ref = new ReflectionClass( $printer );
		$method = $ref->getMethod( 'mapField' );
		$method->setAccessible( true );
		$args = [ $field, $vCard, &$tels, &$addresses, &$emails ];
		$method->invokeArgs( $printer, $args );
	}

	private function newVCardStub(): vCard {
		return new vCard( 'http://example.org', 'Test', [] );
	}

	private function newAddresses(): array {
		return [
			'work' => new Address( 'WORK' ),
			'home' => new Address( 'HOME' ),
		];
	}

	private function newField( string $label, array $returnValues ): ResultArray {
		$dataValueReturns = array_merge( $returnValues, [ false ] );

		$printRequest = $this->createMock( PrintRequest::class );
		$printRequest->method( 'getLabel' )->willReturn( $label );
		$printRequest->method( 'getTypeID' )->willReturn( '' );

		$field = $this->createMock( ResultArray::class );
		$field->method( 'getPrintRequest' )->willReturn( $printRequest );
		$field->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( ...$dataValueReturns );

		return $field;
	}

	private function newDataValue( string $shortWikiText ): SMWDataValue {
		$dv = $this->createMock( SMWDataValue::class );
		$dv->method( 'getShortWikiText' )->willReturn( $shortWikiText );
		return $dv;
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			vCardFileExportPrinter::class,
			new vCardFileExportPrinter( 'vcard' )
		);
	}

	/**
	 * @dataProvider filenameProvider
	 */
	public function testGetFileName( $filename, $searchlabel, $expected ) {
		$parameters = [
			'filename' => $filename,
			'searchlabel' => $searchlabel
		];

		$instance = new vCardFileExportPrinter(
			'vcard'
		);

		$this->resultPrinterReflector->addParameters( $instance, $parameters );

		$this->assertEquals(
			$expected,
			$instance->getFileName( $this->queryResult )
		);
	}

	public function testGetMimeType() {
		$instance = new vCardFileExportPrinter(
			'vcard'
		);

		$this->assertEquals(
			'text/x-vcard',
			$instance->getMimeType( $this->queryResult )
		);
	}

	public function testGetResult_LinkOnNonFileOutput() {
		$link = $this->createMock( \SMWInfolink::class );

		$link->expects( $this->any() )
			->method( 'getText' )
			->willReturn( 'foo_link' );

		$this->queryResult->expects( $this->any() )
			->method( 'getErrors' )
			->willReturn( [] );

		$this->queryResult->expects( $this->any() )
			->method( 'getCount' )
			->willReturn( 1 );

		$this->queryResult->expects( $this->once() )
			->method( 'getQueryLink' )
			->willReturn( $link );

		$instance = new vCardFileExportPrinter(
			'vcard'
		);

		$this->assertEquals(
			'foo_link',
			$instance->getResult( $this->queryResult, [], SMW_OUTPUT_HTML )
		);
	}

	public function filenameProvider() {
		yield [
			'',
			'foo bar',
			'foo_bar.vcf'
		];

		yield [
			'foo',
			'',
			'foo.vcf'
		];

		yield [
			'foo.vcf',
			'',
			'foo.vcf'
		];

		yield [
			'foo bar.vcf',
			'',
			'foo_bar.vcf'
		];
	}

	public function testMapField_EmailWhileLoop_CollectsMultipleValues() {
		$printer = new vCardFileExportPrinter( 'vcard' );
		$vCard = $this->newVCardStub();
		$tels = [];
		$addresses = $this->newAddresses();
		$emails = [];

		$field = $this->newField( 'email', [
			$this->newDataValue( 'alice@example.org' ),
			$this->newDataValue( 'bob@example.org' ),
		] );

		$this->callMapField( $printer, $field, $vCard, $tels, $addresses, $emails );

		$this->assertCount( 2, $emails );
	}

	public function testMapField_WorkStreet_SetsAddressWhenNonEmpty() {
		$printer = new vCardFileExportPrinter( 'vcard' );
		$vCard = $this->newVCardStub();
		$tels = [];
		$addresses = $this->newAddresses();
		$emails = [];

		$field = $this->newField( 'workstreet', [ $this->newDataValue( '123 Main St' ) ] );

		$this->callMapField( $printer, $field, $vCard, $tels, $addresses, $emails );

		$this->assertStringContainsString( '123 Main St', $addresses['work']->text() );
	}

	public function testMapField_WorkStreet_DoesNotSetAddressWhenEmpty() {
		$printer = new vCardFileExportPrinter( 'vcard' );
		$vCard = $this->newVCardStub();
		$tels = [];
		$addresses = $this->newAddresses();
		$emails = [];

		$field = $this->newField( 'workstreet', [ $this->newDataValue( '' ) ] );

		$this->callMapField( $printer, $field, $vCard, $tels, $addresses, $emails );

		$this->assertStringNotContainsString( 'street', $addresses['work']->text() );
	}

	public function testGetResultText_FileOutput_ProducesVCardForOneRow() {
		$title = $this->createMock( Title::class );
		$title->method( 'getFullURL' )->willReturn( 'http://example.org/Test' );
		$title->method( 'getText' )->willReturn( 'Test Person' );

		$subject = $this->createMock( SMWWikiPage::class );
		$subject->method( 'getTitle' )->willReturn( $title );

		$subjectField = $this->createMock( ResultArray::class );
		$subjectField->method( 'getResultSubject' )->willReturn( $subject );
		$printRequest = $this->createMock( PrintRequest::class );
		$printRequest->method( 'getLabel' )->willReturn( '' );
		$subjectField->method( 'getPrintRequest' )->willReturn( $printRequest );
		$subjectField->method( 'getNextDataValue' )->willReturn( false );

		$queryResult = $this->getMockBuilder( '\SMW\Query\QueryResult' )
			->disableOriginalConstructor()
			->getMock();
		$queryResult->method( 'getNext' )->willReturnOnConsecutiveCalls( [ $subjectField ], false );

		$printer = new class ( 'vcard' ) extends vCardFileExportPrinter {
			protected function getPageTimestamp( $title ): string {
				return '20240101000000';
			}
		};

		$reflector = new ResultPrinterReflector();
		$output = $reflector->invoke( $printer, $queryResult, SMW_OUTPUT_FILE );

		$this->assertStringContainsString( 'BEGIN:VCARD', $output );
		$this->assertStringContainsString( 'Test Person', $output );
	}

}
