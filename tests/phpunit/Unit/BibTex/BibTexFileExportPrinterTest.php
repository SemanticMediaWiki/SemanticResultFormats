<?php

namespace SRF\Tests\BibTex;

use SRF\BibTex\BibTexFileExportPrinter;

/**
 * @covers \SRF\BibTex\BibTexFileExportPrinter
 * @group semantic-result-formats
 *
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class BibTexFileExportPrinterTest extends \PHPUnit_Framework_TestCase {

	private $queryResult;
	private $resultPrinterReflector;

	protected function setUp() {
		parent::setUp();

		$this->queryResult = $this->getMockBuilder( '\SMWQueryResult' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function addParameters( $instance, array $parameters ) {

		$reflector = new \ReflectionClass( $instance );
		$params = $reflector->getProperty( 'params' );
		$params->setAccessible( true );
		$params->setValue( $instance, $parameters );

		if ( isset( $parameters['searchlabel'] ) ) {
			$searchlabel = $reflector->getProperty( 'mSearchlabel' );
			$searchlabel->setAccessible( true );
			$searchlabel->setValue( $instance, $parameters['searchlabel'] );
		}

		if ( isset( $parameters['headers'] ) ) {
			$searchlabel = $reflector->getProperty( 'mShowHeaders' );
			$searchlabel->setAccessible( true );
			$searchlabel->setValue( $instance, $parameters['headers'] );
		}

		return $instance;
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			BibTexFileExportPrinter::class,
			new BibTexFileExportPrinter( 'bibtex' )
		);
	}

	public function testGetFileName() {

		$parameters = [
			'filename' => 'foo'
		];

		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->addParameters( $instance, $parameters );

		$this->assertEquals(
			'foo.bib',
			$instance->getFileName( $this->queryResult )
		);
	}

	public function testGetMimeType() {

		$instance = new BibTexFileExportPrinter(
			'bibtex'
		);

		$this->assertEquals(
			'text/bibtex',
			$instance->getMimeType( $this->queryResult )
		);
	}

}
