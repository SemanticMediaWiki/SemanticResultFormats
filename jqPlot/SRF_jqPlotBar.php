<?php
/**
* A query printer for bar charts using the jqPlot JavaScript library.
 *
 * @author Sanyam Goyal
 * @author Yaron Koren
 */

class SRFjqPlotBar extends SMWResultPrinter {
	
	protected static $m_barchartnum = 1;
	
	protected $m_width;
	protected $m_height;
	protected $m_charttitle;
	protected $m_barcolor;
	protected $m_bardirection;
	protected $m_numbersaxislabel;

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );
		
		$this->m_width = $this->m_params['width'];
		$this->m_height = $this->m_params['height'];
		$this->m_charttitle = $this->m_params['charttitle'];
		$this->m_barcolor = $this->m_params['barcolor'];
		$this->m_bardirection = $this->m_params['bardirection'];
		$this->m_numbersaxislabel = $this->m_params['numbersaxislabel'];
	}

	public function getName() {
		return wfMsg( 'srf_printername_jqplotbar' );
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
		$wgResourceModules['ext.srf.jqplotbar'] = $resourceTemplate + array(
			'scripts' => array(
				'jqplot.categoryAxisRenderer.js',
				'jqplot.barRenderer.js',
				'jqplot.canvasAxisTickRenderer.js',
				'jqplot.canvasTextRenderer.js',
				'excanvas.js',
			),
			'styles' => array(
			),
			'dependencies' => array(
				'ext.srf.jqplot',
			),
		);
	}

	protected static function loadJavascriptAndCSS() {
		global $wgOut;
		$wgOut->addModules( 'ext.srf.jqplot' );
		$wgOut->addModules( 'ext.srf.jqplotbar' );
	}

	static public function addJavascriptAndCSS() {
		if ( self::$m_barchartnum > 1 ) {
			return;
		}

		// MW 1.17 +
		if ( class_exists( 'ResourceLoader' ) ) {
			self::loadJavascriptAndCSS();
			return;
		}

		global $wgOut, $smwgJQueryIncluded, $srfgJQPlotIncluded;
		global $srfgScriptPath;

		$scripts = array();
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
			$wgOut->addScript( '<!--[if IE]><script language="javascript" type="text/javascript" src="' . $srfgScriptPath . '/jqPlot/excanvas.js"></script><![endif]-->' );
			$scripts[] = "$srfgScriptPath/jqPlot/jquery.jqplot.js";
			$srfgJQPlotIncluded = true;
		}

		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.categoryAxisRenderer.js";
		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.barRenderer.js";
		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.canvasAxisTickRenderer.js";
		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.canvasTextRenderer.js";

		foreach ( $scripts as $script ) {
			$wgOut->addScriptFile( $script );
		}

		// CSS file
		$wgOut->addExtensionStyle( "$srfgScriptPath/jqPlot/jquery.jqplot.css" );
	}

	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		global $wgOut;

		self::addJavascriptAndCSS();

		$this->isHTML = true;

		$numbers = array();
		$labels = array();
		
		// print all result rows
		$maxValue = 0;
		$minValue = 0;
		
		while ( $row = $res->getNext() ) {
			$name = efSRFGetNextDV( $row[0] )->getShortWikiText();
			$name = str_replace( "'", "\'", $name ); // FIXME: fail escaping is fail
			
			foreach ( $row as $field ) {
				while ( ( $object = efSRFGetNextDV( $field ) ) !== false ) {
					if ( $object->isNumeric() ) {
						// getDataItem was introduced in SMW 1.6, getValueKey was deprecated in the same version.
						if ( method_exists( $object, 'getDataItem' ) ) {
							$numbers[] = $object->getDataItem()->getSortKey();
						} else {
							$numbers[] = $object->getValueKey();
						}
						
						$labels[] = "'$name'";
					}
				}
			}
		}
		
		$maxValue = count( $numbers ) == 0 ? 0 : max( $numbers );
		$minValue = count( $numbers ) == 0 ? 0 : min( $numbers );
		
		foreach ( $numbers as $i => &$nr ) {
			$nr = $this->m_bardirection == 'horizontal' ? "[$nr, $i]" : "$nr";
		}
		
		$barID = 'bar' . self::$m_barchartnum;
		self::$m_barchartnum++;
		
		$labels_str = implode( ', ', $labels );
		$numbers_str = implode( ', ', $numbers );
		
		$labels_axis = 'xaxis';
		$numbers_axis = 'yaxis';
		
		$angle_val = -40;
		$barmargin = 6;
		
		if ( $this->m_bardirection == 'horizontal' ) {
			$labels_axis = 'yaxis';
			$numbers_axis = 'xaxis';
			$angle_val = 0;
			$barmargin = 8 ;
		}
		
		$barwidth = 20; // width of each bar
		$bardistance = 4; // distance between two bars

		// Calculate the tick values for the numbers, based on the
		// lowest and highest number. jqPlot has its own option for
		// calculating ticks automatically - "autoscale" - but it
		// currently (September 2010) fails for numbers less than 1,
		// and negative numbers.
		// If both max and min are 0, just escape now.
		if ( $maxValue == 0 && $minValue == 0 ) {
			return null;
		}
		// Make the max and min slightly larger and bigger than the
		// actual max and min, so that the bars don't directly touch
		// the top and bottom of the graph
		if ( $maxValue > 0 ) { $maxValue += .001; }
		if ( $minValue < 0 ) { $minValue -= .001; }
		if ( $maxValue == 0 ) {
			$multipleOf10 = 0;
			$maxAxis = 0;
		} else {
			$multipleOf10 = pow( 10, floor( log( $maxValue, 10 ) ) );
			$maxAxis = ceil( $maxValue / $multipleOf10 ) * $multipleOf10;
		}
		
		if ( $minValue == 0 ) {
			$negativeMultipleOf10 = 0;
			$minAxis = 0;
		} else {
			$negativeMultipleOf10 = -1 * pow( 10, floor( log( $minValue, 10 ) ) );
			$minAxis = ceil( $minValue / $negativeMultipleOf10 ) * $negativeMultipleOf10;
		}
		
		$numbers_ticks = '';
		$biggerMultipleOf10 = max( $multipleOf10, -1 * $negativeMultipleOf10 );
		$lowestTick = floor( $minAxis / $biggerMultipleOf10 + .001 );
		$highestTick = ceil( $maxAxis / $biggerMultipleOf10 - .001 );
		
		for ( $i = $lowestTick; $i <= $highestTick; $i++ ) {
			$numbers_ticks .= ($i * $biggerMultipleOf10) . ', ';
		}

		$js_bar =<<<END
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function(){
	jQuery.jqplot.config.enablePlugins = true;
	plot1 = jQuery.jqplot('$barID', [[$numbers_str]], {
		title: '{$this->m_charttitle}',
		seriesColors: ['$this->m_barcolor'],
		seriesDefaults: {
			fillToZero: true
		},
		series: [  {
			renderer: jQuery.jqplot.BarRenderer, rendererOptions: {
				barDirection: '{$this->m_bardirection}',
				barPadding: 6,
				barMargin: $barmargin
			}
		}],
		axes: {
			$labels_axis: {
				renderer: jQuery.jqplot.CategoryAxisRenderer,
				ticks: [$labels_str],
				tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
				tickOptions: {
					angle: $angle_val
				}
			},
			$numbers_axis: {
				ticks: [$numbers_ticks],
				label: '{$this->m_numbersaxislabel}'
			}
		}
	});
});
</script>
END;
		$wgOut->addScript( $js_bar );
		
		return Html::element(
			'div',
			array(
				'id' => $barID,
				'style' => Sanitizer::checkCss( "margin-top: 20px; margin-left: 20px; width: {$this->m_width}px; height: {$this->m_height}px;" )
			)
		);
	}

	public function getParameters() {
		$params = parent::getParameters();
		
		$params['height'] = new Parameter( 'height', Parameter::TYPE_INTEGER, 400 );
		$params['height']->setMessage( 'srf_paramdesc_chartheight' );
		
		$params['width'] = new Parameter( 'width', Parameter::TYPE_INTEGER, 150 );
		$params['width']->setMessage( 'srf_paramdesc_chartwidth' );
		
		$params['charttitle'] = new Parameter( 'charttitle', Parameter::TYPE_STRING, ' ' );
		$params['charttitle']->setMessage( 'srf_paramdesc_charttitle' );
		
		$params['barcolor'] = new Parameter( 'barcolor', Parameter::TYPE_STRING, '#85802b' );
		$params['barcolor']->setMessage( 'srf_paramdesc_barcolor' );
		
		$params['bardirection'] = new Parameter( 'bardirection', Parameter::TYPE_STRING, 'vertical' );
		$params['bardirection']->setMessage( 'srf_paramdesc_bardirection' );
		$params['bardirection']->addCriteria( new CriterionInArray( 'horizontal', 'vertical' ) );
		
		$params['numbersaxislabel'] = new Parameter( 'numbersaxislabel', Parameter::TYPE_STRING, ' ' );
		$params['numbersaxislabel']->setMessage( 'srf_paramdesc_barnumbersaxislabel' );
		
		return $params;
	}
	
}
