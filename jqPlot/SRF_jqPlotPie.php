<?php

/**
 * A query printer for pie charts using the jqPlot JavaScript library.
 *
 * @since 1.5.1
 *
 * @licence GNU GPL v3
 *
 * @author Sanyam Goyal
 * @author Yaron Koren
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SRFjqPlotPie extends SMWDistributablePrinter {
	
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

	/**
	 * Add the JS and CSS resources needed by this chart.
	 * 
	 * @since 1.7
	 */
	protected function addResources() {
		if ( self::$m_piechartnum > 1 ) {
			return;
		}

		// MW 1.17 +
		if ( class_exists( 'ResourceLoader' ) ) {
			$this->loadJavascriptAndCSS();
			return;
		}
		
		global $wgOut, $srfgScriptPath;
		global $srfgJQPlotIncluded;

		$wgOut->includeJQuery();

		if ( !$srfgJQPlotIncluded ) {
			$srfgJQPlotIncluded = true;
			$wgOut->addScript( '<!--[if IE]><script language="javascript" type="text/javascript" src="' . $srfgScriptPath . '/jqPlot/excanvas.js"></script><![endif]-->' );
			$wgOut->addScriptFile( "$srfgScriptPath/jqPlot/jquery.jqplot.js" );
		}

		$wgOut->addScriptFile( "$srfgScriptPath/jqPlot/jqplot.pieRenderer.js" );

		// CSS file
		$wgOut->addExtensionStyle( "$srfgScriptPath/jqPlot/jquery.jqplot.css" );
	}
	
	/**
	 * Get the JS and HTML that needs to be added to the output to create the chart.
	 * 
	 * @since 1.7
	 * 
	 * @param array $data label => value
	 */
	protected function getFormatOutput( array $data ) {
		$json = array();
		
		foreach ( $data as $name => $value ) {
			$json[] = array( $name, $value );
		}
		
		$pie_data_str = '[' . FormatJson::encode( $json ) . ']';
		$pieID = 'pie' . self::$m_piechartnum;
		
		self::$m_piechartnum++;

		$js_pie =<<<END
<script type="text/javascript">
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
		global $wgOut;
		$wgOut->addScript( $js_pie );

		$this->isHTML = true;
		
		return Html::element(
			'div',
			array(
				'id' => $pieID,
				'style' => Sanitizer::checkCss( "margin-top: 20px; margin-left: 20px; width: {$this->m_width}px; height: {$this->m_height}px;" )
			)
		);
	}

	/**
	 * @see SMWResultPrinter::getParameters
	 */
	public function getParameters() {
		$params = parent::getParameters();
		
		$params['height'] = new Parameter( 'height', Parameter::TYPE_INTEGER, 400 );
		$params['height']->setMessage( 'srf_paramdesc_chartheight' );

		// TODO: this is a string to allow for %, but better handling would be nice
		$params['width'] = new Parameter( 'width', Parameter::TYPE_STRING, '400' );
		$params['width']->setMessage( 'srf_paramdesc_chartwidth' );

		$params['charttitle'] = new Parameter( 'charttitle', Parameter::TYPE_STRING, ' ' );
		$params['charttitle']->setMessage( 'srf_paramdesc_charttitle' );
		
		$params['distributionlimit']->setDefault( 13 );
		
		return $params;
	}

}
