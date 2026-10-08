<?php

namespace SRF\Tests\Integration\Formats;

use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Parser\ParserOutputLinkTypes;
use MediaWiki\Title\Title;
use SMW\Tests\SMWIntegrationTestCase;
use SMW\Tests\Utils\UtilityFactory;

/**
 * @covers \SRF\Gallery
 *
 * @group semantic-result-formats
 * @group SRF
 * @group SMWExtension
 * @group ResultPrinters
 * @group Database
 *
 * @license GPL-2.0-or-later
 */
class GalleryParsoidTest extends SMWIntegrationTestCase {

	private const FILE = 'GalleryParsoidTest.png';
	private const CAPTION_TEMPLATE = 'GalleryParsoidTestCaption';

	public function testGalleryRendersInsteadOfAbortingTheParse(): void {
		$output = $this->renderWithParsoid(
			'{{#ask: [[File:' . self::FILE . ']] |format=gallery}}'
		);

		$this->assertStringContainsString( 'srf-gallery', $output->getRawText() );
	}

	public function testFileShownByTheGalleryIsRecordedAsUsedByThePage(): void {
		$output = $this->renderWithParsoid(
			'{{#ask: [[File:' . self::FILE . ']] |format=gallery}}'
		);

		$this->assertSame(
			[ self::FILE ],
			$this->linkedPageNames( $output->getLinkList( ParserOutputLinkTypes::MEDIA ) )
		);
	}

	public function testGalleryStyleModuleIsRecordedOnThePage(): void {
		$output = $this->renderWithParsoid(
			'{{#ask: [[File:' . self::FILE . ']] |format=gallery}}'
		);

		$this->assertContains( 'mediawiki.page.gallery.styles', $output->getModuleStyles() );
	}

	public function testCaptionTemplateIsRecordedAsUsedByThePage(): void {
		$output = $this->renderWithParsoid(
			'{{#ask: [[File:' . self::FILE . ']] |format=gallery |captiontemplate=' . self::CAPTION_TEMPLATE . '}}'
		);

		$this->assertSame(
			[ self::CAPTION_TEMPLATE ],
			$this->linkedPageNames( $output->getLinkList( ParserOutputLinkTypes::TEMPLATE ) )
		);
	}

	private function renderWithParsoid( string $query ): ParserOutput {
		$this->createPage( Title::makeTitle( NS_FILE, self::FILE ) );
		$this->createPage(
			Title::makeTitle( NS_TEMPLATE, self::CAPTION_TEMPLATE ),
			'{{{imagecaption|}}}'
		);

		$title = Title::makeTitle( NS_MAIN, 'GalleryParsoidTest' );
		$this->createPage( $title, $query );

		// Creating the pages above parsed them, which leaves the main parser instance started for
		// the rest of the process. A request that renders through Parsoid does not start it, and
		// the gallery must not depend on it being started.
		$services = $this->getServiceContainer();
		$services->resetServiceForTesting( 'ParserFactory' );
		$services->resetServiceForTesting( 'Parser' );

		$page = $services->getPageStore()->getPageByReference( $title );

		$options = ParserOptions::newFromAnon();
		$options->setUseParsoid();

		$status = $services->getParserOutputAccess()->getParserOutput( $page, $options );
		$this->assertStatusGood( $status );

		$output = $status->getValue();

		// Without this the whole test file silently passes on the legacy parser, where the gallery
		// is given the parser rendering the page no matter which one it asks for.
		$this->assertContains( 'mediawiki.skinning.content.parsoid', $output->getModuleStyles() );

		return $output;
	}

	private function createPage( Title $title, string $content = '' ): void {
		UtilityFactory::getInstance()->newPageCreator()->createPage( $title, $content );
	}

	/**
	 * @param list<array{link:\Wikimedia\Parsoid\Core\LinkTarget|\MediaWiki\Linker\LinkTarget}> $links
	 * @return string[]
	 */
	private function linkedPageNames( array $links ): array {
		return array_map(
			static fn ( array $link ): string => $link['link']->getDBkey(),
			$links
		);
	}

}
