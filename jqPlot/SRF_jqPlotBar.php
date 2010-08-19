<?php
/**
* A query printer for bar charts using the jqPlot JavaScript library.
 *
 * @author Sanyam Goyal
 * @author Yaron Koren
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

class SRFjqPlotBar extends SMWResultPrinter {
	protected $m_width = '150';
	protected $m_height = '400';
	protected $m_charttitle = ' ';
	protected $m_barcolor = '#85802b' ;
	protected $m_bardirection = 'vertical';
	protected $m_numbersaxislabel = ' ';
	static protected $m_barchartnum = 1;

	protected function readParameters( $params, $outputmode ) {
		SMWResultPrinter::readParameters( $params, $outputmode );
		if ( array_key_exists( 'width', $this->m_params ) ) {
			$this->m_width = $this->m_params['width'];
		}
		if ( array_key_exists( 'height', $this->m_params ) ) {
			$this->m_height = $this->m_params['height'];
		}
		if ( array_key_exists( 'charttitle', $this->m_params ) ) {
		      $this->m_charttitle = $this->m_params['charttitle'];
		}
		if ( array_key_exists( 'barcolor', $this->m_params ) ) {
		      $this->m_barcolor = $this->m_params['barcolor'];
		}
		if ( array_key_exists( 'bardirection', $this->m_params ) ) {
			// keep it simple - only 'horizontal' makes sense as
			// an alternate value
			if ( $this->m_params['bardirection'] == 'horizontal' ) {
				$this->m_bardirection = $this->m_params['bardirection'];
			}
		}
		else{
		    $this->m_bardirection = 'vertical';
		}
		if ( array_key_exists( 'numbersaxislabel', $this->m_params ) ) {
		      $this->m_numbersaxislabel = $this->m_params['numbersaxislabel'];
		}
	}

	public function getName() {
		return wfMsg( 'srf_printername_jqplotbar' );
	}

	protected function getResultText( $res, $outputmode ) {
		global $smwgIQRunningNumber, $wgOut, $srfgScriptPath;
		global $srfgJQPlotIncluded, $smwgJQueryIncluded;
		global $wgParser;
		$wgParser->disableCache();

		//adding scripts - this code may be moved to some other location
		$scripts = array();
		if ( !$smwgJQueryIncluded ) {
			if ( method_exists( 'OutputPage', 'includeJQuery' ) ) {
				$wgOut->includeJQuery();
			} else {
				$scripts[] = "$srfgScriptPath/jqPlot/jquery-1.4.2.min.js";
			}
			$smwgJQueryIncluded = true;
		}

		if ( !$srfgJQPlotIncluded ) {
			$scripts[] = "$srfgScriptPath/jqPlot/jquery.jqplot.min.js";
			$srfgJQPlotIncluded = true;
		}

		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.categoryAxisRenderer.min.js";
		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.barRenderer.min.js";
		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.canvasAxisTickRenderer.min.js";
		$scripts[] = "$srfgScriptPath/jqPlot/jqplot.canvasTextRenderer.min.js";

		foreach ( $scripts as $script ) {
			$wgOut->addScript( '<script type="text/javascript" src="' . $script . '"></script>' );
		}

		// CSS file
	       $bar_css = array(
				'rel' => 'stylesheet',
				'type' => 'text/css',
				'media' => "screen",
				'href' => $srfgScriptPath . '/jqPlot/jquery.jqplot.css'
			);
		$wgOut->addLink( $bar_css );
		$this->isHTML = true;

		$numbers = array();
		$labels = array();
		// print all result rows
		$count = 0;
		while ( $row = $res->getNext() ) {
			$name = $row[0]->getNextObject()->getShortWikiText();
			foreach ( $row as $field ) {
					while ( ( $object = $field->getNextObject() ) !== false ) {
					if ( $object->isNumeric() ) { // use numeric sortkey
						if ( method_exists( $object, 'getValueKey' ) ) {
							$nr = $object->getValueKey();
						} else {
							$nr = $object->getNumericValue();
						}
						$count++;
						
						if ( $this->m_bardirection == 'horizontal' ) {
							$numbers[] = "[$nr, $count]";
						} else {
							$numbers[] = "$nr";
						}
						$labels[] = "'$name'";
					}
				}
			}
		}
		$barID = 'bar' . self::$m_barchartnum;
		self::$m_barchartnum++;
		
		$labels_str = implode( ', ', $labels );
		$numbers_str = implode( ', ', $numbers );
		$labels_axis ="xaxis";
		$numbers_axis = "yaxis";
		$angle_val = -40;
		$barmargin= 30;
		if ( $this->m_bardirection == 'horizontal' ) {
			$labels_axis ="yaxis";
			$numbers_axis ="xaxis";
			$angle_val = 0;
			$barmargin = 8 ;
		}
		$barwidth = 20; // width of each bar
		$bardistance = 4; // distance between two bars
		$js_bar =<<<END
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function(){
	jQuery.jqplot.config.enablePlugins = true;
	plot1 = jQuery.jqplot('$barID', [[$numbers_str]], {
		title: '{$this->m_charttitle}',
		seriesColors: ['$this->m_barcolor'],
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
				autoscale: true,
				label: '{$this->m_numbersaxislabel}'
			}
		}
	});
});
</script>
 
END;
		$wgOut->addScript($js_bar);
		$text =<<<END
<div id="$barID" style="margin-top: 20px; margin-left: 20px; width: {$this->m_width}px; height: {$this->m_height}px;"></div>

END;
		return $text;
	}

	public function getParameters() {
		return array(
			array( 'name' => 'limit', 'type' => 'int', 'description' => wfMsg( 'smw_paramdesc_limit' ) ),
			array( 'name' => 'height', 'type' => 'int', 'description' => wfMsg( 'srf_paramdesc_chartheight' ) ),
			array( 'name' => 'charttitle', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_charttitle' ) ),
			array( 'name' => 'barcolor', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_barcolor' ) ),
			array( 'name' => 'bardirection', 'type' => 'enumeration', 'description' => wfMsg( 'srf_paramdesc_bardirection' ),'values' => array('horizontal', 'vertical')),
			array( 'name' => 'numbersaxislabel', 'type' => 'string', 'description' => wfMsg( 'srf_paramdesc_barnumbersaxislabel' ) ),
			array( 'name' => 'width', 'type' => 'int', 'description' => wfMsg( 'srf_paramdesc_chartwidth' ) ),
		);
	}
}
