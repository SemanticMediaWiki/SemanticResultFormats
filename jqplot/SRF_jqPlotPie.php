<?php

/**
 * A query printer for pie charts using the jqPlot JavaScript library.
 *
 * @since 1.8
 *
 * @file SRF_jqPlotBar.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v3
 *
 * @author mwjames 
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Sanyam Goyal
 * @author Yaron Koren
 */
class SRFjqPlotPie extends SRFjqPlot {

	/*
	 * Message name  
	 *
	 */
	public function getName() {
		return wfMsg( 'srf_printername_jqplotpie' );
	}

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
		$this->isHTML = true;
		
		if ( !class_exists( 'ResourceLoader' ) ) {
			return '<span class="error">' . wfMsgForContent( 'srf-error-resourceloader' ) . '</span>';
		}

		 // Wrap everything in a div tag so it does not interfere with other objects
		 // of the page
		 
		// <span srf-jqplotpie-bottom > contains a possible bottom text
		// <div $barchartID > and class = srf-jqplotbar contains the actual jqplot graph	
		// <div jqplot> includes all above elements so it can be handled as one object 		 	  
		$piechartID = 'jqplotpie' . ++$statNr;
		$bottomText = '';
		
		if ( !empty( $this->params['charttext'] ) ){
			// Calculation basis to ensure that the text and graph will keep in the limts of the set height
			// If you do change these seetings please ensure text, graph alignment is tested with/without text/class 
			$chartHeight 	= $this->params['height'] - ( 20 + ( strlen( $this->params['charttext'] ) / 10 ) );
			$chartWidth 	= ( strpos( $this->params['width'], '%' ) ? $this->params['width'] : $this->params['width'] - 10 );	

			$divAttrs = array( 'class' => 'srf-jqplotpie-bottom', 'style' => "color:grey; top: -10px; margin-left:10px; margin-right:20px; display:block; position:relative; font-size:90%;" );
			$bottomText = Xml::tags( 'span', $divAttrs, Sanitizer::removeHTMLtags ( $this->params['charttext'] ) );
		} else {
			$chartHeight 	= $this->params['height'] - 10;
			$chartWidth 	= ( strpos( $this->params['width'], '%' ) ? $this->params['width'] : $this->params['width'] - 10 );	
		}
				 
		$divAttrs = array( 'id' => $piechartID, 'class' => "srf-jqplotpie" , 'style' => Sanitizer::checkCss( "width: {$chartWidth}px; height: {$chartHeight}px;" ) );	
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
		$json = array();
		
		foreach ( $data as $name => $value ) {
			if ( $value > $this->params['min'] ) {
				$json[] = array( $name , $value );
			}
		}

		$requireHeadItem = array ( $piechartID => FormatJson::encode( $this->prepareDataSet( $json ) ) ); 
		SMWOutputs::requireHeadItem( $piechartID, Skin::makeVariablesScript($requireHeadItem ) );

		// Initialize JS object either for the pie or the donut format
		if ( $this->params['renderer'] == 'donut' ) {
			SMWOutputs::requireResource( 'ext.srf.jqplot.donut' );				
		} else {
			SMWOutputs::requireResource( 'ext.srf.jqplot.pie' );		
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
	 *
	 * @return array
	 */
	private function prepareDataSet( $data ) {

		$parameters = array (
			'charttitle' => $this->params['charttitle'],	
			'theme' => $this->params['theme'],
			'filling' => $this->params['filling'],
			'datalabels' => $this->params['datalabels'],
			'valueformat' => $this->params['valueformat'],
			'chartlegend' => $this->params['chartlegend'],
			'colorscheme' => (!empty( $this->params['colorscheme'] ) ? $this->params['colorscheme'] : null ),
			'grid' => ( $this->params['theme'] == 'vector' ? array ( 'borderColor' => '#a7d7f9'	) : ( $this->params['theme'] == 'mono' ? array ( 'borderColor' => '#ddd'	) : null ) ),   
			'seriescolors' => (!empty( $this->params['chartcolor'] ) ? explode(",", $this->params['chartcolor'] ) : null )
		);

		return array (
			'data' => array($data), // Additional wrapping array	
			'renderer' => $this->params['renderer'],
			'mode' => 'single', // for now this hard coded 
			'parameters' => $parameters
		);
	} // end of prepareDataSet();

	/**
	 * @see SMWResultPrinter::getParamDefinitions
	 *
	 * @since 1.8
	 *
	 * @param $definitions array of IParamDefinition
	 *
	 * @return array of IParamDefinition|array
	 */
	public function getParamDefinitions( array $definitions ) {
		$params = parent::getParamDefinitions( $definitions );

		$params['distributionlimit']['default'] = 13;

		$params['chartlegend'] = array(
			'name' => 'chartlegend',
			'message' => 'srf-paramdesc-chartlegend',
			'values' => array( 'nw','n', 'ne', 'e', 'se', 's', 'sw', 'w' ),
			'default' => '',
		);

		return $params;
	}

}