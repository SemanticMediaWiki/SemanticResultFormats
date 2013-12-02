/**
 * JavaScript for SRF Java Infovis Toolkit module
 * 
 * @since 1.9
 * 
 * @ingroup SemanticResultFormats
 * 
 * @licence GNU GPL v2 or later
 */
(function($) {

	var labelType, useGradients, nativeTextSupport, animate;
	var ua = navigator.userAgent, iStuff = ua.match(/iPhone/i)
			|| ua.match(/iPad/i), typeOfCanvas = typeof HTMLCanvasElement, nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'), textSupport = nativeCanvasSupport
			&& (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
	labelType = (!nativeCanvasSupport || (textSupport && !iStuff)) ? 'Native' : 'HTML';
	nativeTextSupport = labelType == 'Native';
	useGradients = nativeCanvasSupport;
	animate = !(iStuff || !nativeCanvasSupport);

	$.fn.createJitVisualisation = function() {
		var graphData = $.parseJSON((this).html());
		(function (json, settings) {
			var divID = '#progress-' + settings.d_id;
			var prg_bar = $(divID);
			var fd = new $jit.ForceDirected(
				{
					injectInto : settings.divID,
					Navigation : {
						enable : settings.navigation,
						panning : settings.panning,
						zooming : settings.zooming
					},
					Node : {
						overridable : true,
						color : '#005588',
						dim : 7
					},
					Edge : {
						overridable : false,
						color : settings.edgeColor,
						lineWidth : settings.edgeWidth
					},
					Label : {
						type : labelType, // Native or HTML
						size : 16,
						style : 'normal',
						textAlign : 'center',
						color : settings.labelColor
					},
					Tips : {
						enable : true,
						onShow : function(tip, node) {
							// count connections
							var count = 0;
							node.eachAdjacency(function() {
								count++;
							});

							var tipHTML = "<div class=\"tip-title\">" + node.name + "</div>" + "<div class=\"tip-text\"><b>connections:</b> " + count + "</div>" + "<div class=\"tip-text\"><ol>";

							node.eachAdjacency(function(adj) {
								var nodeToName = adj.nodeTo.name;
								var nodeFromName = adj.nodeFrom.name;
								var edgeType = adj.nodeTo.getData("edgeType");
								tipHTML += "<li>" + nodeToName + (edgeType != ''? "(" + edgeType + ")" : "") + "</li>";
							});
							tipHTML += "</ol></div>";
							tip.innerHTML = tipHTML;
						}
					},
					Events : {
						enable : true,
						type : 'Native',
						onMouseEnter : function() {
							fd.canvas.getElement().style.cursor = '';
						},
						onMouseLeave : function() {
							fd.canvas.getElement().style.cursor = '';
						},
						onDragMove : function(node, eventInfo, e) {
							var pos = eventInfo.getPos();
							node.pos.setc(pos.x, pos.y);
							fd.plot();
						},
						onTouchMove : function(node, eventInfo, e) {
							$jit.util.event.stop(e); 
							this.onDragMove(node, eventInfo, e);
						},
						onClick : function(node) {
							if (!node)
								return;
							var html = "<h4>" + node.name
									+ "</h4><b> connections:</b>", list = [];
							node.eachAdjacency(function(adj) {
								list.push(adj.nodeTo.name);
							});
							window.location = node.getData("url");
						}
					},
					iterations : 200,
					levelDistance : settings.edgeLength,
					onCreateLabel : function(domElement, node) {
						domElement.innerHTML = node.name;
						var style = domElement.style;
						style.fontSize = "0.8em";
						style.color = "#ddd";
					},
					onPlaceLabel : function(domElement, node) {
						var style = domElement.style;
						var left = parseInt(style.left);
						var top = parseInt(style.top);
						var w = domElement.offsetWidth;
						style.left = (left - w / 2) + 'px';
						style.top = (top + 10) + 'px';
						style.display = '';
					}
				}
			);
			fd.loadJSON(json);
			fd.computeIncremental({
				iter : 40,
				property : 'end',
				onStep : function(perc) {
					prg_bar.progressBar(perc);
				},
				onComplete : function() {
					prg_bar.progressBar(100);
					var divID = '#progress-' + settings.d_id;
					var t = setTimeout("jQuery('" + divID + "').hide('slow');",
							3000);
					fd.animate({
						modes : [ 'linear' ],
						transition : $jit.Trans.Elastic.easeOut,
						duration : 3000
					});
				}
			});
		})(graphData.data, graphData.settings);
	};


	$(document).ready(function() {
		$(".infovis-data").each(function(e) {
			$(this).createJitVisualisation();
		});
	});
})(window.jQuery);