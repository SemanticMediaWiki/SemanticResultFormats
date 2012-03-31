/**
 * JavaSript for SRF jqPlot Pie module
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

		$( ".srf-jqplotpie" ).each(function() {	

			var $this = $( this );
			var jqplotpie = $this.closest( ".srf-jqplotpie" ).attr( "id" );
			var json = mw.config.get(jqplotpie);

			// Parse json string and convert it back into objects
			typeof json == 'string' ? data = JSON.parse( json ) : data = json;
	
			// .remove() was reported to solve some memory leak problems on IE 
			// in connection with canvas objects
			$this.find('.srf-jqplotpie').remove();

			// Release graph and bottom text
 			$this.parent().show();
			
			console.log(data);
			console.log(jqplotpie);
/******************************************************************************
 *	Begin jqPlot javascript
 ******************************************************************************/
			// Handle the data array 
			var dataRenderer = function() {
				jqplotpiedata = data.data;
				console.log(jqplotpiedata);		
				return jqplotpiedata;
			};

			$.jqplot.config.enablePlugins = true;
					
			var jqplotpie = $.jqplot(jqplotpie, [], {
					dataRenderer: dataRenderer,
					title: data['parameters'].charttitle,
					seriesColors: (  data['parameters'].seriescolors ?  data['parameters'].seriescolors :  ( data['parameters'].colorscheme == null ? null : colorscheme[data['parameters'].colorscheme][9] ) ) ,
					grid: data['parameters'].grid,
					seriesDefaults: {
						renderer: (data['renderer'] == 'donut' ? $.jqplot.DonutRenderer : $.jqplot.PieRenderer ), 
						shadow:  ( data['parameters'].theme == 'mono' ? false : true ) ,
						rendererOptions: {
							fill: data['parameters'].filling,
							lineWidth: 2,
							showDataLabels: ( data['parameters'].datalabels == 'percent' || data['parameters'].datalabels == 'value' || data['parameters'].datalabels == 'label' ? true : false ),
							dataLabels: data['parameters'].datalabels,
							sliceMargin: 2,
							dataLabelFormatString: ( data['parameters'].datalabels == 'label' ? null : ( !data['parameters'].valueformat ? '%d' : data['parameters'].valueformat ) )
						}, 
					},
				legend: {
					show: ( !data['parameters'].chartlegend ? false : true ), 
					location: data['parameters'].chartlegend, 
					// labels:	 data['legendLabels'],					
					placement: 'inside', 
					xoffset: 10,
					yoffset:10
				} 
		} ); // end of jqplot object

		// Theming support for commonly styled attributes of plot elements 
		// using jqPlot's "themeEngine" 
		jqplotpie.themeEngine.newTheme( 'mono', mono ); 
		jqplotpie.themeEngine.newTheme( 'vector', vector ); 
		( data['parameters'].theme == null ? null : jqplotpie.activateTheme( data['parameters'].theme ) ); // only overwrites the default for cases witha theme 

/******************************************************************************
 *	End of jqPlot javascript
 ******************************************************************************/
		} ); // end of initilized $this object
	} ); // end $(document).ready  
} )( window.jQuery );	  