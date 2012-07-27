/**
 * JavaSript for SRF jqPlot Bar module
 * 
 * The script is designed to handle single and series mode while
 * SRF_jqPlotBar.php itself is designed to support single mode 
 * for aggregated numbers only  
 *
 * @licence: GNU GPL v3 or later
 * @author:  mwjames 
 * 
 * @release: 0.3  
 */
(function( $ ) {

	// Only display errors
	try { console.log('console ready'); } catch (e) { var console = { log: function () { } }; }

	$(document).ready(function() {

		$( ".srf-jqplotbar" ).each(function() {	

			var $this = $( this );
			var jqplotbar = $this.closest( ".srf-jqplotbar" ).attr( "id" );
			var json = mw.config.get(jqplotbar);

			// Parse json string and convert it back into objects
			typeof json == 'string' ? data = JSON.parse( json ) : data = json;
	
			// .remove() was reported to solve some memory leak problems on IE 
			// in connection with canvas objects
			$this.find('.srf-jqplotbar').remove();

			// Release graph and bottom text
			$this.parent().show();
			
			console.log(data);
			console.log(jqplotbar);
/******************************************************************************
 *	Begin jqPlot javascript
 ******************************************************************************/
			// Handle the data array 
			var dataRenderer = function() {
				jqplotdata = data.data;
				console.log(jqplotdata);		
				return jqplotdata;
			};

			// Elements to be swapped with direction, can't be done within var definition 
			// because $.functions included
			var numberaxis = {    
				ticks: ( ( data['parameters'].stackseries == true ) || ( data['parameters'].autoscale == true ) ?  [] : data['ticks'] ), // use autoscale for staked series
				label: data['parameters'].numbersaxislabel,
				labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
				autoscale: ( ( data['parameters'].stackseries == true ) || ( data['parameters'].autoscale == true ) ? true : false ), 
				tickOptions: {
				angle: ( data['parameters'].bardirection == 'horizontal' ? 0 : -40 ), 
				formatString: ( !data['parameters'].valueformat ? '%d' : data['parameters'].valueformat )  // %d default
				}
			};

			var labelaxis = {  
				renderer: $.jqplot.CategoryAxisRenderer,
				ticks: data['labels'],
				tickRenderer: $.jqplot.CanvasAxisTickRenderer,
				tickOptions: {
					angle: ( data['parameters'].bardirection == 'horizontal' ? 0 : -40 ), 
					formatString: ( !data['parameters'].valueformat ? '%d' : data['parameters'].valueformat )  // %d default
				}
			};

			// Required for the horizontal view
			var single = [{ 
						renderer: ( data['renderer'] == 'line' ?  $.jqplot.LineRenderer : $.jqplot.BarRenderer ), 
						rendererOptions: {
							barDirection: data['parameters'].bardirection,
							barPadding: 6,
							barMargin: ( data['parameters'].bardirection == 'horizontal' ? 8 : 6),
							barWidth: ( data['renderer'] == 'vector' || data['renderer'] == 'mono' ? 20 : null ), 
  						smooth: ( data['parameters'].smoothlines == true ? true : false ),
							varyBarColor: true
 						},
					}];
			var series = data['series'];

			// Explicitly enable plugins either via { show: true } plugin option 
			// to the plot or by using the $.jqplot.config.enablePlugins = true; 
			$.jqplot.config.enablePlugins = true;
 
			var jqplotbar = $.jqplot(jqplotbar, [], {
					title: data['parameters'].charttitle,
					dataRenderer: dataRenderer,
					stackSeries: ( data['parameters'].stackseries == true ?  true : false ),
					seriesColors: (  data['parameters'].seriescolors ?  data['parameters'].seriescolors :  ( data['parameters'].colorscheme == null ? null : colorscheme[data['parameters'].colorscheme][9] ) ) ,
					axesDefaults: 	{
						padMax: 2.5,
						pad: 2.1,
						showTicks: ( data['parameters'].ticklabels == true ? true : false ), 
						tickOptions: { showMark: false }
					},
					grid: data['parameters'].grid,
					highlighter: {
						show:  ( data['parameters'].highlighter == true && data['renderer'] == 'line' ? true : false ),		
						showTooltip: ( data['parameters'].highlighter == true ? true : false ),
						tooltipLocation: 'w',
						useAxesFormatters: true,
						tooltipAxes: ( data['parameters'].bardirection == 'horizontal' ? 'x' : 'y' )
					},
					seriesDefaults:  { 
						renderer: ( data['renderer'] == 'line' ?  $.jqplot.LineRenderer : $.jqplot.BarRenderer ), 
						fillToZero: true,
						shadow: ( data['parameters'].theme == 'mono' || data['parameters'].theme == 'plain' ? false : true ),
						//trendline: {
				 		//	'show' => ( $this->params['trendline'] == true && $this->params['renderer'] == 'line' ? true : false ),
			 			//	'color' => '#666666',
						//}, 
						rendererOptions: {
							smooth: ( data['parameters'].smoothlines == true ? true : false )				
						},
						pointLabels: {
							show: ( data['parameters'].pointlabels == true ? false : true ),
							location: ( data['parameters'].bardirection == 'vertical' ? 'n' : 'e' ), 
							edgeTolerance: ( data['renderer'] == 'bar' ? '-35': '-20' ), 
							formatString: ( !data['parameters'].valueformat ? '%d' : data['parameters'].valueformat ),
							labels: ( data['parameters'].pointlabels == 'label' ? data['labels'] : data['numbers'] )
					 }	
					},
					series: ( data['mode'] == 'single' ?  single : series ),
					axes: {
						xaxis : ( data['parameters'].bardirection == 'vertical' ? labelaxis : numberaxis ) ,
						yaxis : ( data['parameters'].bardirection == 'vertical' ? numberaxis : labelaxis ) ,
						// x2axis : ( data['parameters'].bardirection == 'vertical' ?  label2axis : number2axis ) ,
						// y2axis : ( data['parameters'].bardirection == 'vertical' ?  number2axis : label2axis ) 
					},
					legend: {
						show: ( !data['parameters'].chartlegend ? false : true ), 
						location: data['parameters'].chartlegend, 
						labels:	 data['legendLabels'],  
						placement: 'inside',
						xoffset: 10,
						yoffset: 10						
					} 
			} ); // enf of $.jqplot

			// Theming support for commonly styled attributes of plot elements 
			// using jqPlot's "themeEngine" 
			jqplotbar.themeEngine.newTheme( 'mono', mono ); 
			jqplotbar.themeEngine.newTheme( 'vector', vector ); 
			( data['parameters'].theme == null ? null : jqplotbar.activateTheme( data['parameters'].theme ) ); // only overwrites the default for cases witha theme 

/******************************************************************************
 *	End of jqPlot javascript
 ******************************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready
} )( window.jQuery );	  