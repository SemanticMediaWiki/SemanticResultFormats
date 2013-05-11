/**
 * This file is part of the Semantic Result Formats Media module
 * @see https://www.semantic-mediawiki.org/wiki/Help:Media_formats
 *
 * @section LICENSE
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 *
 * @file
 * @ignore
 *
 * @since 1.9
 * @ingroup SRF
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */
( function( $, mw, srf ) {
	'use strict';

	/*jshint scripturl:true*/

	/**
	 * Helper method
	 * @ignore
	 */
	var h = mw.html;

	/**
	 * Internationalization (i18n) support
	 *
	 * @since 1.9
	 * @ignore
	 */
	var _i18n = {
		'previous' : mw.msg( 'srf-ui-mediaplayer-label-previous' ),
		'pause' : mw.msg( 'srf-ui-mediaplayer-label-pause' ),
		'play' : mw.msg( 'srf-ui-mediaplayer-label-play' ),
		'next' : mw.msg( 'srf-ui-mediaplayer-label-next' ),
		'stop' : mw.msg( 'srf-ui-mediaplayer-label-stop' ),
		'mute' : mw.msg( 'srf-ui-mediaplayer-label-mute' ),
		'unmute' : mw.msg( 'srf-ui-mediaplayer-label-unmute' ),
		'volumeMax' : mw.msg( 'srf-ui-mediaplayer-label-volume-max' ),
		'shuffle' : mw.msg( 'srf-ui-mediaplayer-label-shuffle' ),
		'shuffleOff' : mw.msg( 'srf-ui-mediaplayer-label-shuffle-off' ),
		'repeat' : mw.msg( 'srf-ui-mediaplayer-label-repeat' ),
		'repeatOff' : mw.msg( 'srf-ui-mediaplayer-label-repeat-off' ),
		'fullScreen' : mw.msg( 'srf-ui-mediaplayer-label-full-screen' ),
		'restoreScreen' : mw.msg( 'srf-ui-mediaplayer-label-restore-screen' )
	};

	/**
	 * Inheritance class for the srf.template constructor
	 *
	 * @since 1.9
	 *
	 * @class
	 * @abstract
	 */
	srf.template = srf.template || {};

	/**
	 * Base constructor for objects representing a template instance
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.template
	 */
	srf.template.jplayer = {

		/**
		 * Placeholder for the media inspector
		 *
		 * @return object
		 */
		inspector : function( ID ) { return h.element( 'div', { 'id' : ID }, '' );
		},

		/**
		 * Audio player template
		 *
		 * @return object
		 */
		audio: {
			single : function( options ) { return h.element( 'div', { 'id' : options.playerId, 'class': 'jp-jplayer' } ) +
				h.element( 'div', { 'id' : options.containerId , 'class': 'jp-audio' },
					new h.Raw( h.element( 'div', { 'class': 'jp-type-single' },
						new h.Raw( h.element( 'div', { 'class': 'jp-gui jp-interface' },
							new h.Raw( h.element( 'ul', { 'class': 'jp-controls' },
								new h.Raw (
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-play' , 'tabindex' : 1, 'title': _i18n.play }, _i18n.play ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-pause' , 'tabindex' : 1, 'title': _i18n.pause }, _i18n.pause ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-stop' , 'tabindex' : 1, 'title': _i18n.stop }, _i18n.stop ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-mute' , 'tabindex' : 1, 'title' : _i18n.mute }, _i18n.mute ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-unmute' , 'tabindex' : 1, 'title': _i18n.unmute }, _i18n.unmute ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-volume-max' , 'tabindex' : 1, 'title': _i18n.volumeMax }, _i18n.volumeMax ) ) )
								) ) +
								// progress
								h.element( 'div', { 'class': 'jp-progress' }, new h.Raw ( h.element( 'div', { 'class': 'jp-seek-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-play-bar' }, '' ) ) ) ) ) +
								// Volumn
								h.element( 'div', { 'class': 'jp-volume-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-volume-bar-value' }, '' ) ) ) +
								// Time
								h.element( 'div', { 'class': 'jp-time-holder' },  new h.Raw (  h.element( 'div', { 'class': 'jp-current-time' }, '' ) + h.element( 'div', { 'class': 'jp-duration' }, '' ) ) ) +
								//
								h.element( 'ul', { 'class': 'jp-toggles' },
								new h.Raw (
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat' , 'tabindex' : 1, 'title' : _i18n.repeat }, _i18n.repeat ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat-off' , 'tabindex' : 1, 'title' : _i18n.repeatOff }, _i18n.repeatOff ) ) )
								) )
						) ) + h.element( 'div', { 'class': 'jp-playlist' }, new h.Raw( h.element( 'ul', {}, new h.Raw( h.element( 'li', {}, '' ) ) ) ) ) )
				) ) );
			},
			multi : function( options ) { return h.element( 'div', { 'id' : options.playerId, 'class': 'jp-jplayer' } ) +
				h.element( 'div', { 'id' : options.containerId , 'class': 'jp-audio' },
					new h.Raw( h.element( 'div', { 'class': 'jp-type-playlist' },
						new h.Raw( h.element( 'div', { 'class': 'jp-gui jp-interface' },
							new h.Raw( h.element( 'ul', { 'class': 'jp-controls' },
								new h.Raw (
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-previous' , 'tabindex' : 1, 'title': _i18n.previous }, _i18n.previous ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-play' , 'tabindex' : 1, 'title': _i18n.play }, _i18n.play ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-pause' , 'tabindex' : 1, 'title': _i18n.pause }, _i18n.pause ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-next' , 'tabindex' : 1, 'title': _i18n.next }, _i18n.next ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-stop' , 'tabindex' : 1, 'title': _i18n.stop }, _i18n.stop ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-mute' , 'tabindex' : 1, 'title' : _i18n.mute }, _i18n.mute ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-unmute' , 'tabindex' : 1, 'title': _i18n.unmute }, _i18n.unmute ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-volume-max' , 'tabindex' : 1, 'title': _i18n.volumeMax }, _i18n.volumeMax ) ) )
								) ) +
								// progress
								h.element( 'div', { 'class': 'jp-progress' }, new h.Raw ( h.element( 'div', { 'class': 'jp-seek-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-play-bar' }, '' ) ) ) ) ) +
								// Volumn
								h.element( 'div', { 'class': 'jp-volume-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-volume-bar-value' }, '' ) ) ) +
								// Time
								h.element( 'div', { 'class': 'jp-time-holder' },  new h.Raw (  h.element( 'div', { 'class': 'jp-current-time' }, '' ) + h.element( 'div', { 'class': 'jp-duration' }, '' ) ) ) +
								//
								h.element( 'ul', { 'class': 'jp-toggles' },
								new h.Raw (
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-shuffle' , 'tabindex' : 1, 'title' : _i18n.shuffle }, _i18n.shuffle ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-shuffle-off' , 'tabindex' : 1, 'title' : _i18n.shuffleOff }, _i18n.shuffleOff ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat' , 'tabindex' : 1, 'title' : _i18n.repeat }, _i18n.repeat ) ) ) +
									h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat-off' , 'tabindex' : 1, 'title' : _i18n.repeatOff }, _i18n.repeatOff ) ) )
								) )
						) ) + h.element( 'div', { 'class': 'jp-playlist' }, new h.Raw( h.element( 'ul', {}, new h.Raw( h.element( 'li', {}, '' ) ) ) ) ) )
				) ) );
			}
		},

		/**
		 * Video player template
		 *
		 * @return object
		 */
		video: {
			single : function( options ) { return h.element( 'div', { 'id' : options.containerId , 'class': 'jp-video' },
				new h.Raw(  h.element( 'div', { 'class': 'jp-type-single' },
					new h.Raw(
						h.element( 'div', { 'id': options.playerId, 'class': 'jp-jplayer' }  ) +
						h.element( 'div', { 'class': 'jp-gui' },
							new h.Raw(
								h.element( 'div', { 'class': 'jp-video-play' }, new h.Raw(  h.element( 'a', { 'href' :  'javascript:;', 'class' : 'jp-video-play-icon', 'tabindex' : 1 }, 'play' ) ) )+
								h.element( 'div', { 'class': 'jp-interface' },
								new h.Raw (
									// progress
									h.element( 'div', { 'class': 'jp-progress' }, new h.Raw ( h.element( 'div', { 'class': 'jp-seek-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-play-bar' }, '' ) ) ) ) ) +
									h.element( 'div', { 'class': 'jp-current-time' }, '' ) +
									h.element( 'div', { 'class': 'jp-duration' }, '' ) +
									h.element( 'div', { 'class': 'jp-controls-holder' },
									new h.Raw( h.element( 'ul', { 'class': 'jp-controls' },
										new h.Raw (
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-play' , 'tabindex' : 1, 'title': _i18n.play }, _i18n.play ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-pause' , 'tabindex' : 1, 'title': _i18n.pause }, _i18n.pause ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-stop' , 'tabindex' : 1, 'title' : _i18n.stop }, _i18n.stop ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-mute' , 'tabindex' : 1, 'title' : _i18n.mute }, _i18n.mute ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-unmute' , 'tabindex' : 1, 'title' : _i18n.unmute }, _i18n.unmute ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-volume-max' , 'tabindex' : 1, 'title': _i18n.volumeMax }, _i18n.volumeMax ) ) )
									) ) + h.element( 'div', { 'class': 'jp-volume-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-volume-bar-value' }, '' ) ) ) +
										h.element( 'ul', { 'class': 'jp-toggles' },
											new h.Raw (
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-full-screen' , 'tabindex' : 1, 'title' : _i18n.fullScreen }, _i18n.fullScreen ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-restore-screen' , 'tabindex' : 1, 'title': _i18n.restoreScreen }, _i18n.restoreScreen ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat' , 'tabindex' : 1, 'title' : _i18n.repeat }, _i18n.repeat ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat-off' , 'tabindex' : 1, 'title': _i18n.repeatOff }, _i18n.repeatOff ) ) )
									) ) ) ) + h.element( 'div', { 'class': 'jp-title' }, new h.Raw( h.element( 'ul', {}, new h.Raw( h.element( 'li', {}, '' ) ) ) ) ) )
								) ) ) +
									h.element( 'div', { 'class': 'jp-playlist' }, new h.Raw( h.element( 'ul', {}, new h.Raw( h.element( 'li', {}, '' ) ) ) ) ) )
				) ) );
			},
			multi : function( options ) { return h.element( 'div', { 'id' : options.containerId , 'class': 'jp-video' },
				new h.Raw(  h.element( 'div', { 'class': 'jp-type-playlist' },
					new h.Raw(
						h.element( 'div', { 'id': options.playerId, 'class': 'jp-jplayer' }  ) +
						h.element( 'div', { 'class': 'jp-gui' },
							new h.Raw(
								h.element( 'div', { 'class': 'jp-video-play' }, new h.Raw(  h.element( 'a', { 'href' :  'javascript:;', 'class' : 'jp-video-play-icon', 'tabindex' : 1 }, 'play' ) ) )+
								h.element( 'div', { 'class': 'jp-interface' },
								new h.Raw (
									// progress
									h.element( 'div', { 'class': 'jp-progress' }, new h.Raw ( h.element( 'div', { 'class': 'jp-seek-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-play-bar' }, '' ) ) ) ) ) +
									h.element( 'div', { 'class': 'jp-current-time' }, '' ) +
									h.element( 'div', { 'class': 'jp-duration' }, '' ) +
									h.element( 'div', { 'class': 'jp-controls-holder' },
									new h.Raw( h.element( 'ul', { 'class': 'jp-controls' },
										new h.Raw (
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-previous' , 'tabindex' : 1, 'title': _i18n.previous }, _i18n.previous ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-play' , 'tabindex' : 1, 'title': _i18n.play }, _i18n.play ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-pause' , 'tabindex' : 1, 'title': _i18n.pause }, _i18n.pause ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-next' , 'tabindex' : 1, 'title': _i18n.next }, _i18n.next ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-stop' , 'tabindex' : 1, 'title' : _i18n.stop }, _i18n.stop ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-mute' , 'tabindex' : 1, 'title' : _i18n.mute }, _i18n.mute ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-unmute' , 'tabindex' : 1, 'title' : _i18n.unmute }, _i18n.unmute ) ) ) +
											h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-volume-max' , 'tabindex' : 1, 'title': _i18n.volumeMax }, _i18n.volumeMax ) ) )
									)
								) + h.element( 'div', { 'class': 'jp-volume-bar' }, new h.Raw ( h.element( 'div', { 'class': 'jp-volume-bar-value' }, '' ) ) ) +
										h.element( 'ul', { 'class': 'jp-toggles' },
											new h.Raw (
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-full-screen' , 'tabindex' : 1, 'title' : _i18n.fullScreen }, _i18n.fullScreen ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-restore-screen' , 'tabindex' : 1, 'title': _i18n.restoreScreen }, _i18n.restoreScreen ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-shuffle' , 'tabindex' : 1, 'title' : _i18n.shuffle }, _i18n.shuffle ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-shuffle-off' , 'tabindex' : 1, 'title': _i18n.shuffleOff }, _i18n.shuffleOff ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat' , 'tabindex' : 1, 'title' : _i18n.repeat }, _i18n.repeat ) ) ) +
												h.element( 'li', {}, new h.Raw( h.element( 'a', { 'href' : 'javascript:', 'class' : 'jp-repeat-off' , 'tabindex' : 1, 'title': _i18n.repeatOff }, _i18n.repeatOff ) ) )
										) ) ) ) + h.element( 'div', { 'class': 'jp-title' }, new h.Raw( h.element( 'ul', {}, new h.Raw( h.element( 'li', {}, '' ) ) ) ) ) )
							) )
						) + h.element( 'div', { 'class': 'jp-playlist' }, new h.Raw( h.element( 'ul', {}, new h.Raw( h.element( 'li', {}, '' ) ) ) ) ) )
				) ) );
			}
		}
	};

} )( jQuery, mediaWiki, semanticFormats );