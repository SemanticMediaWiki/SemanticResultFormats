/**
 * File holding the distance-filter plugin
 * 
 * For this plugin to work, the filtered plugin needs to be available first.
 * 
 * @author Stephan Gambke
 * @file
 * @ingroup SemanticResultFormats
 */

(function ($) {

	var methods = {
		
		init: function( args ){
			
			function update( filtered, filterDistance, target ) {
				
				var values = filtered.data('ext.srf.filtered')['values'];

				for ( i in values ) {

					var distance = values[i]['data']['distance-filter'];

					filtered.filtered( 'voteItemVisibilityAndUpdate', {
						'filter': 'value', 
						'printout' : target, 
						'visible': distance <= filterDistance,
						'item': i
					});

				}
				
			}  // function update( filtered, filterDistance, target )
			
			
			var filtered = this;
			
			
			
			var target = args.printout;
			var values = this.data('ext.srf.filtered')['values'];
			var data = this.data('ext.srf.filtered')['data']['filterdata']['distance'][target];
			
			// build filter controls
			var filtercontrols = this.children('.filtered-filters').children('.filtered-distance');
			
			// insert the label of the printout this filter filters on
			
			for ( var i in values ) break;

			var readoutAndSlider = $('<tr>');

			var readout = $('<div class="filtered-distance-readout">' + data['max'] + data['unit'] + '</div>' );
			var slider = $('<div class="filtered-distance-slider">');

			var readoutTD = $('<td class="filtered-distance-readout-cell">');
			var sliderTD = $('<td class="filtered-distance-slider-cell">');
			
			readoutTD.append( readout );
			sliderTD.append( slider );
			
			readoutAndSlider
			.append( readoutTD )
			.append( sliderTD );
			
			filtercontrols
			.append( '<div class="filtered-distance-label"><span>' + values[i]['printouts'][target]['label'] + '</span></div>' )
			.append( readoutAndSlider );

			readoutAndSlider.wrap('<table><tbody>');
			
			readout.width( readout.width() ); // fix width of readout
			
			slider.slider({
				animate: true,
				max: data['max'],
				value: data['max'],
				slide: function(event, ui) {
					readout.empty().append( ui.value + data['unit'] );
				},
				change: function(event, ui) {
					update( filtered, ui.value, target );
				}
			});
			
//			// insert the label of the printout this filter filters on
//			filtercontrols.append('<div class="filtered-value-label"><span>' + values[i]['printouts'][target]['label'] + '</span></div>');
//
//			if ( collapsible != null && ( collapsible == 'collapsed' || collapsible == 'uncollapsed') ) {
//				var showControl = $('<span class="filtered-value-show">[+]</span>');
//				var hideControl = $('<span class="filtered-value-hide">[-]</span>');
//				
//				
//				filtercontrols
//				.prepend(showControl)
//				.prepend(hideControl);
//				
//				filtercontrols = $('<div class="filtered-value-collapsible">')
//				.appendTo(filtercontrols);
//				
//				showControl.click(function(){
//					filtercontrols.slideDown();
//					showControl.hide();
//					hideControl.show();
//				});
//				
//				hideControl.click(function(){
//					filtercontrols.slideUp();
//					showControl.show();
//					hideControl.hide();
//				});
//				
//				if ( collapsible == 'collapsed' ) {
//					hideControl.hide();
//					filtercontrols.slideUp(0);
//				} else {
//					showControl.hide();
//				}
//				
//			}
//
//			// set default config values
//			filtered.filtered( 'setFilterData', {filter: 'value', printout: target, configvar: 'use or', configvalue: true} );
//			
//			
//			// insert switches
//			if ( switches != null && switches.length > 0 ) {
//			
//				var switchControls = $('<div class="filtered-value-switches">');
//			
//				if ( $.inArray('and or', switches) >= 0 ) {
//
//					var andorControl = $('<div class="filtered-value-andor">');
//					var andControl = $('<input type="radio" name="filtered-value-andor ' +
//						target + '"  class="filtered-value-andor ' + target + '" value="and">');
//
//					var orControl = $('<input type="radio" name="filtered-value-andor ' +
//						target + '"  class="filtered-value-andor ' + target + '" value="or" checked>');
//
//					andControl
//					.add( orControl )
//					.change(function() {
//						filtered.filtered( 'setFilterData', {filter: 'value', printout: target, configvar: 'use or', configvalue: orControl.is(':checked')} );
//						update( filtered, filtercontrols, target );
//					});
//
//					andorControl
//					.append( orControl )
//					.append(' OR ')
//					.append( andControl )
//					.append(' AND ')
//					.appendTo( switchControls );
//
//				}
//				
//				filtercontrols.append( switchControls );
//			}
//			
//			if ( height != null ) {
//				filtercontrols = $( '<div class="filtered-value-scrollable">' )
//				.appendTo( filtercontrols );
//				
//				filtercontrols.height( height );
//			}
//			
//			
//			var sortedDistinctValues = [];
//			
//			for ( var i in distinctValues ) {
//				sortedDistinctValues.push(i);
//			}
//			
//			sortedDistinctValues.sort();
//			
//			// insert options (checkboxes and labels) and attach event handlers
//			// TODO: Do we need to wrap these in a form?
//			for ( var j in sortedDistinctValues ) {
//				var option = $('<div class="filtered-value-option">');
//				var checkbox = $('<input type="checkbox" class="filtered-value-value" value="' + sortedDistinctValues[j] + '"  >');
//				
//				// attach event handler
//				checkbox.change(function( evt ){
//					update(filtered, filtercontrols, target);
//				});
//				
//				option
//				.append(checkbox)
//				.append(sortedDistinctValues[j]);
//				
//				filtercontrols
//				.append(option);
//				
//			}
			
			return this;
		},
		
		alert: function(){
			alert('DistanceFilter!');
			return this;
		}
		
	};

	distanceFilter = function( method ) {
  
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.filtered.distanceFilter' );
		}    


	};

	// attach ListView to all Filtered query printers
	// let them sort out, if ListView is actually applicable to them
	jQuery('.filtered').filtered('attachFilter', 'distance', distanceFilter );

})(jQuery);

