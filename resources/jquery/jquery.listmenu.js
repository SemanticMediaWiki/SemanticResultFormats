/*
*
* jQuery listmenu plugin
* Copyright (c) 2009 iHwy, Inc.
* Author: Jack Killpatrick
*
* Version 1.1 (08/09/2009)
* Requires jQuery 1.3.2 or jquery 1.2.6
*
* Visit http://www.ihwy.com/labs/jquery-listmenu-plugin.aspx for more information.
*
* Dual licensed under the MIT and GPL licenses:
*   http://www.opensource.org/licenses/mit-license.php
*   http://www.gnu.org/licenses/gpl.html
*
*/

(function($) {
	$.fn.listmenu = function(options) {
		var opts = $.extend({}, $.fn.listmenu.defaults, options);
		var alph = ['_', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '-'];

		return this.each(function() {
			var $wrapper, list, $list, $letters, letters = {}, $letterCount, id, $menu, colOpts = $.extend({}, $.fn.listmenu.defaults.cols, opts.cols), onNav = false, onMenu = false, currentLetter = '';
			id = this.id;
			$list = $(this);

			function init() {
				$list.css('visibility', 'hidden'); // hiding to prevent pre-load flicker. Using visibility:hidden so that list item dimensions remain available

				setTimeout(function() {
					$list.before(createWrapperHtml());

					$wrapper = $('#' + id + '-menu');
					$wrapper.append(createLettersHtml());

					$letters = $('.lm-letters', $wrapper).slice(0, 1); // will always be a single item
					if (opts.showCounts) $letterCount = $('.lm-letter-count', $wrapper).slice(0, 1); // will always be a single item

					$wrapper.append(createMenuHtml());
					$menu = $('.lm-menu', $wrapper);
					populateMenu();
					if (opts.flagDisabled) addDisabledClass(); // run after populateMenu(): needs some data from there

					bindHandlers();

					// decide whether to include num and/or other links
					//
					if (!opts.includeNums) $('._', $letters).remove();
					if (!opts.includeOther) $('.-', $letters).remove();
					$(':last', $letters).addClass('lm-last');

					$wrapper.show();
				}, 50);
			}

			// positions the letter count div above the letter links (so we only have to do it once: after this we just change it's left position via mouseover)
			//
			function setLetterCountTop() {
				$letterCount.css({ top: $('.a', $letters).slice(0, 1).offset({ margin: false, border: true }).top - $letterCount.outerHeight({ margin: true }) });
			}

			function addDisabledClass() {
				for (var i = 0; i < alph.length; i++) {
					if (letters[alph[i]] === undefined) $('.' + alph[i], $letters).addClass('lm-disabled');
				}
			}

			function populateMenu() {
				var gutter = colOpts.gutter,
					cols = colOpts.count,
					menuWidth,
					colWidth;

				if (opts.menuWidth) menuWidth = opts.menuWidth; // use user defined menu width if one provided, else calculate one
				else menuWidth = $('.lm-letters', $wrapper).width() - ($menu.outerWidth() - $menu.width());

				colWidth = (cols == 1) ? menuWidth : Math.floor((menuWidth - (gutter * (cols - 1))) / cols);
				$menu.width(menuWidth); // prevents it from resizing based on content

				$list.width(colWidth);

				var letter, outerHeight;
				$list.children().each(function() {
					str = $(this).text().replace(/\s+/g, ''); // strip all white space from text (including tabs and linebreaks that might have been in the HTML) // thanks to Liam Byrne, liam@onsight.ie
					// Deployed with SRF 1.9 to recognize the data-sortkey
					var sortKey = String( $(this).data( 'sortkey' ) );
					if ( sortKey !== 'undefined' ){
						firstChar = sortKey.toLowerCase();
					} else {
						if (str !== '') {
							firstChar = str.slice(0, 1).toLowerCase();
							if (/\W/.test(firstChar)) firstChar = '-'; // not A-Z, a-z or 0-9, so considered "other"
							if (!isNaN(firstChar)) firstChar = '_'; // use '_' if the first char is a number
						}
					}

					outerHeight = $(this).outerHeight();

					if (letters[firstChar] === undefined) letters[firstChar] = { totHeight: 0, count: 0, colHeight: 0, hasMenu: false };
					letter = letters[firstChar];
					letter.totHeight += outerHeight;
					letter.count++;

					$.data($(this)[0], id, { firstChar: firstChar, height: outerHeight });
				});

				$.each(letters, function() {
					this.colHeight = (this.count > 1) ? Math.ceil(this.totHeight / cols) : this.totHeight;
				});

				var $this, data, iHeight, iLetter, cols = {}, c;
				var tagName = $list[0].tagName.toLowerCase();
				$list.children().each(function() {
					$this = $(this);
					data = $.data($this.get(0), id); iLetter = data.firstChar; iHeight = data.height;
					if (!letters[l = iLetter].hasMenu) {
						$menu.append('<div class="lm-submenu ' + iLetter + '" style="display:none;"></div>');
						letters[iLetter].hasMenu = true;
					}

					if (cols[iLetter] === undefined) cols[iLetter] = { height: 0, colNum: 0, itemCount: 0, $colRoot: null };
					c = cols[iLetter];
					c.itemCount++;
					if (c.height === 0) {
						c.colNum++;
						$('.lm-submenu.' + iLetter, $menu).append('<div class="lm-col c' + c.colNum + '"><' + tagName + ((tagName == 'ol') ? ' start="' + c.itemCount + '"' : '') + ' id="lm-' + id + '-' + iLetter + '-' + c.colNum + '" class="lm-col-root"></' + tagName + '></div>'); // reset start number for OL lists (deprecacted, but no reliable css solution). Creating an id to make lookups for appending list items faster
					}
					$('#lm-' + id + '-' + iLetter + '-' + c.colNum).append($(this));

					c.height += iHeight;
					if (c.height >= letters[iLetter].colHeight) c.height = 0; // forces another column to get started if this letter comes up again
				});

				$.each(letters, function(idx) {
					if (this.hasMenu) {
						$('.lm-submenu.' + idx + ' .lm-col', $menu).css({ 'width': colWidth, 'float': 'left' });
						$('.lm-submenu.' + idx + ' .lm-col:not(:last)', $menu).css({ 'marginRight': gutter });
					}
				});

				$menu.append('<div class="lm-no-match" style="display:none">' + opts.noMatchText + '</div>');
				$list.remove();
			}

			function getLetterCount(el) {
				var letter = letters[$(el).attr('class').split(' ')[0]];
				return (letter !== undefined) ? letter.count : 0; // some letters may not be in the hash
			}

			function hideCurrentSubmenu() {
				if (currentLetter !== '') $('.lm-submenu.' + currentLetter, $menu).hide();
				$('.lm-no-match', $menu).hide(); // hiding each time, rather than checking to see if we need to hide it
			}

			function bindHandlers() {

				// sets the top position of the count div in case something above it on the page has resized
				//
				if (opts.showCounts) {
					$wrapper.mouseover(function() {
						setLetterCountTop();
					});
				}

				// kill letter clicks
				//
				$('a', $letters).click(function() {
					$(this).blur();
					return false;
				});

				$letters.hover(
					function() { onNav = true; },
					function() {
						onNav = false;

						setTimeout(function() {
							if (!onMenu) {
								$('a.lm-selected', $letters).removeClass('lm-selected');
								$('.lm-menu', $wrapper).hide();
								hideCurrentSubmenu();
								currentLetter = '';
							}
						}, 10);
					}
				);

				$('a', $letters).mouseover(function() {
					var count = getLetterCount(this);
					var $this = $(this);

					if (opts.showCounts) {
						var left = $this.position().left;
						var width = $this.outerWidth({ margin: true });
						if (opts.showCounts) $letterCount.css({ left: left, width: width + 'px' }).text(count).show(); // set left position and width of letter count, set count text and show it
					}

					var newLetter = $this.attr('class').split(' ')[0];
					if (newLetter != currentLetter) {
						if (currentLetter !== '') {
							hideCurrentSubmenu();
							$('a.lm-selected', $letters).removeClass('lm-selected');
						}
						$this.addClass('lm-selected');

						if (count > 0) $('.lm-submenu.' + newLetter, $wrapper).show();
						else $('.lm-no-match', $wrapper).show();

						if (currentLetter === '') $('.lm-menu', $wrapper).show();
						currentLetter = newLetter;
					}
				});

				$('a', $letters).mouseout(function() {
					if (opts.showCounts) $letterCount.hide();
				});

				$menu.hover(
					function() {
						onMenu = true;
					},
					function() {
						onMenu = false;
						setTimeout(function() {
							if (!onNav) {
								$('a.lm-selected', $letters).removeClass('lm-selected');
								$('.lm-menu', $wrapper).hide();
								hideCurrentSubmenu();
								currentLetter = '';
							}
						}, 10);
					}
				);

				if (opts.onClick !== null) {
					$menu.click(function(e) {
						var $target = $(e.target);
						opts.onClick($target);
						return false;
					});
				}
			}

			// creates the HTML for the letter links
			//
			function createLettersHtml() {
				var html = [];
				for (var i = 1; i < alph.length; i++) {
					if (html.length === 0) html.push('<a class="_" href="#">0-9</a>');
					html.push('<a class="' + alph[i] + '" href="#">' + ((alph[i] == '-') ? '...' : alph[i].toUpperCase()) + '</a>');
				}
				return '<div class="lm-letters">' + html.join('') + '</div>' + ((opts.showCounts) ? '<div class="lm-letter-count" style="display:none; position:absolute; top:0; left:0; width:20px;">0</div>' : ''); // the styling for letterCount is to give us a starting point for the element, which will be repositioned when made visible (ie, should not need to be styled by the user)
			}

			function createMenuHtml() {
				return '<div class="lm-menu"></div>';
			}

			function createWrapperHtml() {
				return '<div id="' + id + '-menu" class="lm-wrapper"></div>';
			}

			init();
		});
	};

	$.fn.listmenu.defaults = {
		includeNums: true,
		includeOther: false,
		flagDisabled: true,
		noMatchText: 'No matching entries',
		showCounts: true,
		menuWidth: null,
		cols: {
			count: 4,
			gutter: 40
		},
		onClick: null
	};
})(jQuery);