/**
 * JavaSript for SRF jqPlot Series module
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

		$( ".srf-jqplotbubble" ).each(function() {	

			var $this = $( this );
			var jqplotbubble = $this.closest( ".srf-jqplotbubble" ).attr( "id" );
			
			var json = mw.config.get(jqplotbubble);

			// Parse json string and convert it back into an object
			typeof json == 'string' ? data = JSON.parse( json ) : data = json;
	
			// .remove() was reported to solve some memory leak problems on IE 
			// in connection with canvas objects
			$this.find('.srf-jqplotbubble').remove();

			// Release graph and bottom text
 			$this.parent().show();
			
			console.log(data);
			console.log(jqplotbubble);
/******************************************************************************
 *	Begin jqPlot javascript
 ******************************************************************************/
			// Handle the data array 
			var dataRenderer = function() {
				jqplotbubbledata = data.data;
				labels = data.legendlabels;

				// Bubble data object requires [x, y, radius, <label or object>]
				// threfore the fouth element has to be a label 
				$.each( jqplotbubbledata,function( key, val ){
					jqplotbubbledata[key][3] = labels[key]; 
				});
				console.log(jqplotbubbledata);		
				return [jqplotbubbledata];
			};

			$.jqplot.config.enablePlugins = true;
			
			var jqplotbubble = $.jqplot(jqplotbubble, [], {
					dataRenderer: dataRenderer,
					title: data['parameters'].charttitle,
					seriesColors: (  data['parameters'].seriescolors ?  data['parameters'].seriescolors :  ( data['parameters'].colorscheme == null ? null : colorscheme[data['parameters'].colorscheme][9] ) ) ,
					grid: data['parameters'].grid,
					seriesDefaults: {
						renderer: (data['renderer'] == 'bubble' ? $.jqplot.BubbleRenderer : $.jqplot.PieRenderer ), 
						shadow:  ( data['parameters'].theme == 'mono' ? false : true ) ,
						rendererOptions: {
							autoscalePointsFactor: -0.15,
							autoscaleMultiplier: 0.85,
							highlightMouseOver: true,
							bubbleGradients: true,
							bubbleAlpha: 0.7
						}, 
					},
				legend: {
					show: ( !data['parameters'].chartlegend ? false : false ), 
					location: data['parameters'].chartlegend, 
					// labels:	 data['legendLabels'],					
					placement: 'inside', 
					xoffset: 10,
					yoffset:10
				} 
		} ); // end of jqplot object

		// Theming support for commonly styled attributes of plot elements 
		// using jqPlot's "themeEngine" 
		jqplotbubble.themeEngine.newTheme( 'mono', mono ); 
		jqplotbubble.themeEngine.newTheme( 'vector', vector ); 
		( data['parameters'].theme == null ? null : jqplotbubble.activateTheme( data['parameters'].theme ) ); // only overwrites the default for cases witha theme 

/******************************************************************************
 *	End of jqPlot javascript
 ******************************************************************************/
		} ); // end of initilized $this object
	
	} ); // end $(document).ready  

})( window.jQuery );	  