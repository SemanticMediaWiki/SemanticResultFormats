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
class SRFjqPlotPie extends SMWAggregatablePrinter {

	/*
	 * Message name  
	 *
	 */
	public function getName() {
		return wfMsg( 'srf_printername_jqplotpie' );
	}

	/**
	 * 
	 * Handling of specified parameters 
	 * @see SMWResultPrinter::handleParameters
	 *
	 * @since 1.7.2
	 *
	 * @param array $params
	 * @param $outputmode
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );

	} // end of handleParameters ()

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
			$json[] = array( $name , $value );
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
	 * @see SMWResultPrinter::getParameters
	 */
	public function getParameters() {
		global $srfgjqPlotSettings, $srfgColorScheme;
		
		$params = parent::getParameters();

		$params['distributionlimit']->setDefault( 13 );

		$params['min'] = new Parameter( 'min', Parameter::TYPE_INTEGER );
		$params['min']->setMessage( 'srf-paramdesc-minvalue' );
		$params['min']->setDefault( false, false );

		$params['renderer'] = new Parameter( 'renderer', Parameter::TYPE_STRING, 'pie' );
		$params['renderer']->setMessage( 'srf-paramdesc-renderer' );
		$params['renderer']->addCriteria( new CriterionInArray( $srfgjqPlotSettings['pierenderer'] ) );

 		$params['height'] = new Parameter( 'height', Parameter::TYPE_INTEGER, 400 );
		$params['height']->setMessage( 'srf_paramdesc_chartheight' );

		// TODO: this is a string to allow for %, but better handling would be nice
		$params['width'] = new Parameter( 'width', Parameter::TYPE_STRING, '400' );
		$params['width']->setMessage( 'srf_paramdesc_chartwidth' );

		$params['charttitle'] = new Parameter( 'charttitle', Parameter::TYPE_STRING, '' );
		$params['charttitle']->setMessage( 'srf_paramdesc_charttitle' );

		$params['charttext'] = new Parameter( 'charttext', Parameter::TYPE_STRING, '' );
		$params['charttext']->setMessage( 'srf-paramdesc-charttext' );

		$params['valueformat'] = new Parameter( 'valueformat', Parameter::TYPE_STRING, '%d' );
		$params['valueformat']->setMessage( 'srf-paramdesc-valueformat' );

		$params['filling'] = new Parameter( 'filling', Parameter::TYPE_BOOLEAN, true );
		$params['filling']->setMessage( 'srf-paramdesc-filling' );

		$params['chartlegend'] = new Parameter( 'chartlegend', Parameter::TYPE_STRING, '' );
		$params['chartlegend']->setMessage( 'srf-paramdesc-chartlegend' );
		$params['chartlegend']->addCriteria( new CriterionInArray( 'nw','n', 'ne', 'e', 'se', 's', 'sw', 'w' ) );

		$params['datalabels'] = new Parameter( 'datalabels', Parameter::TYPE_STRING, '' );
		$params['datalabels']->setMessage( 'srf-paramdesc-datalabels' );
		$params['datalabels']->addCriteria( new CriterionInArray( 'percent','value', 'label' ) );

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
	} // end of getParameters()

}