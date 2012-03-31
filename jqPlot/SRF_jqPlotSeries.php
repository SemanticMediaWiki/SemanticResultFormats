<?php

/**
 * A query printer for charts series using the jqPlot JavaScript library.
 *
 * @since 1.8
 *  
 * @file SRF_jqPlotSeries.php
 * @ingroup SemanticResultFormats
 * @licence GNU GPL v3
 *
 * @author mwjames 
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SRFjqPlotSeries extends SMWResultPrinter {
	
	/**
	 * Coressponding message name  
	 * @see SMWResultPrinter::getName
	 */ 
	public function getName() {
		return wfMsg( 'srf_printername_jqplotseries' );
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::handleParameters
	 */
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );
		
	}

	/**
	 * (non-PHPdoc)
	 * @see SMWResultPrinter::getResult
	 */
	public function getResult( SMWQueryResult $results, array $params, $outputmode ) {
		$this->handleParameters( $params, $outputmode );

		if ( !class_exists( 'ResourceLoader' ) ) {
			return '<span class="error">' . wfMsgForContent( 'srf-error-resourceloader' ) . '</span>';
		}

		if ( ( count( $results ) == 0 ) ) {
			return '<span class="error">' . wfMsgForContent( 'srf-warn-empy-chart' ) . '</span>';
		}
	
		if ( empty( $params['renderer'] ) ) {
			return '<span class="error">' . wfMsgForContent( 'srf-error-missing-renderer' ) . '</span>';
		}
		
		return $this->getResultText( $results, SMW_OUTPUT_HTML );
	}

	/**
	 * Builds data sets from query to be used as chart series
	 * 
	 * @since 1.8
	 * 
	 * @param SMWQueryResult $res
	 * @param $outputmode
	 * 
	 * @return array
	 */
	 protected function getResultText( SMWQueryResult $res, $outputmode ) {
		static $statNr = 0;
		$result = '';
		$numbers_ticks = '';
		$this->isHTML = true;

		// The data array is assumed to have at least one number per label 
		// A multi-dimensional array is split into an array series where the 
		// series array is again grouped by an associative array using the 
		// property column index
		 
		// In order to simplify logic we store the same data in different groupings 
		// makes processing easier at a later point  
		 
		// Data can either be grouped along the row or along column and is 
		// independent from the direction of the graph  
		// $data['series'] contains numerical data for row grouping (default) = page 
		// $data['column'] contains identical data only pre grouped as column data =  property
		
		while ( $row = $res->getNext() ) {
			// Loop over their fields (properties).
			$propertyObjects = array();
	
			foreach ( $row as /* SMWResultArray */ $field ) {
				  
				// Indicator for associative array
				$propertyIndex = $field->getPrintRequest()->getLabel();

				// Loop over all values for the property.
				while ( ( /* SMWDataValue */ $object = $field->getNextDataValue() ) !== false ) {
							
					if ( $object->getDataItem()->getDIType() == SMWDataItem::TYPE_NUMBER ) {
						$number =  $object->getNumber();

						// Divide numbers per series in associative array
						$data['series'][$propertyIndex][] = $number;

						// Summarize all values per row 
						$propertyObjects[] = $number;
					}else{
						$data['label'][] = Sanitizer::decodeCharReferences( $object->getWikiValue() );
					} // end if ()
				} // end while ()
			} // end foreach ()
			// Stores all elements that belong to row as member of a column
			$data['column'][] = $propertyObjects;
		} // end of while ()

		// Their is a better way of doing this but for now ... feel free
		// Any way in cases where you have a label but now further values the matrix 
		// don't add up and in order to preserve the integrity do check 
		// the following array to ensure we don't any empty entries  
		foreach ( $data['column'] as $key => $row ){
		
		 // Find the array which has no actual data which means count = 0
		 // $key points to the array entry and if so delete the label entry 
		 // since we know that $data['series'] don't have any entry for this 
		 // specific key
		 if ( ( count($row) == 0 ) ){
			unset( $data['label'][$key] );
			// unset( $data['column'][$key] );
		 } 
		}

		// In case someone tries to generates a graph without a text or data
		if ( empty( $data['label'] ) || empty( $data['series'] )  ) {
			return '<span class="error">' . wfMsgForContent( 'srf-error-series-data' ) . '</span>';
		}
				
		// Only look for numeric values that have been stored
		$dataSeries = array_values($data['series']);	
		
		// Find min and max values to determine the graphs axis parameter
		//$maxValue = count( $dataSeries ) == 0 ? 0 : max( max( $dataSeries ) );
		$maxValue = count( $dataSeries ) == 0 ? 0 : max( array_map("max", $dataSeries) ) ;

		if ( $this->params['min'] === false ) {
			// $minValue = count( $dataSeries ) == 0 ? 0 : min( min( $dataSeries ) );
			$minValue = count( $dataSeries ) == 0 ? 0 : min( array_map("min", $dataSeries) ) ;
		} else {
			$minValue = $this->params['min'];
		}
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
		
		$biggerMultipleOf10 = max( $multipleOf10, -1 * $negativeMultipleOf10 );
		$lowestTick = floor( $minAxis / $biggerMultipleOf10 + .001 );
		$highestTick = ceil( $maxAxis / $biggerMultipleOf10 - .001 );
		
		for ( $i = $lowestTick; $i <= $highestTick; $i++ ) {
			// Store values in the data array 
			$data['numbersticks'][] = ($i * $biggerMultipleOf10) ; 
		}

		// DIV object
		// <div> tag around existing $result and wrap it into an 
		// individual jqplot div so it does not interfere with other objects
		// <span> contains a possible bottom text
		// <div> id = $chartID; class = srf-jqplotbar contains the actual jqplot graph	
		// <div> id = jqplot; includes above elements so all elements can be dealt with at once 		 	  
		$chartID 	= 'jqplotbarseries' . ++$statNr;
    if ( $this->params['renderer'] == 'donut' ){
    	$chartClass	= 'srf-jqplotpie';
		} else {
	    if ( $this->params['renderer'] == 'line' || $this->params['renderer'] == 'bar' ){
	    	$chartClass	= 'srf-jqplotbar';
			} else {
		    $chartClass	= 'srf-jqplot' . $this->params['renderer'];
			}
		}
		
		// Smaller align adjustments 
		$bottomTextLeft = ( $this->params['ticklabels'] == false || $this->params['renderer'] == 'donut' ? 8 : 0 ); 
		$bottomTextTop = ( $this->params['renderer'] == 'donut' ? -20 : 0 );  	
		$bottomText = '';
		
		// Wrap the bottom text if exists
		if ( !empty( $this->params['charttext'] ) ){
			$chartHeight 	= $this->params['height'] - ( 30 + ( strlen( $this->params['charttext'] ) / 8 ) );
			$chartWidth 	= ( strpos( $this->params['width'], '%' ) ? $this->params['width'] : $this->params['width'] - 10 );	

			$divAttrs = array( 'class' => 'srf-jqplotbar-bottom', 'style' => "color:grey; margin-left:{$bottomTextLeft}px; top:{$bottomTextTop}px; margin-right:20px; display:block; position:relative; font-size:90%;" );
			$bottomText = Xml::tags( 'span', $divAttrs, Sanitizer::removeHTMLtags ( $this->params['charttext'] )  );
		} else {
			$chartHeight 	= $this->params['height'] - 10;
			$chartWidth 	= ( strpos( $this->params['width'], '%' ) ? $this->params['width'] : $this->params['width'] - 10 );	
		}
		
		// Wrap the jplot object 		 
		$divAttrs = array( 'id' => $chartID, 'class' => $chartClass , 'style' => Sanitizer::checkCss( " margin-bottom:2px; width: {$chartWidth}px; height: {$chartHeight}px;" ) );	
		$result .= Xml::tags( 'div', $divAttrs, '' );

		$result .= $bottomText;
		
		// In case of a class don't interfere with any margin otherwhise go with the standard settings 
		if ( !empty( $this->params['chartclass'] ) ){		
			$divAttrs = array( 'id' => 'jqplot', 'class' => Sanitizer::checkCss( "{$this->params['chartclass']}" ), 'style' => Sanitizer::checkCss( "display:none; margin-bottom:10px; width: {$this->params['width']}px; height: {$this->params['height']}px;" ) );	
		} else {
			$divAttrs = array( 'id' => 'jqplot', 'style' => Sanitizer::checkCss( "display:none; margin-bottom:10px; margin-top: 10px; margin-left: 10px; width: {$this->params['width']}px; height: {$this->params['height']}px;" ) );			
		}
		$result = Xml::tags( 'div', $divAttrs, $result );
		
		// Data encoding
		$requireHeadItem = array ( $chartID => FormatJson::encode( $this->prepareDataSet( $data ) )  ); 
		SMWOutputs::requireHeadItem( $chartID, Skin::makeVariablesScript($requireHeadItem ) );

		// Initialization of the JS object  
		if ( $this->params['renderer'] == 'bubble' ){
			SMWOutputs::requireResource( 'ext.srf.jqplot.bubble' );
		}else{
			if ( $this->params['renderer'] == 'donut' ){
				SMWOutputs::requireResource( 'ext.srf.jqplot.donut' );
			}else{
				if ( $this->params['pointlabels'] == true || $this->params['highlighter'] == true ) {
					SMWOutputs::requireResource( 'ext.srf.jqplot.bar.extended' );				
				}else{
					SMWOutputs::requireResource( 'ext.srf.jqplot.bar' );		
				}; // end if		
			}; // end if
		}; // end if
				 		 		
		return $result;	
	} // end of getResultText()

	/**
	 * Plug-in data and parameter conversion
	 *
	 * All relevant data and options are transferred to mw.config.set/mw.config.get
	 * names generally correspond to the jqPlot names 
	 * 	 
	 * Styling of jqPlot is done using jquery.jqplot.css but some objects  
	 * (line color, grid background) are the exception in order to control 
	 * attributes of the renderered canvas
	 *
	 * @since 1.8
	 * 
	 * @param array $data label => value
	 *
	 */
	private function prepareDataSet( $data ) {
		$grid = array ();
		$seriesColors = array ();
		$legendLabels = array();

		// Data grouping either as row or column, data are an array of y values, 
		// or as an array of [label, value] pairs, labels are used only on the 
		// first series with labels on subsequent series being ignored

		if ( $this->params['seriesgroup'] == 'label' ) {
			// Trying to sort a series over a stored label
			// Experimental feature depending on the data model where numerical 
			// values ( subobjects etc.) make either sense or not 
			//
			// This feature makes less sense when having more than one numerical property
			// while sorting over a text label since its values are summarized 
			// under a specific label which can lead to confusion about the data 
			// source
			//
			// The axis labels are are either a simple number index or values 
			// a re fetched from $this->params['serieslabel']
			//
			// This feature can only be used with 1n-array; a count index is used as 
			// access criteria
			// # If more than one subject with the same ID is stored on the same page the 
			// index will break
			
			$i = 0;
			$countLabel = 0;
			
			// Go through existing labels we know  that the index of the labels array 
			// corresponds to the array index of the data stored in the columns array 
			foreach ( $data['label'] as $label ) {
				
				// Go through the index column and fetch the values assigned 
				foreach ( $data['column'][$i++] as $val ) { 
					$data['sort'][$label][] = $val; 
				}

				// Count the source array and create an index number
			  $tempCount = ( empty( $data['sort'][$label] ) ? 0 : count ( $data['sort'][$label] ) ); 
			  $countLabel = ( $tempCount > $countLabel ? $tempCount : $countLabel );
			}
			
			// Swap the original series with the sorted in order to be able to 
			// use all succeeding steps
			if ( $this->params['serieslabel'] ){
				$serieslabel = explode(",", $this->params['serieslabel'] );
				$data['label'] =  array_slice($serieslabel, 0, $countLabel); ;
			} else {
				$data['label'] = array_fill ( 1 , $countLabel , 0 );
			}
			$data['series'] = $data['sort'];
		}

		// Column grouping 
		if ( $this->params['seriesgroup'] == 'column' ) {
			// $seriesCategory = array_keys($data['series']);
	 		$seriesCategory = array_values($data['label']);
	 		$labelsticks = array_keys($data['series']);

			if ( $this->params['renderer'] == 'donut' ){
				$legendLabels = array_keys( $data['series'] );			
			} else {
				foreach ( $data['label'] as $row ) {
					$legendLabels[] = $row ;	
				}
			}
			
			$plabels = $labelsticks;
			// Strip the data array into jqplot array format
			$a=1;
			foreach ( $data['column'] as $row ) {
				if ( !empty( $row )){
					$dataseries[] =  $row  ;	
				}  
			}
			foreach ( $data['series'] as $row ) {
			//	  $labelsticks[] = $row ;
			}

		} else {
			if ( $this->params['renderer'] == 'donut' ){
					foreach ( $data['label'] as $row ) {
						$legendLabels[] = $row ;	
					}
			} else {
				$legendLabels = array_keys($data['series']);			
			}

			// Identify the data elements for 
			// $legendLabels = array_keys($data['series']);
			$seriesCategory = array_keys($data['series']);
	
			// Strip the data array into jqplot array format
			foreach ( $data['series'] as $row ) {
				$dataseries[] = $row ;
			}
			foreach ( $data['label'] as $row ) {
				$labelsticks[] = $row ;
			}
			$plabels = $labelsticks;
		}; // end if column / row grouping

		// Flip horizontal / vertical view 
		// Horizontal bar charts, x an y values must be "flipped" from 
		// their vertical counterpart.
		 
		// Series [  [,] ...     ]
	 	// vertical : [[92.6, 7, 0], [74.6, 24, 1.4] ... ]
	 	// horizontal: [[[92.6, PO] [7, PO] [0, PO]] ... ]
	 	// One could try to assign $data['label'][$a++] as index labels but 
	 	// it doesn't work, so we use the normal numerical index
		if ( $this->params['bardirection'] == 'horizontal' ) {
			foreach ( $dataseries as $fliprow ) {
				$a=1; // position on the graph, 0 isn't valid
				$flipset = array();
				foreach ( $fliprow  as $i => $nr ) {
					$flipset[] = array ($nr , $a++ ); 
				} // end of foreach ()  
					$data['horizontal'][] = $flipset;
			} // end of foreach()
			$dataseries = $data['horizontal'];
		}; // end if ()

		// Donut display manipulation 
		// The data array needs a specific structure otherwise labels 
		// are not displayed is also needs a revers conversion in case of
		// horizontal direction		 
		if ( $this->params['renderer'] == 'donut' ){
			foreach ( $dataseries as $row) {
				$i=0;
				$donutArray = array();
				
				foreach ( $row as $key => $value) {
					if ( $this->params['bardirection'] == 'vertical' ){
						$donutArray[] = array ( $labelsticks[$i++], $value );
					}else{
						// Fetch the value from a sub-array that was needed for 
						// line/bar horizontal view which is not needed for the donut chart
						// we know the value is stored in $value[0]
						$donutArray[] = array ( $labelsticks[$i++], $value[0] );			 
					}
				}
			$donutseries[] = $donutArray;
			}
			unset ( $dataseries ); // clear the array before the copy
			$dataseries = $donutseries; 
		}	 		 		

		// Set plug-in series parameter array for line / bar chart 
		$i=0;
		$series = array();
		$numbers = '';
		if ( $this->params['renderer'] == 'bar' || $this->params['renderer'] == 'line' ){
			foreach ( $seriesCategory as $row ) {
				$series[] = array ('label' => $row ,
				'xaxis' => 'xaxis',
				'yaxis' => 'yaxis',
				'fill' => ( $this->params['stackseries'] == true ? true : false ),			
				'rendererOptions' => array (
					'barDirection' => $this->params['bardirection'] )
				);
			};
		};

		$parameters = array (
			'charttitle' => $this->params['charttitle'],	
			'theme' => ( $this->params['theme'] ? $this->params['theme'] : null ),
			'valueformat' => ( $this->params['pointlabels'] == 'label' ? '' : $this->params['valueformat'] ),
			'ticklabels' => $this->params['ticklabels'],
			'highlighter' => $this->params['highlighter'],
			'autoscale' => false,
			'bardirection' => $this->params['bardirection'],
			'smoothlines' => $this->params['smoothlines'],
			'chartlegend' => $this->params['chartlegend'],
			'colorscheme' => (!empty( $this->params['colorscheme'] ) ? $this->params['colorscheme'] : null ),
			'pointlabels' => $this->params['pointlabels'],
			'datalabels' => $this->params['pointlabels'],
			'numbersaxislabel' => $this->params['numbersaxislabel'],
			'stackseries' => $this->params['stackseries'],
			'grid' => ( $this->params['theme'] == 'vector' ? array ( 'borderColor' => '#a7d7f9'	) : ( $this->params['theme'] == 'mono' ? array ( 'borderColor' => '#ddd'	) : null ) ),   
			'seriescolors' => (!empty( $this->params['chartcolor'] ) ? explode( ",", $this->params['chartcolor'] ) : null )
		);

		return array (
			'data' => $dataseries , 
			//'rawdata' => $data ,   // control array 
			'series' => $series,
			'parameters' => $parameters,
			'ticks' => $data['numbersticks'],	
			'mode' => 'series',
			'labels' => $labelsticks,
			'legendlabels' => $legendLabels,
			'renderer' => $this->params['renderer'],
		);
	} // end of prepareDataSet();

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
		$params['renderer']->addCriteria( new CriterionInArray(  $srfgjqPlotSettings['seriesrenderer'] ) );

		$params['seriesgroup'] = new Parameter( 'seriesgroup', Parameter::TYPE_STRING, 'row' );
		$params['seriesgroup']->setMessage( 'srf-paramdesc-seriesgroup' );
		$params['seriesgroup']->addCriteria( new CriterionInArray( $srfgjqPlotSettings['seriesgroup'] ) );

		$params['bardirection'] = new Parameter( 'bardirection', Parameter::TYPE_STRING, 'vertical' );
		$params['bardirection']->setMessage( 'srf_paramdesc_bardirection' );
		$params['bardirection']->addCriteria( new CriterionInArray( 'horizontal', 'vertical' ) );
	
		$params['height'] = new Parameter( 'height', Parameter::TYPE_INTEGER, 400 );
		$params['height']->setMessage( 'srf_paramdesc_chartheight' );
		
		// TODO: this is a string to allow for %, but better handling would be nice
		$params['width'] = new Parameter( 'width', Parameter::TYPE_STRING, '100%' );
		$params['width']->setMessage( 'srf_paramdesc_chartwidth' );

		$params['stackseries'] = new Parameter( 'stackseries', Parameter::TYPE_BOOLEAN, false );
		$params['stackseries']->setMessage( 'srf-paramdesc-stackseries' );
						
		$params['pointlabels'] = new Parameter( 'pointlabels', Parameter::TYPE_STRING, '' );
		$params['pointlabels']->setMessage( 'srf-paramdesc-pointlabels' );
		$params['pointlabels']->addCriteria( new CriterionInArray( 'value', 'label' ) );
		
		$params['highlighter'] = new Parameter( 'highlighter', Parameter::TYPE_BOOLEAN, false );
		$params['highlighter']->setMessage( 'srf-paramdesc-highlighter' );

		$params['smoothlines'] = new Parameter( 'smoothlines', Parameter::TYPE_BOOLEAN, false );
		$params['smoothlines']->setMessage( 'srf-paramdesc-smoothlines' );

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

		$params['serieslabel'] = new Parameter( 'serieslabel', Parameter::TYPE_STRING );
		$params['serieslabel']->setMessage( 'srf-paramdesc-serieslabel' );
		$params['serieslabel']->setDefault( '' );

		$params['valueformat'] = new Parameter( 'valueformat', Parameter::TYPE_STRING, '%d' );
		$params['valueformat']->setMessage( 'srf-paramdesc-valueformat' );
		// %.2f round number to 2 digits after decimal point e.g.  EUR %.2f, $ %.2f  
		// %d a signed integer, in decimal

		$params['chartlegend'] = new Parameter( 'chartlegend', Parameter::TYPE_STRING, 'ne' );
		$params['chartlegend']->setMessage( 'srf-paramdesc-chartlegend' );
		$params['chartlegend']->addCriteria( new CriterionInArray( 'nw','n', 'ne', 'e', 'se', 's', 'sw', 'w' ) );

		$params['ticklabels'] = new Parameter( 'ticklabels', Parameter::TYPE_BOOLEAN, true );
		$params['ticklabels']->setMessage( 'srf-paramdesc-datalabels' );

		return $params;
	}	
}