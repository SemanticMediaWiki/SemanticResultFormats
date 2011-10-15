<?php
/**
 * A query printer for pie charts using the jqPlot JavaScript library.
 *
 * @author Sanyam Goyal
 * @author Yaron Koren
 */

class SRFjqPlotPie extends SMWResultPrinter {
	
	protected static $m_piechartnum = 1;
	
	protected $m_width;
	protected $m_height;
	protected $m_charttitle;

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );
		
		$this->m_width = $this->m_params['width'];
		$this->m_height = $this->m_params['height'];
		$this->m_charttitle = $this->m_params['charttitle'];
	}

	public function getName() {
		return wfMsg( 'srf_printername_jqplotpie' );
	}

	public static function registerResourceModules() {
		global $wgResourceModules, $srfgIP;

		$resourceTemplate = array(
			'localBasePath' => $srfgIP . '/jqPlot',
			'remoteExtPath' => 'SemanticResultFormats/jqPlot'
		);
		$wgResourceModules['ext.srf.jqplot'] = $resourceTemplate + array(
			'scripts' => array(
				'jquery.jqplot.js',
			),
			'styles' => array(
				'jquery.jqplot.css',
			),
			'dependencies' => array(
			),
		);
		$wgResourceModules['ext.srf.jqplotpie'] = $resourceTemplate + array(
			'scripts' => array(
				'jqplot.pieRenderer.js',
				'excanvas.js',
			),
			'styles' => array(
			),
			'dependencies' => array(
				'ext.srf.jqplot',
			),
		);
	}

	protected function loadJavascriptAndCSS() {
		global $wgOut;
		$wgOut->addModules( 'ext.srf.jqplot' );
		$wgOut->addModules( 'ext.srf.jqplotpie' );
	}

	protected function addJavascriptAndCSS() {
		if ( self::$m_piechartnum > 1 ) {
			return;
		}

		// MW 1.17 +
		if ( class_exists( 'ResourceLoader' ) ) {
			$this->loadJavascriptAndCSS();
			return;
		}
		global $wgOut, $srfgScriptPath;
		global $smwgJQueryIncluded, $srfgJQPlotIncluded;

		if ( !$smwgJQueryIncluded ) {
			$realFunction = array( $wgOut, 'includeJQuery' );
			if ( is_callable( $realFunction ) ) {
				$wgOut->includeJQuery();
			} else {
				$scripts[] = "$srfgScriptPath/jqPlot/jquery-1.4.2.min.js";
			}
			$smwgJQueryIncluded = true;
		}

		if ( !$srfgJQPlotIncluded ) {
			$srfgJQPlotIncluded = true;
			$wgOut->addScript( '<!--[if IE]><script language="javascript" type="text/javascript" src="' . $srfgScriptPath . '/jqPlot/excanvas.js"></script><![endif]-->' );
			$wgOut->addScriptFile( "$srfgScriptPath/jqPlot/jquery.jqplot.js" );
		}

		$wgOut->addScriptFile( "$srfgScriptPath/jqPlot/jqplot.pieRenderer.js" );

		// CSS file
		$wgOut->addExtensionStyle( "$srfgScriptPath/jqPlot/jquery.jqplot.css" );
	}

	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		global $wgOut;

		$this->addJavascriptAndCSS();

		$this->isHTML = true;

		$t = "";
		$pie_data = array();
		// print all result rows
		while ( $row = $res->getNext() ) {
			$name = efSRFGetNextDV( $row[0] )->getShortWikiText();
			$name = str_replace( "'", "\'", $name );
			
			foreach ( $row as $field ) {
				while ( ( $object = efSRFGetNextDV( $field ) ) !== false ) {
					if ( $object->isNumeric() ) { // use numeric sortkey
						
						// getDataItem was introduced in SMW 1.6, getValueKey was deprecated in the same version.
						if ( method_exists( $object, 'getDataItem' ) ) {
							$nr = $object->getDataItem()->getSortKey();
						} else {
							$nr = $object->getValueKey();
						}
						
						$pie_data[] .= "['$name', $nr]";
					}
				}
			}
		}
		
		$pie_data_str = "[[" . implode( ', ', $pie_data ) . "]]";
		$pieID = 'pie' . self::$m_piechartnum;
		
		self::$m_piechartnum++;

		$js_pie =<<<END
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function(){
	jQuery.jqplot.config.enablePlugins = true;
	plot1 = jQuery.jqplot('$pieID', $pie_data_str, {
		title: '$this->m_charttitle',
		seriesDefaults: {
			renderer: jQuery.jqplot.PieRenderer,
			rendererOptions: {
				sliceMargin:2
			}
		},
			legend: { show:true }
	});
});
</script>
END;
		$wgOut->addScript( $js_pie );

		return Html::element(
			'div',
			array(
				'id' => $pieID,
				'style' => Sanitizer::checkCss( "margin-top: 20px; margin-left: 20px; width: {$this->m_width}px; height: {$this->m_height}px;" )
			)
		);
	}

	public function getParameters() {
		$params = parent::getParameters();
		
		$params['height'] = new Parameter( 'height', Parameter::TYPE_INTEGER, 400 );
		$params['height']->setMessage( 'srf_paramdesc_chartheight' );

		$params['width'] = new Parameter( 'width', Parameter::TYPE_INTEGER, 400 );
		$params['width']->setMessage( 'srf_paramdesc_chartwidth' );

		$params['charttitle'] = new Parameter( 'charttitle', Parameter::TYPE_STRING, ' ' );
		$params['charttitle']->setMessage( 'srf_paramdesc_charttitle' );
		
		return $params;
	}

}
