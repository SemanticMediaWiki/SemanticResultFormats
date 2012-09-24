<?php

/**
 * Static class for hooks handled by the Semantic Result Formats.
 *
 * @since 1.7
 * 
 * @file
 * @ingroup SemanticResultFormats
 * 
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
final class SRFHooks {

	/**
	 * Hook to add PHPUnit test cases.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/UnitTestsList
	 *
	 * @since 1.8
	 *
	 * @param array $files
	 *
	 * @return boolean
	 */
	public static function registerUnitTests ( array &$files ) {
		$testFiles = array(
			'formats/Array',
			'formats/Gallery',
			'formats/Math',
			'formats/TagCloud',
			'formats/vCard',
			'formats/Sparkline',
			'formats/jqPlotChart',
			'formats/jqPlotSeries',
			'formats/ListWidget',
			'formats/PageWidget',
			'formats/Timeseries',
			'formats/Feed',
			'formats/EventCalendar',
			'formats/Dygraphs'
		);

		foreach ( $testFiles as $file ) {
			$files[] = dirname( __FILE__ ) . '/tests/phpunit/' . $file . 'Test.php';
		}

		return true;
	}

	/**
	 * Adds a link to Admin Links page.
	 *
	 * @since 1.7
	 *
	 * @param ALTree $admin_links_tree
	 *
	 * @return boolean
	 */
	public static function addToAdminLinks( ALTree &$admin_links_tree ) {
		$displaying_data_section = $admin_links_tree->getSection( wfMessage( 'smw_adminlinks_displayingdata' )->text() );
		
		// Escape is SMW hasn't added links.
		if ( is_null( $displaying_data_section ) ) {
			return true;
		}
			
		$smw_docu_row = $displaying_data_section->getRow( 'smw' );
		$srf_docu_label = wfMessage( 'adminlinks_documentation', wfMessage( 'srf-name' )->text() )->text();
		$smw_docu_row->addItem( AlItem::newFromExternalLink( 'https://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats', $srf_docu_label ) );
		
		return true;
	}
}
