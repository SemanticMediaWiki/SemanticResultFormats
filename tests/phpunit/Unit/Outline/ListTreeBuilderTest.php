<?php

namespace SRF\Tests\Outline;

use SMW\Query\PrintRequest;
use SMW\Query\Result\ResultArray;
use SMWDataItem;
use SMWDataValue;
use SMWWikiPageValue;
use SRF\Outline\ListTreeBuilder;
use SRF\Outline\OutlineItem;
use SRF\Outline\OutlineTree;

/**
 * @covers \SRF\Outline\ListTreeBuilder
 * @group semantic-result-formats
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class ListTreeBuilderTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ListTreeBuilder::class,
			new ListTreeBuilder( [] )
		);
	}

	public function testBuildForEmptyTree() {
		$params = [
			'outlineproperties' => [ 'Foo' ]
		];

		$instance = new ListTreeBuilder( $params );

		$this->assertIsString(
			$instance->build( new OutlineTree() )
		);
	}

	public function testBuildWithItemIncludesValueInOutput() {
		$params = [
			'outlineproperties' => [ 'OutlineProp' ],
			'link' => 'none',
			'showHeaders' => false,
		];

		$dataItem = $this->createMock( SMWDataItem::class );
		$dataItem->method( 'getDIType' )->willReturn( SMWDataItem::TYPE_BLOB );

		$dataValue = $this->createMock( SMWDataValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $dataItem );
		$dataValue->method( 'getShortText' )->willReturn( 'TestValue' );

		$printRequest = $this->createMock( PrintRequest::class );
		$printRequest->method( 'getText' )->willReturn( 'SomeProperty' );
		$printRequest->method( 'getLabel' )->willReturn( 'SomeProperty' );
		$printRequest->method( 'isMode' )->willReturn( false );

		$resultArray = $this->createMock( ResultArray::class );
		$resultArray->method( 'getPrintRequest' )->willReturn( $printRequest );
		$resultArray->method( 'reset' )->willReturn( null );
		$resultArray->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( $dataValue, false );

		$item = new OutlineItem( [ $resultArray ] );
		$tree = new OutlineTree( [ $item ] );

		$instance = new ListTreeBuilder( $params );
		$output = $instance->build( $tree );

		$this->assertStringContainsString( 'TestValue', $output );
	}

	public function testBuildWithWikiPageItemUsesDisplayTitleAsCaption() {
		$params = [
			'outlineproperties' => [ 'OutlineProp' ],
			'link' => 'none',
			'showHeaders' => false,
		];

		$dataItem = $this->createMock( SMWDataItem::class );
		$dataItem->method( 'getDIType' )->willReturn( SMWDataItem::TYPE_WIKIPAGE );

		$dataValue = $this->createMock( SMWWikiPageValue::class );
		$dataValue->method( 'getDataItem' )->willReturn( $dataItem );
		$dataValue->method( 'getDisplayTitle' )->willReturn( 'My Display Title' );
		$dataValue->expects( $this->once() )->method( 'setCaption' )->with( 'My Display Title' );
		$dataValue->method( 'getShortText' )->willReturn( 'My Display Title' );

		$printRequest = $this->createMock( PrintRequest::class );
		$printRequest->method( 'getText' )->willReturn( 'SomeProperty' );
		$printRequest->method( 'getLabel' )->willReturn( 'SomeProperty' );
		$printRequest->method( 'isMode' )->willReturn( false );

		$resultArray = $this->createMock( ResultArray::class );
		$resultArray->method( 'getPrintRequest' )->willReturn( $printRequest );
		$resultArray->method( 'reset' )->willReturn( null );
		$resultArray->method( 'getNextDataValue' )->willReturnOnConsecutiveCalls( $dataValue, false );

		$item = new OutlineItem( [ $resultArray ] );
		$tree = new OutlineTree( [ $item ] );

		$instance = new ListTreeBuilder( $params );
		$instance->build( $tree );
	}

}
