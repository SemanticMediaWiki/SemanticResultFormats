/**
 * This file is part of the Semantic Result Formats Media module
 *
 * Progressive enhancement of the server rendered HTML5 <audio>/<video> player:
 * custom controls (play/pause, stop, seek, volume, repeat, shuffle, full screen)
 * and a playlist. It uses the native HTML5 media elements only and has no third
 * party dependency (it replaces the formerly bundled, unmaintained jPlayer).
 * Without JavaScript the native controls of the browser stay available.
 *
 * @see https://www.semantic-mediawiki.org/wiki/Help:Media_format
 *
 * @license GPL-2.0-or-later
 * @file
 * @ignore
 * @ingroup SRF
 */
( function ( mw ) {
	'use strict';

	const ROOT_SELECTOR = '.srf-media';
	const SVG_NS = 'http://www.w3.org/2000/svg';

	/**
	 * Icons: list of [ path data, 'f' (filled) | 's' (stroked) ] on a 24x24 grid
	 */
	const ICONS = {
		play: [ [ 'M8 5v14l11-7z', 'f' ] ],
		pause: [ [ 'M6 5h4v14H6zM14 5h4v14h-4z', 'f' ] ],
		stop: [ [ 'M6 6h12v12H6z', 'f' ] ],
		previous: [ [ 'M6 6h2v12H6zM9.5 12l8.5 6V6z', 'f' ] ],
		next: [ [ 'M16 6h2v12h-2zM6 18l8.5-6L6 6z', 'f' ] ],
		volume: [ [ 'M3 9v6h4l5 5V4L7 9z', 'f' ], [ 'M15.5 8.5a5 5 0 0 1 0 7', 's' ] ],
		muted: [ [ 'M3 9v6h4l5 5V4L7 9z', 'f' ], [ 'M16 9l5 6M21 9l-5 6', 's' ] ],
		volumeMax: [ [ 'M3 9v6h4l5 5V4L7 9z', 'f' ], [ 'M15.5 8.5a5 5 0 0 1 0 7', 's' ], [ 'M18 6a9 9 0 0 1 0 12', 's' ] ],
		repeat: [ [ 'M17 2l4 4-4 4M3 11V9a3 3 0 0 1 3-3h15M7 22l-4-4 4-4M21 13v2a3 3 0 0 1-3 3H3', 's' ] ],
		shuffle: [ [ 'M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5', 's' ] ],
		fullscreen: [ [ 'M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5', 's' ] ]
	};

	/**
	 * @param {string} name
	 * @return {string}
	 */
	function msg( name ) {
		return mw.msg( 'srf-ui-mediaplayer-label-' + name );
	}

	/**
	 * @param {string} name key of ICONS
	 * @return {SVGElement}
	 */
	function makeIcon( name ) {
		const svg = document.createElementNS( SVG_NS, 'svg' );
		svg.setAttribute( 'viewBox', '0 0 24 24' );
		svg.setAttribute( 'width', '18' );
		svg.setAttribute( 'height', '18' );
		svg.setAttribute( 'aria-hidden', 'true' );
		svg.setAttribute( 'focusable', 'false' );
		ICONS[ name ].forEach( ( def ) => {
			const path = document.createElementNS( SVG_NS, 'path' );
			path.setAttribute( 'd', def[ 0 ] );
			if ( def[ 1 ] === 's' ) {
				path.setAttribute( 'fill', 'none' );
				path.setAttribute( 'stroke', 'currentColor' );
				path.setAttribute( 'stroke-width', '2' );
				path.setAttribute( 'stroke-linecap', 'round' );
				path.setAttribute( 'stroke-linejoin', 'round' );
			} else {
				path.setAttribute( 'fill', 'currentColor' );
			}
			svg.appendChild( path );
		} );
		return svg;
	}

	/**
	 * @param {HTMLElement} el
	 * @param {string} label
	 */
	function setLabel( el, label ) {
		el.title = label;
		el.setAttribute( 'aria-label', label );
	}

	/**
	 * @param {string} cssClass
	 * @param {string} icon key of ICONS
	 * @param {string} label
	 * @return {HTMLButtonElement}
	 */
	function makeButton( cssClass, icon, label ) {
		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'srf-media-button ' + cssClass;
		button.appendChild( makeIcon( icon ) );
		setLabel( button, label );
		return button;
	}

	/**
	 * @param {HTMLButtonElement} button
	 * @param {string} icon key of ICONS
	 */
	function setIcon( button, icon ) {
		const old = button.querySelector( 'svg' );
		const fresh = makeIcon( icon );
		if ( old ) {
			button.replaceChild( fresh, old );
		} else {
			button.appendChild( fresh );
		}
	}

	/**
	 * @param {string} cssClass
	 * @param {number} max
	 * @param {number} step
	 * @param {string} label
	 * @return {HTMLInputElement}
	 */
	function makeRange( cssClass, max, step, label ) {
		const input = document.createElement( 'input' );
		input.type = 'range';
		input.className = 'srf-media-range ' + cssClass;
		input.min = '0';
		input.max = String( max );
		input.step = String( step );
		input.value = '0';
		setLabel( input, label );
		return input;
	}

	/**
	 * @param {HTMLInputElement} input
	 */
	function paintRange( input ) {
		const max = Number( input.max ) || 1;
		input.style.setProperty( '--srf-media-fill', ( Number( input.value ) / max * 100 ) + '%' );
	}

	/**
	 * @param {number} seconds
	 * @return {string} mm:ss
	 */
	function formatTime( seconds ) {
		if ( !isFinite( seconds ) || seconds < 0 ) {
			return '00:00';
		}
		const total = Math.floor( seconds );
		const m = Math.floor( total / 60 );
		const s = total % 60;
		return ( m < 10 ? '0' : '' ) + m + ':' + ( s < 10 ? '0' : '' ) + s;
	}

	/**
	 * Reads the playlist entries from the server rendered markup
	 *
	 * @param {HTMLElement} root
	 * @return {Array}
	 */
	function readItems( root ) {
		return Array.prototype.map.call(
			root.querySelectorAll( '.srf-media-item' ),
			( li ) => {
				let sources = [];
				try {
					sources = JSON.parse( li.getAttribute( 'data-sources' ) || '[]' );
				} catch ( e ) {
					sources = [];
				}
				return {
					element: li,
					sources: Array.isArray( sources ) ? sources : [],
					poster: li.getAttribute( 'data-poster' ) || '',
					type: li.getAttribute( 'data-type' ) === 'video' ? 'video' : 'audio'
				};
			}
		).filter( ( item ) => item.sources.length > 0 );
	}

	/**
	 * Fisher-Yates shuffle returning a new array
	 *
	 * @param {Array} list
	 * @return {Array}
	 */
	function shuffled( list ) {
		const copy = list.slice();
		for ( let i = copy.length - 1; i > 0; i-- ) {
			const j = Math.floor( Math.random() * ( i + 1 ) );
			[ copy[ i ], copy[ j ] ] = [ copy[ j ], copy[ i ] ];
		}
		return copy;
	}

	/**
	 * @param {HTMLElement} root
	 * @return {Object|null} the controller (useful for tests)
	 */
	function init( root ) {
		// The wikipage.content hook can fire more than once for the same markup
		if ( root.classList.contains( 'srf-media-ready' ) ) {
			return null;
		}

		const player = root.querySelector( '.srf-media-element' );
		const items = readItems( root );

		if ( !player || items.length === 0 ) {
			return null;
		}

		root.classList.add( 'srf-media-ready' );

		// The custom controls replace the native ones (which stay as the no-JS fallback)
		player.controls = false;
		player.removeAttribute( 'controls' );

		const multi = items.length > 1;
		const isVideo = player.tagName === 'VIDEO';
		const cover = root.querySelector( '.srf-media-cover' );
		const state = { index: 0, shuffle: false, repeat: false, order: items.map( ( item, i ) => i ) };

		// Controls
		const ui = document.createElement( 'div' );
		ui.className = 'srf-media-ui';

		const seek = makeRange( 'srf-media-seek', 1000, 1, msg( 'play' ) );
		const times = document.createElement( 'div' );
		times.className = 'srf-media-times';
		const timeCurrent = document.createElement( 'span' );
		timeCurrent.className = 'srf-media-time-current';
		timeCurrent.textContent = formatTime( 0 );
		const timeDuration = document.createElement( 'span' );
		timeDuration.className = 'srf-media-time-duration';
		timeDuration.textContent = formatTime( 0 );
		times.append( timeCurrent, timeDuration );

		const controls = document.createElement( 'div' );
		controls.className = 'srf-media-controls';

		const volumeGroup = document.createElement( 'div' );
		volumeGroup.className = 'srf-media-group srf-media-group-volume';
		const mute = makeButton( 'srf-media-mute', 'volume', msg( 'mute' ) );
		const volume = makeRange( 'srf-media-volume', 100, 1, msg( 'volume-max' ) );
		volume.value = '100';
		const volumeMax = makeButton( 'srf-media-volume-max', 'volumeMax', msg( 'volume-max' ) );
		volumeGroup.append( mute, volume, volumeMax );

		const transport = document.createElement( 'div' );
		transport.className = 'srf-media-group srf-media-group-transport';
		const prev = makeButton( 'srf-media-previous', 'previous', msg( 'previous' ) );
		const playPause = makeButton( 'srf-media-play', 'play', msg( 'play' ) );
		const next = makeButton( 'srf-media-next', 'next', msg( 'next' ) );
		const stop = makeButton( 'srf-media-stop', 'stop', msg( 'stop' ) );
		if ( multi ) {
			transport.append( prev, playPause, next, stop );
		} else {
			transport.append( playPause, stop );
		}

		const toggles = document.createElement( 'div' );
		toggles.className = 'srf-media-group srf-media-group-toggles';
		const repeat = makeButton( 'srf-media-repeat', 'repeat', msg( 'repeat' ) );
		repeat.setAttribute( 'aria-pressed', 'false' );
		const shuffle = makeButton( 'srf-media-shuffle', 'shuffle', msg( 'shuffle' ) );
		shuffle.setAttribute( 'aria-pressed', 'false' );
		toggles.appendChild( repeat );
		if ( multi ) {
			toggles.appendChild( shuffle );
		}
		const fullscreen = makeButton( 'srf-media-fullscreen', 'fullscreen', msg( 'full-screen' ) );
		if ( isVideo && ( player.requestFullscreen || player.webkitEnterFullscreen ) ) {
			toggles.appendChild( fullscreen );
		}

		controls.append( volumeGroup, transport, toggles );
		ui.append( seek, times, controls );
		player.after( ui );
		paintRange( seek );
		paintRange( volume );

		// Playback
		function playNow() {
			const promise = player.play();
			if ( promise && typeof promise.catch === 'function' ) {
				// Autoplay can be refused by the browser; the user can still press play
				promise.catch( () => {} );
			}
		}

		function select( index, autoplay ) {
			const item = items[ index ];
			if ( !item ) {
				return;
			}
			state.index = index;

			// Rebuild the <source> list so the browser can pick a supported format
			while ( player.firstChild ) {
				player.removeChild( player.firstChild );
			}
			item.sources.forEach( ( source ) => {
				const el = document.createElement( 'source' );
				el.src = source.src;
				if ( source.type ) {
					el.type = source.type;
				}
				player.appendChild( el );
			} );
			const itemIsVideo = item.type === 'video';
			if ( isVideo ) {
				// The (audio only) items of a mixed list are played without showing the empty video
				player.classList.toggle( 'srf-media-hidden', !itemIsVideo );
				if ( itemIsVideo && item.poster ) {
					player.setAttribute( 'poster', item.poster );
				} else {
					player.removeAttribute( 'poster' );
				}
			}
			if ( cover ) {
				// The cover art of an audio file stays visible while it plays
				const showCover = !itemIsVideo && item.poster !== '';
				cover.hidden = !showCover;
				if ( showCover ) {
					cover.src = item.poster;
				} else {
					cover.removeAttribute( 'src' );
				}
			}
			player.load();

			seek.value = '0';
			paintRange( seek );
			timeCurrent.textContent = formatTime( 0 );
			timeDuration.textContent = formatTime( 0 );

			items.forEach( ( other, i ) => {
				other.element.classList.toggle( 'srf-media-current', i === index );
			} );

			if ( autoplay ) {
				playNow();
			}
		}

		function step( delta, wrap ) {
			const position = state.order.indexOf( state.index ) + delta;
			if ( position < 0 || position >= state.order.length ) {
				return wrap ? state.order[ ( position + state.order.length ) % state.order.length ] : -1;
			}
			return state.order[ position ];
		}

		playPause.addEventListener( 'click', () => {
			if ( player.paused ) {
				playNow();
			} else {
				player.pause();
			}
		} );

		if ( isVideo ) {
			player.addEventListener( 'click', () => playPause.click() );
		}

		stop.addEventListener( 'click', () => {
			player.pause();
			player.currentTime = 0;
		} );

		prev.addEventListener( 'click', () => select( step( -1, true ), true ) );
		next.addEventListener( 'click', () => select( step( 1, true ), true ) );

		player.addEventListener( 'play', () => {
			setIcon( playPause, 'pause' );
			setLabel( playPause, msg( 'pause' ) );
			root.classList.add( 'srf-media-playing' );
		} );
		player.addEventListener( 'pause', () => {
			setIcon( playPause, 'play' );
			setLabel( playPause, msg( 'play' ) );
			root.classList.remove( 'srf-media-playing' );
		} );

		player.addEventListener( 'timeupdate', () => {
			timeCurrent.textContent = formatTime( player.currentTime );
			if ( isFinite( player.duration ) && player.duration > 0 && !seek.dataset.dragging ) {
				seek.value = String( Math.round( player.currentTime / player.duration * 1000 ) );
				paintRange( seek );
			}
		} );
		player.addEventListener( 'durationchange', () => {
			timeDuration.textContent = formatTime( player.duration );
		} );

		seek.addEventListener( 'input', () => {
			seek.dataset.dragging = '1';
			paintRange( seek );
			if ( isFinite( player.duration ) && player.duration > 0 ) {
				player.currentTime = Number( seek.value ) / 1000 * player.duration;
			}
		} );
		seek.addEventListener( 'change', () => {
			delete seek.dataset.dragging;
		} );

		// Volume
		function refreshVolume() {
			const silent = player.muted || player.volume === 0;
			setIcon( mute, silent ? 'muted' : 'volume' );
			setLabel( mute, msg( silent ? 'unmute' : 'mute' ) );
			volume.value = String( player.muted ? 0 : Math.round( player.volume * 100 ) );
			paintRange( volume );
		}
		mute.addEventListener( 'click', () => {
			player.muted = !player.muted;
			if ( !player.muted && player.volume === 0 ) {
				player.volume = 1;
			}
			refreshVolume();
		} );
		volume.addEventListener( 'input', () => {
			player.muted = false;
			player.volume = Number( volume.value ) / 100;
			refreshVolume();
		} );
		volumeMax.addEventListener( 'click', () => {
			player.muted = false;
			player.volume = 1;
			refreshVolume();
		} );
		player.addEventListener( 'volumechange', refreshVolume );

		// Toggles
		shuffle.addEventListener( 'click', () => {
			state.shuffle = !state.shuffle;
			state.order = state.shuffle ?
				[ state.index ].concat( shuffled( items.map( ( item, i ) => i ).filter( ( i ) => i !== state.index ) ) ) :
				items.map( ( item, i ) => i );
			shuffle.setAttribute( 'aria-pressed', String( state.shuffle ) );
			shuffle.classList.toggle( 'srf-media-active', state.shuffle );
			setLabel( shuffle, msg( state.shuffle ? 'shuffle-off' : 'shuffle' ) );
		} );

		repeat.addEventListener( 'click', () => {
			state.repeat = !state.repeat;
			repeat.setAttribute( 'aria-pressed', String( state.repeat ) );
			repeat.classList.toggle( 'srf-media-active', state.repeat );
			setLabel( repeat, msg( state.repeat ? 'repeat-off' : 'repeat' ) );
		} );

		fullscreen.addEventListener( 'click', () => {
			if ( document.fullscreenElement ) {
				document.exitFullscreen();
			} else if ( player.requestFullscreen ) {
				player.requestFullscreen();
			} else if ( player.webkitEnterFullscreen ) {
				player.webkitEnterFullscreen();
			}
		} );

		player.addEventListener( 'ended', () => {
			if ( !multi ) {
				if ( state.repeat ) {
					player.currentTime = 0;
					playNow();
				}
				return;
			}
			const following = step( 1, state.repeat );
			if ( following !== -1 ) {
				select( following, true );
			}
		} );

		// Clicking an entry of the playlist plays it (without JS the link opens the file)
		items.forEach( ( item, i ) => {
			item.element.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				select( i, true );
			} );
		} );

		select( 0, false );
		refreshVolume();

		return { select: select, state: state, player: player, ui: ui };
	}

	/**
	 * @param {Document|HTMLElement} [scope]
	 * @return {Array}
	 */
	function initAll( scope ) {
		return Array.prototype.map.call(
			( scope || document ).querySelectorAll( ROOT_SELECTOR ),
			init
		);
	}

	const api = { init: init, initAll: initAll, formatTime: formatTime };

	if ( typeof module !== 'undefined' && module.exports ) {
		module.exports = api;
	}

	mw.srfMedia = api;

	if ( typeof mw.hook === 'function' ) {
		mw.hook( 'wikipage.content' ).add( ( $content ) => {
			initAll( $content && $content[ 0 ] ? $content[ 0 ] : document );
		} );
	}

}( mediaWiki ) );
