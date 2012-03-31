<?php

/**
 * A query printer for bar / line charts using the jqPlot JavaScript library.
 *
 * @since 1.8
 *
 * @file SRF_jqPlotBar.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v3
 *
 * @author Sanyam Goyal
 * @author Yaron Koren
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author mwjames 
 */
class SRFjqPlotBar extends SMWAggregatablePrinter {
	
	/**
	 * Corresponding message name  
	 *
	 */ 
	public function getName() {
		return wfMsg( 'srf_printername_jqplotbar' );
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::handleParameters()
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );
		
	} // end of handleParameters()

	/**
	 * Prepare aggregated data output
	 * 
	 * @since 1.8
	 * 
	 * @param array $data label => value
	 */
	protected function getFormatOutput( array $data ) {
		static $statNr = 0;
		$result = '';
		$numbers_ticks = '';

		$this->isHTML = true;

		if ( !class_exists( 'ResourceLoader' ) ) {
			return '<span class="error">' . wfMsgForContent( 'srf-error-resourceloader' ) . '</span>';
		}

		// Find min and max values to determine the graphs axis parameter
		$maxValue = count( $data ) == 0 ? 0 : max( $data );

		if ( $this->params['min'] === false ) {
			$minValue = count( $data ) == 0 ? 0 : min( $data );
		} else {
			$minValue = $this->params['min'];
		}
		// Calculate the tick values for the numbers, based on the
		// lowest and highest number. jqPlot has its own option for
		// calculating ticks automatically - "autoscale" - but it
		// currently (September 2010, it also fails with the jpLot 1.00b 2012) 
		// fails for numbers less than 1, and negative numbers.
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
		
		$biggerMultipleOf10 = max( $multipleOf10, -1 * $negativeMultipleOf10 );
		$lowestTick = floor( $minAxis / $biggerMultipleOf10 + .001 );
		$highestTick = ceil( $maxAxis / $biggerMultipleOf10 - .001 );
		
		for ( $i = $lowestTick; $i <= $highestTick; $i++ ) {
			$dataObject['numbersticks'][] = ($i * $biggerMultipleOf10) ; 
		}

		 // Wrap everything in a div tag so it does not interfere with other objects
		 // of the page
		 
		// <span srf-jqplotbar-bottom > contains a possible bottom text
		// <div $barchartID > and class = srf-jqplotbar contains the actual jqplot graph	
		// <div jqplot> includes all above elements so it can be handled as one object 		 	  
		$barchartID = 'jqplotbar' . ++$statNr;
		$bottomText = '';

		// Some minor align adjustment 
		$bottomTextLeft = ( $this->params['ticklabels'] == false || $this->params['renderer'] == 'donut' ? 8 : 0 ); 		
		$bottomText = '';
		
		// Bottom text 
		if ( !empty( $this->params['charttext'] ) ){
			// Calculation basis to ensure the text and graph will keep in the limts of the set height
			$chartHeight 	= $this->params['height'] - ( 30 + ( strlen( $this->params['charttext'] ) / 8 ) );
			$chartWidth 	= ( strpos( $this->params['width'], '%' ) ? $this->params['width'] : $this->params['width'] - 10 );	

			$divAttrs = array( 'class' => 'srf-jqplotbar-bottom', 'style' => "color:grey; margin-left:{$bottomTextLeft}px; margin-right:20px; display:block; position:relative; font-size:90%;" );
			$bottomText = Xml::tags( 'span', $divAttrs, Sanitizer::removeHTMLtags ( $this->params['charttext'] ) );
		} else {
			$chartHeight 	= $this->params['height'] - 10;
			$chartWidth 	= ( strpos( $this->params['width'], '%' ) ? $this->params['width'] : $this->params['width'] - 10 );	
		}
		
		// Actual chart object		 
		$divAttrs = array( 'id' => $barchartID, 'class' => "srf-jqplotbar" , 'style' => Sanitizer::checkCss( " margin-bottom:2px; width: {$chartWidth}px; height: {$chartHeight}px;" ) );	
		$result .= Xml::tags( 'div', $divAttrs, '' );
		$result .= $bottomText;
		
		// Collect everything and set display:none to avoid any display clutter
		if ( !empty( $this->params['chartclass'] ) ){		
			$divAttrs = array( 'id' => 'jqplot', 'class' => Sanitizer::checkCss( "{$this->params['chartclass']}" ), 'style' => Sanitizer::checkCss( "display:none; margin-bottom:10px; width: {$this->params['width']}px; height: {$this->params['height']}px;" ) );	
		} else {
			$divAttrs = array( 'id' => 'jqplot', 'style' => Sanitizer::checkCss( "display:none; margin-bottom:10px; margin-top: 10px; margin-left: 10px; width: {$this->params['width']}px; height: {$this->params['height']}px;" ) );			
		}
		$result = Xml::tags( 'div', $divAttrs, $result );
		
		// Data encoding 
		// This way we keep it similar to the jqplotseries data structure
		$dataObject['series'] = $data; 
		
		$requireHeadItem = array ( $barchartID => FormatJson::encode( $this->prepareDataSet( $dataObject ) ) ); 
		SMWOutputs::requireHeadItem( $barchartID, Skin::makeVariablesScript($requireHeadItem ) );

		// Initialization of the JS object  
		if ( $this->params['pointlabels'] == true || $this->params['highlighter'] == true ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.bar.extended' );				
		} else {
			// Two issues are interfere with the jqplot trendline feature 
			// As alpha feature it works but we do not advertise it 
			//if ( $this->params['trendline'] == true ) {
			//}else{
			//	SMWOutputs::requireResource( 'ext.srf.jqplot.bar.trendline' );				
			//}	
			SMWOutputs::requireResource( 'ext.srf.jqplot.bar' );		
		}
		return $result;	
	} // end of getFormatOutput()

	/**
	 * Plug-in data and parameters
	 *
	 * All relevant data and options are transferred to mw.config.set/mw.config.get
	 * names generally correspond to the jqPlot names 
	 *
	 * @since 1.8
	 * 
	 * @param array $data label => value
	 */
	private function prepareDataSet( $data ) {

		// Swap the data 
		if ( $this->params['bardirection'] == 'horizontal' ) {
			$a = 1;
			foreach ( $data['series'] as $i => &$nr ) {
				// $nr = array( $nr, $i );
				// jPlot needs a 1D [val1, val2, ...], or 2D [[val, label], [val, label], ...]
				$nr = array( $nr , $a++ );
			}
		}	

		// Set jqPlot parameter options 
		$parameters = array (
			'charttitle' => $this->params['charttitle'],	
			'numbersaxislabel' => $this->params['numbersaxislabel'],
			'theme'	=> ( $this->params['theme'] ? $this->params['theme'] : null ),
			'valueformat'	=> $this->params['valueformat'],
			'ticklabels'	=> $this->params['ticklabels'],
			'highlighter'	=> $this->params['highlighter'],
			'bardirection' => $this->params['bardirection'],
			'smoothlines'	=> $this->params['smoothlines'],
			'colorscheme'	=> (!empty( $this->params['colorscheme'] ) ? $this->params['colorscheme'] : null ),
			'pointlabels'	=> $this->params['pointlabels'],
			'grid' => ( $this->params['theme'] == 'vector' ? array ( 'borderColor' => '#a7d7f9'	) : ( $this->params['theme'] == 'mono' ? array ( 'borderColor' => '#ddd'	) : null ) ),   
			'seriescolors' => (!empty( $this->params['chartcolor'] ) ? explode( ",", $this->params['chartcolor'] ) : null )
		);
		
		// Data can be an array of y values, or an array of [label, value] pairs; 
		// While labels are used only on the first series with labels on 
		// subsequent series being ignored
		return array (
			'data'	=>  array ( array_values ( $data['series'] ) ),  // need an extra array here
			'ticks' => $data['numbersticks'],	
			'labels' => array_keys 	( $data['series'] ),
			'numbers' => array_values ( $data['series'] ),
			'parameters'=> $parameters,
			'mode' => 'single',
			'series' => array(), // is empty but is needed for series mode
			//'rawdata'	=> $data,   // control array
			'renderer' => $this->params['renderer']
		);		
	} // end of prepareDataSet()

	/**
	 * @see SMWResultPrinter::getParameters
	 */
	public function getParameters() {
		global $srfgColorScheme, $srfgjqPlotSettings;
		
		$params = parent::getParameters();

		$params['min'] = new Parameter( 'min', Parameter::TYPE_INTEGER );
		$params['min']->setMessage( 'srf-paramdesc-minvalue' );
		$params['min']->setDefault( false, false );

		$params['charttitle'] = new Parameter( 'charttitle', Parameter::TYPE_STRING, ' ' );
		$params['charttitle']->setMessage( 'srf_paramdesc_charttitle' );

		$params['charttext'] = new Parameter( 'charttext', Parameter::TYPE_STRING, '' );
		$params['charttext']->setMessage( 'srf-paramdesc-charttext' );

		$params['numbersaxislabel'] = new Parameter( 'numbersaxislabel', Parameter::TYPE_STRING, ' ' );
		$params['numbersaxislabel']->setMessage( 'srf_paramdesc_barnumbersaxislabel' );

		$params['renderer'] = new Parameter( 'renderer', Parameter::TYPE_STRING, 'bar' );
		$params['renderer']->setMessage( 'srf-paramdesc-renderer' );
		$params['renderer']->addCriteria( new CriterionInArray( $srfgjqPlotSettings['barrenderer'] ) );

		$params['bardirection'] = new Parameter( 'bardirection', Parameter::TYPE_STRING, 'vertical' );
		$params['bardirection']->setMessage( 'srf_paramdesc_bardirection' );
		$params['bardirection']->addCriteria( new CriterionInArray( 'horizontal', 'vertical' ) );
	
		$params['smoothlines'] = new Parameter( 'smoothlines', Parameter::TYPE_BOOLEAN, false );
		$params['smoothlines']->setMessage( 'srf-paramdesc-smoothlines' );

		$params['height'] = new Parameter( 'height', Parameter::TYPE_INTEGER, 400 );
		$params['height']->setMessage( 'srf_paramdesc_chartheight' );
		
		// TODO: this is a string to allow for %, but better handling would be nice
		$params['width'] = new Parameter( 'width', Parameter::TYPE_STRING, '100%' );
		$params['width']->setMessage( 'srf_paramdesc_chartwidth' );

		$params['valueformat'] = new Parameter( 'valueformat', Parameter::TYPE_STRING, '%d' );
		$params['valueformat']->setMessage( 'srf-paramdesc-valueformat' );
		// %.2f round number to 2 digits after decimal point e.g.  EUR %.2f, $ %.2f  
		// %d a signed integer, in decimal
						
		$params['pointlabels'] = new Parameter( 'pointlabels', Parameter::TYPE_STRING, '' );
		$params['pointlabels']->setMessage( 'srf-paramdesc-pointlabels' );
		$params['pointlabels']->addCriteria( new CriterionInArray( 'value', 'label' ) );
		
		$params['ticklabels'] = new Parameter( 'ticklabels', Parameter::TYPE_BOOLEAN, true );
		$params['ticklabels']->setMessage( 'srf-paramdesc-datalabels' );

		$params['highlighter'] = new Parameter( 'highlighter', Parameter::TYPE_BOOLEAN, false );
		$params['highlighter']->setMessage( 'srf-paramdesc-highlighter' );

		$params['theme'] = new Parameter( 'theme', Parameter::TYPE_STRING, '' );
		$params['theme']->setMessage( 'srf-paramdesc-theme' );
		$params['theme']->addCriteria( new CriterionInArray( 'vector', 'mono') );
		
		$params['colorscheme'] = new Parameter( 'colorscheme', Parameter::TYPE_STRING, '' );
		$params['colorscheme']->setMessage( 'srf-paramdesc-colorscheme' );
		$params['colorscheme']->addCriteria( new CriterionInArray( $srfgColorScheme ) );

		$params['chartcolor'] = new Parameter( 'chartcolor', Parameter::TYPE_STRING, '' );
		$params['chartcolor']->setMessage( 'srf-paramdesc-chartcolor' );

		$params['chartclass'] = new Parameter( 'chartclass', Parameter::TYPE_STRING );
		$params['chartclass']->setMessage( 'srf-paramdesc-chartclass' );
		$params['chartclass']->setDefault( '' );

		return $params;	
	} // enf of getParameters()	
} // end of class