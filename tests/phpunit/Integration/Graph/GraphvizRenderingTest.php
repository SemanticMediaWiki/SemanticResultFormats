<?php

namespace SRF\Tests\Integration\Graph;

use MediaWiki\MediaWikiServices;
use SRF\Graph\GraphFormatter;
use SRF\Graph\GraphNode;
use SRF\Graph\GraphOptions;

/**
 * Regression test for https://github.com/SemanticMediaWiki/SemanticResultFormats/issues/816
 *
 * The graph format's DOT output can be rendered by any of three extensions that all shell
 * out to the same `dot`/`neato` binaries on the same DOT source: the GraphViz extension, the
 * Diagrams extension, and External Data (in <graphviz> tag-emulation mode). GraphFormatter
 * used to pick the field-value line separator ("<br />" vs. a plain newline) based on whether
 * the Diagrams extension happened to be loaded — an unverified assumption (see the "GraphViz
 * is not working" comment introduced in 5b0df5ac) that plain GraphViz/dot couldn't handle
 * "<br />" in an HTML label. This test verifies, against the real `dot` binary and against
 * each extension's own DOT preprocessing step, that no such distinction is warranted: all
 * three paths hand the DOT source to `dot` unmodified with respect to HTML-label content.
 *
 * @license GPL-2.0-or-later
 * @group semantic-result-formats
 * @group Database
 */
class GraphvizRenderingTest extends \MediaWikiIntegrationTestCase {

	private const DOT_BINARY = '/usr/bin/dot';

	protected function setUp(): void {
		parent::setUp();
		if ( !is_executable( self::DOT_BINARY ) ) {
			$this->markTestSkipped( self::DOT_BINARY . ' is not available in this environment.' );
		}
	}

	/**
	 * Renders $dot with the real `dot` binary and returns the SVG output.
	 *
	 * @param string $dot
	 * @return string
	 */
	private function renderSvg( string $dot ): string {
		$descriptors = [
			0 => [ 'pipe', 'r' ],
			1 => [ 'pipe', 'w' ],
			2 => [ 'pipe', 'w' ],
		];
		// phpcs:ignore MediaWiki.Usage.ForbiddenFunctions.proc_open
		$process = proc_open( [ self::DOT_BINARY, '-Tsvg' ], $descriptors, $pipes );
		$this->assertIsResource( $process, 'Failed to start the dot binary.' );

		fwrite( $pipes[0], $dot );
		fclose( $pipes[0] );
		$svg = stream_get_contents( $pipes[1] );
		$stderr = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		$exitCode = proc_close( $process );

		$this->assertSame( 0, $exitCode, "dot failed to render the DOT source:\n$stderr" );
		return $svg;
	}

	/**
	 * Builds a minimal DOT HTML-label node, wrapping the given words with the given line
	 * separator inside a <td> — the same structural context GraphFormatter::buildGraph()
	 * uses for field values. Used to isolate dot's own line-break handling from the
	 * separator-selection logic under test, mirroring the pre-#816-fix Diagrams-loaded vs.
	 * Diagrams-absent cases.
	 *
	 * @param string $lineSeparator
	 * @return string
	 */
	private function buildFieldValueDot( string $lineSeparator ): string {
		$value = implode( $lineSeparator, [ 'long', 'field' ] );
		return 'digraph G { a [label=<<table><tr><td>' . $value . '</td></tr></table>>] }';
	}

	/**
	 * @covers \SRF\Graph\GraphFormatter::buildGraph()
	 *
	 * A field value wrapped with a literal "<br />" (the fixed behaviour) must render as
	 * multiple separate <text> lines in the SVG.
	 */
	public function testBrSeparatedFieldValueRendersAsMultipleTextLines(): void {
		$dot = $this->buildFieldValueDot( '<br />' );
		$svg = $this->renderSvg( $dot );

		preg_match_all( '/<text[^>]*>([^<]*)<\/text>/', $svg, $matches );
		$textLines = $matches[1];

		$this->assertContains( 'long', $textLines );
		$this->assertContains( 'field', $textLines );
		$this->assertNotContains( 'longfield', $textLines,
			'"<br />" must be rendered as a line break, not run together into one line.' );
	}

	/**
	 * @covers \SRF\Graph\GraphFormatter::buildGraph()
	 *
	 * Regression test for issue #816: a field value wrapped with a plain newline (the
	 * pre-fix behaviour when Diagrams was not loaded) is swallowed by `dot` inside an HTML
	 * label's <td> — the words run together into a single <text> line instead of wrapping.
	 * This demonstrates why the old Diagrams-dependent separator choice was actually a bug
	 * for the non-Diagrams path, not a legitimate GraphViz/Diagrams behavioural difference.
	 */
	public function testPlainNewlineSeparatedFieldValueIsSwallowedByDot(): void {
		$dot = $this->buildFieldValueDot( "\n" );
		$svg = $this->renderSvg( $dot );

		preg_match_all( '/<text[^>]*>([^<]*)<\/text>/', $svg, $matches );
		$textLines = $matches[1];

		$this->assertContains( 'longfield', $textLines,
			'A plain newline inside a DOT HTML label <td> is not a line break; words run together.' );
	}

	/**
	 * @covers \SRF\Graph\GraphFormatter::buildGraph()
	 *
	 * The fixed GraphFormatter output (which always uses "<br />" for field values,
	 * regardless of which extension will eventually render it) must render identically
	 * through the real dot binary.
	 */
	public function testFixedFormatterOutputRendersCorrectLineBreaks(): void {
		$params = [
			'graphname' => 'Issue816',
			'graphsize' => '',
			'graphfontsize' => 10,
			'nodeshape' => 'rect',
			'nodelabel' => '',
			'arrowdirection' => 'LR',
			'arrowhead' => 'diamond',
			'wordwraplimit' => 5,
			'relation' => 'parent',
			'graphlink' => false,
			'graphlabel' => false,
			'graphcolor' => false,
			'graphlegend' => false,
			'graphfields' => true,
			'graphfieldspages' => false,
		];
		// No reflection override here: this is the formatter's actual, unmodified behaviour.
		$formatter = new GraphFormatter( new GraphOptions( $params ) );
		$node = new GraphNode( 'Test:Node' );
		$node->addField( 'Description', 'A long field value here', '_txt', 'Description', null );
		$formatter->buildGraph( [ $node ] );

		$svg = $this->renderSvg( $formatter->getGraph() );

		preg_match_all( '/<text[^>]*>([^<]*)<\/text>/', $svg, $matches );
		$textLines = $matches[1];

		$this->assertContains( 'long', $textLines );
		$this->assertContains( 'field', $textLines );
	}

	/**
	 * @covers \MediaWiki\Extension\Diagrams\Dot::getSrc()
	 *
	 * Regression test for issue #816 (extension-agnosticism): the Diagrams extension's
	 * Dot::getSrc() preprocessing does not touch HTML-label body content ("<br />", <td>
	 * text) — it only rewrites the "image=" attribute and rejects specific forbidden
	 * attributes. This confirms that the DOT source GraphFormatter builds reaches the real
	 * `dot` binary unmodified with respect to line breaks under Diagrams, so the line
	 * separator must not depend on whether Diagrams is loaded.
	 *
	 * Unlike testExternalDataGraphvizTagRendersCorrectLineBreaks() below, this test only
	 * exercises Dot::getSrc() directly rather than the full <graphviz> tag callback:
	 * Diagrams::renderLocally() always writes its rendered output to a FileRepo image file
	 * (there is no config option to get raw SVG/stdout back, unlike External Data's
	 * EDConnectorExe, which returns stdout directly when no "temp" file param is given).
	 * Exercising that path would require standing up a FileRepo and reading back the
	 * generated file, which tests MediaWiki's file-repo machinery rather than the actual
	 * question here — whether "<br />" content survives preprocessing unmodified.
	 */
	public function testDiagramsPreprocessingLeavesHtmlLabelBreaksUntouched(): void {
		if ( !class_exists( '\MediaWiki\Extension\Diagrams\Dot' ) ) {
			$this->markTestSkipped( 'Diagrams extension is not installed in this environment.' );
		}

		$dot = $this->buildFieldValueDot( '<br />' );
		$dotWrapper = new \MediaWiki\Extension\Diagrams\Dot( $dot );
		$this->assertSame(
			$dot,
			$dotWrapper->getSrc(),
			'Diagrams\' Dot::getSrc() must not alter HTML-label "<br />" content.'
		);
	}

	/**
	 * Registers a minimal autoloader for the ExternalData extension's classes and seeds its
	 * $wg-prefixed config globals from extension.json's declared defaults — everything
	 * ExtensionRegistry would normally do for a real wfLoadExtension(), EXCEPT registering
	 * hooks (which would install a competing <graphviz> tag handler via Parser::setHook() —
	 * see the class docblock). This keeps External Data's own code (loadConfig(),
	 * presetGroups(), etc.) working exactly as it does when genuinely loaded.
	 *
	 * @return bool True if External Data is installed and was set up.
	 */
	private function setUpExternalDataWithoutLoadingItsHooks(): bool {
		global $IP;
		$extensionJsonPath = "$IP/extensions/ExternalData/extension.json";
		if ( !is_file( $extensionJsonPath ) ) {
			return false;
		}
		$manifest = json_decode( file_get_contents( $extensionJsonPath ), true );
		$baseDir = dirname( $extensionJsonPath );
		$classMap = $manifest['AutoloadClasses'] ?? [];
		$namespaceMap = $manifest['AutoloadNamespaces'] ?? [];

		spl_autoload_register( static function ( string $class ) use ( $baseDir, $classMap, $namespaceMap ) {
			if ( isset( $classMap[$class] ) ) {
				require_once "$baseDir/{$classMap[$class]}";
				return;
			}
			foreach ( $namespaceMap as $prefix => $relativeDir ) {
				if ( strpos( $class, $prefix ) === 0 ) {
					$relativeClass = substr( $class, strlen( $prefix ) );
					$path = "$baseDir/$relativeDir" . strtr( $relativeClass, '\\', '/' ) . '.php';
					if ( is_file( $path ) ) {
						require_once $path;
						return;
					}
				}
			}
		} );

		foreach ( $manifest['config'] ?? [] as $key => $setting ) {
			$GLOBALS["wgExternalData$key"] = $setting['value'] ?? null;
		}
		return true;
	}

	/**
	 * @covers \EDConnectorBase::emulatedTags()
	 *
	 * End-to-end smoke test for issue #816 (extension-agnosticism): configures a real
	 * "graphviz" External Data source exactly as documented (see
	 * https://www.mediawiki.org/wiki/Extension:External_Data#Emulating_the_lt;graphvizgt;
	 * -tag), registers it via EDConnectorBase::loadConfig(), wires the resulting callback
	 * onto a real Parser via setHook() exactly as ParserFirstCallInit normally would, and
	 * parses actual "<graphviz>...</graphviz>" wikitext through Parser::parse() — covering
	 * MediaWiki's own tag recognition/attribute parsing too, not just the callback in
	 * isolation. This runs the real `dot` shell command via MediaWiki\Shell\Shell and proves
	 * External Data hands the DOT source GraphFormatter builds to `dot` unmodified with
	 * respect to "<br />" line breaks in an HTML label's <td>, so the line separator must
	 * not depend on whether External Data or Diagrams is used to render the graph.
	 */
	public function testExternalDataGraphvizTagRendersCorrectLineBreaks(): void {
		if ( !$this->setUpExternalDataWithoutLoadingItsHooks() ) {
			$this->markTestSkipped( 'External Data extension is not installed in this environment.' );
		}
		if ( \MediaWiki\Shell\Shell::isDisabled() ) {
			$this->markTestSkipped( 'Shell execution is disabled in this environment.' );
		}

		$this->overrideConfigValues( [
			// Disable External Data's DB-backed URL cache: this test only needs to prove
			// the tag callback renders correctly, not exercise the cache layer, and this
			// test case has no database access.
			'ExternalDataCacheTable' => '',
			'ExternalDataSources' => [
				'graphviz' => [
					'name' => 'GraphViz',
					'program url' => 'https://graphviz.org/',
					'version command' => null,
					'command' => 'dot -K$layout$ -Tsvg',
					'params' => [ 'layout' => 'dot' ],
					'param filters' => [ 'layout' => '/^(dot|neato|twopi|circo|fdp|osage|patchwork|sfdp)$/' ],
					'input' => 'dot',
					'preprocess' => 'EDConnectorExe::wikilinks4dot',
					'postprocess' => 'EDConnectorExe::innerXML',
					'env' => [
						'HOME' => '/tmp',
						'XDG_CACHE_HOME' => '/tmp',
					],
					'min cache seconds' => 30 * 24 * 60 * 60,
					'tag' => 'graphviz',
				],
			],
			'ExternalDataAllowGetters' => true,
		] );
		\EDConnectorBase::loadConfig();

		$tags = \EDConnectorBase::emulatedTags();
		$this->assertArrayHasKey( 'graphviz', $tags,
			'EDConnectorBase::emulatedTags() must register the configured "graphviz" tag.' );

		// Wire the real callback onto a fresh Parser exactly as
		// Hooks::onParserFirstCallInit() would via $parser->setHook( $tag, $function ).
		$parser = MediaWikiServices::getInstance()->getParserFactory()->create();
		$parser->setHook( 'graphviz', $tags['graphviz'] );

		$dot = $this->buildFieldValueDot( '<br />' );
		$wikitext = "<graphviz>\n$dot\n</graphviz>";
		$title = \Title::newFromText( 'Issue816Test' );
		$parserOptions = \ParserOptions::newFromAnon();

		$parserOutput = $parser->parse( $wikitext, $title, $parserOptions );
		$html = $parserOutput->getRawText();

		$this->assertStringContainsString( '<svg', $html,
			"Parsing the real <graphviz> wikitext tag did not yield SVG output:\n$html" );
		preg_match_all( '/<text[^>]*>([^<]*)<\/text>/', $html, $matches );
		$textLines = $matches[1];
		$this->assertContains( 'long', $textLines );
		$this->assertContains( 'field', $textLines );
		$this->assertNotContains( 'longfield', $textLines,
			'"<br />" must be rendered as a line break when parsing the real <graphviz> ' .
			'wikitext tag via External Data, not run together into one line.' );
	}

}
