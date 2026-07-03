<?php

namespace SRF\Tests\Outline;

use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SMWDataValue;
use SMWWikiPageValue;
use SRF\Outline\OutlineItem;
use SRF\Outline\OutlineTree;
use SRF\Outline\TemplateBuilder;

/**
 * @covers \SRF\Outline\TemplateBuilder
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class TemplateBuilderTest extends \PHPUnit\Framework\TestCase {

	private array $baseParams = [
		'outlineproperties' => [ 'Foo' ],
		'template' => 'Bar',
		'userparam' => '',
		'introtemplate' => '',
		'outrotemplate' => '',
		'link' => 'none',
	];

	public function testCanConstruct() {
		$this->assertInstanceOf(
			TemplateBuilder::class,
			new TemplateBuilder( [] )
		);
	}

	public function testBuildForEmptyTree() {
		$params = array_merge( $this->baseParams, [
			'introtemplate' => 'Intro',
			'outrotemplate' => 'Outro',
		] );

		$instance = new TemplateBuilder( $params );

		$this->assertIsString(
			$instance->build( new OutlineTree() )
		);
	}

	public function testBuildWithItemIncludesTemplateCallInOutput() {
		$dataValue = $this->createMock( SMWDataValue::class );
		$dataValue->method( 'getShortText' )->willReturn( 'PageTitle' );

		$printRequest = $this->createMock( PrintRequest::class );
		$printRequest->method( 'getText' )->willReturn( 'NonOutlineProp' );
		$printRequest->method( 'getLabel' )->willReturn( 'NonOutlineProp' );
		$printRequest->method( 'isMode' )->with( PrintRequest::PRINT_THIS )->willReturn( false );

		$resultArray = $this->createMock( ResultArray::class );
		$resultArray->method( 'getPrintRequest' )->willReturn( $printRequest );
		$resultArray->method( 'reset' )->willReturn( null );
		$resultArray->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( $dataValue, false );

		$item = new OutlineItem( [ $resultArray ] );
		$tree = new OutlineTree( [ $item ] );

		$instance = new TemplateBuilder( $this->baseParams );
		$output = $instance->build( $tree );

		$this->assertStringContainsString( '{{Bar-item', $output );
	}

	public function testBuildWithSubjectItemUsesDisplayTitleAsCaption() {
		$dataValue = $this->createMock( SMWWikiPageValue::class );
		$dataValue->method( 'getShortText' )->willReturn( 'My Display Title' );
		$dataValue->method( 'getDisplayTitle' )->willReturn( 'My Display Title' );
		$dataValue->expects( $this->once() )->method( 'setCaption' )->with( 'My Display Title' );

		$printRequest = $this->createMock( PrintRequest::class );
		$printRequest->method( 'getText' )->willReturn( 'NonOutlineProp' );
		$printRequest->method( 'getLabel' )->willReturn( 'NonOutlineProp' );
		$printRequest->method( 'isMode' )->with( PrintRequest::PRINT_THIS )->willReturn( true );

		$resultArray = $this->createMock( ResultArray::class );
		$resultArray->method( 'getPrintRequest' )->willReturn( $printRequest );
		$resultArray->method( 'reset' )->willReturn( null );
		$resultArray->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( $dataValue, false );

		$item = new OutlineItem( [ $resultArray ] );
		$tree = new OutlineTree( [ $item ] );

		$instance = new TemplateBuilder( $this->baseParams );
		$instance->build( $tree );
	}

}
