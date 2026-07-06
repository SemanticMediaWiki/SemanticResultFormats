// useGradients/nativeTextSupport/animate are legacy JIT toolkit feature-detection state kept
// for parity with upstream; only labelType is consumed.
// eslint-disable-next-line no-unused-vars
let labelType, useGradients, nativeTextSupport, animate;

( function () {
	const ua = navigator.userAgent,
		iStuff = ua.match( /iPhone/i ) || ua.match( /iPad/i ),
		typeOfCanvas = typeof HTMLCanvasElement,
		nativeCanvasSupport = ( typeOfCanvas === 'object' || typeOfCanvas === 'function' ),
		textSupport = nativeCanvasSupport &&
        ( typeof document.createElement( 'canvas' ).getContext( '2d' ).fillText === 'function' );
	// I'm setting this based on the fact that ExCanvas provides text support for IE
	// and that as of today iPhone/iPad current text support is lame
	labelType = ( !nativeCanvasSupport || ( textSupport && !iStuff ) ) ? 'Native' : 'HTML';
	nativeTextSupport = labelType === 'Native';
	useGradients = nativeCanvasSupport;
	animate = !( iStuff || !nativeCanvasSupport );
}() );

// eslint-disable-next-line no-unused-vars -- kept for the commented-out Log.write() calls below
const Log = {
	elem: false,
	write: function ( text ) {
		if ( !this.elem ) {
			this.elem = document.getElementById( 'log' );
		}
		this.elem.innerHTML = text;
		this.elem.style.left = ( 500 - this.elem.offsetWidth / 2 ) + 'px';
	}
};

// called as `this.init(json, graphSettings)` from PHP-generated inline script markup,
// see SRF_JitGraph.php
// eslint-disable-next-line no-unused-vars
function init( json, userSettings ) {
	// init data

	// end
	// init ForceDirected

	/*
  var settings = {
      "divID": "infovis",
      "edgeColor": "#23A4FF",
      "edgeWidth": 2,
      "edgeLength": 150,
      "navigation": true,
      "zooming": false,
      "panning": "avoid nodes",
      "labelcolor": "#000000"
  };
  */

	const settings = userSettings;
	const divID = '#progress-' + settings.d_id;
	const prgBar = jQuery( divID );

	// alert(settings.edgeColor)

	const fd = new $jit.ForceDirected( {
		// id of the visualization container
		injectInto: settings.divID,
		// Enable zooming and panning
		// by scrolling and DnD
		Navigation: {
			enable: settings.navigation,
			// Enable panning events only if we're dragging the empty
			// canvas (and not a node).
			panning: settings.panning,
			zooming: settings.zooming // zoom speed. higher is more sensible
		},
		// Change node and edge styles such as
		// color and width.
		// These properties are also set per node
		// with dollar prefixed data-properties in the
		// JSON structure.
		Node: {
			overridable: true,
			color: '#005588',
			dim: 7
		},
		Edge: {
			overridable: false,
			color: settings.edgeColor,
			lineWidth: settings.edgeWidth
		},
		// Native canvas text styling
		Label: {
			type: labelType, // Native or HTML
			size: 16,
			style: 'normal',
			textAlign: 'center',
			color: settings.labelColor
		},
		// Add Tips
		Tips: {
			enable: true,
			onShow: function ( tip, node ) {
				// count connections
				let count = 0;
				node.eachAdjacency( () => {
					count++;
				} );
				// display node info in tooltip

				let tipHTML = '<div class="tip-title">' + node.name + '</div>' +
          '<div class="tip-text"><b>connections:</b> ' + count + '</div>' +
          '<div class="tip-text"><ol>';

				node.eachAdjacency( ( adj ) => {
					const nodeToName = adj.nodeTo.name;
					tipHTML += '<li>' + nodeToName + '</li>';
					// tipHTML += "<li>Connection "+ counter +": "+nodeFromName +" "+ edgeType+" " +nodeToName+"</li>";
					// list.push(adj.nodeTo.name);
				} );

				tipHTML += '</ol></div>';
				tip.innerHTML = tipHTML;
			}
		},
		// Add node events
		Events: {
			enable: true,
			type: 'Native',
			// Change cursor style when hovering a node
			onMouseEnter: function () {
				fd.canvas.getElement().style.cursor = '';
			},
			onMouseLeave: function () {
				fd.canvas.getElement().style.cursor = '';
			},
			// Update node positions when dragged
			onDragMove: function ( node, eventInfo ) {
				const pos = eventInfo.getPos();
				node.pos.setc( pos.x, pos.y );
				fd.plot();
			},
			// Implement the same handler for touchscreens
			onTouchMove: function ( node, eventInfo, e ) {
				$jit.util.event.stop( e ); // stop default touchmove event
				this.onDragMove( node, eventInfo, e );
			},
			// Add also a click handler to nodes
			onClick: function ( node ) {
				if ( !node ) {
					return;
				}
				// Build the right column relations list.
				// This is done by traversing the clicked node connections.
				const list = [];
				node.eachAdjacency( ( adj ) => {
					list.push( adj.nodeTo.name );
				} );

				// append connections information
				// $jit.id('inner-details').innerHTML = html + list.join("</li><li>") + "</li></ul>";
				window.location = node.getData( 'url' );
			}
		},
		// Number of iterations for the FD algorithm
		iterations: 200,
		// Edge length
		levelDistance: settings.edgeLength,
		// Add text to the labels. This method is only triggered
		// on label creation and only for DOM labels (not native canvas ones).
		onCreateLabel: function ( domElement, node ) {
			domElement.innerHTML = node.name;
			const style = domElement.style;
			style.fontSize = '0.8em';
			style.color = '#ddd';
		},
		// Change node styles when DOM labels are placed
		// or moved.
		onPlaceLabel: function ( domElement ) {
			const style = domElement.style;
			const left = parseInt( style.left );
			const top = parseInt( style.top );
			const w = domElement.offsetWidth;
			style.left = ( left - w / 2 ) + 'px';
			style.top = ( top + 10 ) + 'px';
			style.display = '';
		}
	} );
	// load JSON data.
	fd.loadJSON( json );
	// compute positions incrementally and animate.
	fd.computeIncremental( {
		iter: 40,
		property: 'end',
		onStep: function ( perc ) {
			// alert(divID);
			prgBar.progressBar( perc );
			// alert(perc + '% loaded…');
			// Log.write(perc + '% loaded...');
		},
		onComplete: function () {
			// Log.write('done');
			prgBar.progressBar( 100 );
			setTimeout( () => {
				jQuery( divID ).hide( 'slow' );
			}, 3000 );
			fd.animate( {
				modes: [ 'linear' ],
				transition: $jit.Trans.Elastic.easeOut,
				duration: 3000
			} );
		}
	} );
	// end
}
