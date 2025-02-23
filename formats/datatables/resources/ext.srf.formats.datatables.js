/**
 * SRF DataTables JavaScript Printer using the SMWAPI
 *
 * @see http://datatables.net/
 *
 * @licence GPL-2.0-or-later
 * @author thomas-topway-it for KM-A
 * @credits mwjames (ext.smw.tableprinter.js)
 */

(function ($, mw, srf) {
	'use strict';

	/* Private methods and objects */

	/**
	 * Helper objects
	 *
	 * @since 1.9
	 *
	 * @ignore
	 * @private
	 * @static
	 */
	var html = mw.html;

	/**
	 * Cache results retrieved through Ajax up to
	 * a certain limit, this allows smooth navigation
	 * of pages already retrieved, without to perform
	 * an Ajax request again
	 */
	var _cacheLimit = 40000;

	/**
	 * Container for all non-public objects and methods
	 *
	 * @private
	 * @member srf.formats.datatables
	 */
	var _datatables = {
		/**
		 * Returns ID
		 *
		 * @private
		 * @return {string}
		 */
		getID: function (container) {
			return container.attr('id');
		},

		getNestedProp: function (path, obj) {
			return path.reduce((xs, x) => (xs && xs[x] ? xs[x] : null), obj);
		},

		objectValues: function (obj) {
			return Object.keys(obj).map(function (e) {
				return obj[e];
			});
		},
		objectEntries: function (obj) {
			var keys = Object.keys(obj),
				i = keys.length,
				ret = new Array(i);
			while (i--) {
				ret[i] = [keys[i], obj[keys[i]]];
			}
			return ret;
		},

		getCacheLimit: function (obj) {
			return _cacheLimit;
		},

		getCacheKey: function (obj) {
			// this ensures that the preload key
			// and the dynamic key match
			// this does not work: "searchPanes" in obj && Object.entries(obj.searchPanes).find(x => Object.keys(x).length ) ? obj.searchPanes : {},
			if ('searchPanes' in obj) {
				for (var i in obj.searchPanes) {
					if (!Object.keys(obj.searchPanes[i]).length) {
						delete obj.searchPanes[i];
					}
				}
			}

			return objectHash.sha1({
				order: obj.order.map((x) => {
					return { column: x.column, dir: x.dir };
				}),
				// search: obj.search,
				searchPanes:
					'searchPanes' in obj &&
					_datatables
						.objectEntries(obj.searchPanes)
						.find((x) => Object.keys(x).length)
						? obj.searchPanes
						: {},
				searchBuilder: 'searchBuilder' in obj ? obj.searchBuilder : {},
			});
		},

		getCacheData: function (count, preloadData, cacheKey, datatableData) {
			if (!(cacheKey in preloadData)) {
				return false;
			}

			var data = [];
			for (
				var i = datatableData.start;
				i < datatableData.start + datatableData.length;
				i++
			) {
				if (i >= count) {
					break;
				}
				if (!(i in preloadData[cacheKey].data)) {
					return false;
				}
				data.push(preloadData[cacheKey].data[i]);
			}

			return { count: preloadData[cacheKey].count, data };
		},

		setCacheData: function (preloadData, json) {
			var cacheKey = json.cacheKey;
			if (!(cacheKey in preloadData)) {
				preloadData[cacheKey] = { data: {} };
			}

			var n = json.start;
			for (var row of json.data) {
				preloadData[cacheKey].data[n] = row;
				n++;
			}

			preloadData[cacheKey].count = json.recordsFiltered;
		},

		/**
		 * Returns container data
		 *
		 * @private
		 * @return {object}
		 */
		getData: function (container) {
			return mw.config.get(this.getID(container));
		},

		/**
		 * Adds the initial sort/order from the #ask request that is available as
		 * `data-column-sort` attribute with something like:
		 *
		 * {
		 *  "list":["","Foo","Bar"]
		 *  "sort":["Foo"],
		 *  "order":["asc"]
		 * }
		 *
		 * on
		 *
		 * {{#ask: ...
		 *  |?Foo
		 *  |?Bar
		 *  |sort=Foo
		 *  |order=asc
		 *  ...
		 * }}
		 *
		 * @since 3.0
		 *
		 * @private
		 * @static
		 *
		 * @param {Object} table
		 */
		initColumnSort: function (table) {
			var column = table.data('column-sort');

			var order = [];

			// SMW allows descending and ascending but those are unknown to DataTables
			var orderMap = {
				descending: 'desc',
				ascending: 'asc',
				asc: 'asc',
				desc: 'desc',
			};

			// https://datatables.net/reference/api/order()
			// [1, 'asc'], [2, 'desc']
			$.map(column.sort, function (val, i) {
				if (val === '') {
					i = 0;
				}

				if ($.inArray(val, column.list) < 0) {
					return;
				}

				order.push([
					$.inArray(val, column.list), // Find matchable index from the list
					column.order[i] === undefined ? 'asc' : orderMap[column.order[i]],
				]);
			});

			if (order.length > 0) {
				table.data('order', order);
			} else {
				// default @see https://datatables.net/reference/option/order
				table.data('order', [[0, 'asc']]);
			}
		},

		initSearchPanesColumns(columnDefs, options) {
			for (var i in columnDefs) {
				if (!('searchPanes' in columnDefs[i])) {
					columnDefs[i].searchPanes = {};
				}

				if (
					'show' in columnDefs[i].searchPanes &&
					columnDefs[i].searchPanes.show === false
				) {
					delete columnDefs[i].searchPanes;
					continue;
				}

				if (
					'columns' in options.searchPanes &&
					options.searchPanes.columns.length &&
					$.inArray(i * 1, options.searchPanes.columns) < 0
				) {
					delete columnDefs[i].searchPanes;
				}
			}
		},

		// this is used only if Ajax is disabled and
		// the table does not have fields with multiple values
		getPanesOptions: function (data, columnDefs, options) {
			var ret = {};
			var dataLength = {};
			var div = document.createElement('div');

			for (var i in columnDefs) {
				if ('searchPanes' in columnDefs[i]) {
					ret[i] = {};
					dataLength[i] = 0;
				}
			}

			for (var i in data) {
				for (var ii in ret) {
					var cellData = data[i][ii];
					if (!cellData) {
						continue;
					}
					dataLength[ii]++;
					var label;
					if (options.searchPanes.htmlLabels === false) {
						div.innerHTML = cellData.display;
						label = div.textContent || div.innerText || '';
					} else {
						label = cellData.display;
					}

					// this will exclude images as well if
					// options.searchPanes.htmlLabels === false
					if (label === '') {
						continue;
					}

					if (!(cellData.display in ret[ii])) {
						ret[ii][cellData.display] = {
							label: label,
							value: cellData.display,
							count: 0,
						};
					}

					ret[ii][cellData.display].count++;
				}
			}

			for (var i in ret) {
				var threshold =
					'threshold' in columnDefs[i].searchPanes
						? columnDefs[i].searchPanes.threshold
						: options.searchPanes.threshold;

				// @see https://datatables.net/extensions/searchpanes/examples/initialisation/threshold.htm
				// @see https://github.com/DataTables/SearchPanes/blob/818900b75dba6238bf4b62a204fdd41a9b8944b7/src/SearchPane.ts#L824
				// _uniqueRatio
				var binLength = Object.keys(ret[i]).length;
				// data.length;
				var uniqueRatio = binLength / dataLength[i];

				//  || binLength <= 1
				if (uniqueRatio > threshold) {
					delete ret[i];
					continue;
				}

				ret[i] = Object.values(ret[i]).filter(
					(x) => x.count >= options.searchPanes.minCount
				);

				if (!ret[i].length) {
					delete ret[i];
				}
			}

			for (var i in columnDefs) {
				if (!(i in ret)) {
					// delete columnDefs[i].searchPanes;
					columnDefs[i].searchPanes = { show: false };
				}
			}

			return ret;
		},

		setPanesOptions: function (searchPanesOptions, columnDefs) {
			for (let i in searchPanesOptions) {
				// @see https://datatables.net/reference/option/columns.searchPanes.combiner
				columnDefs[i].searchPanes.combiner =
					'combiner' in columnDefs[i].searchPanes
						? columnDefs[i].searchPanes.combiner
						: 'or';
				columnDefs[i].searchPanes.options = [];

				// @see https://datatables.net/reference/option/columns.searchPanes.options
				for (let ii in searchPanesOptions[i]) {
					columnDefs[i].searchPanes.options.push({
						label: searchPanesOptions[i][ii].label,
						value: function (rowData, rowIdx) {
							return rowData[i].display === searchPanesOptions[i][ii].value;
						},
					});
				}

				// @TODO sort panes after rendering using the following
				// https://github.com/DataTables/SearchPanes/blob/master/src/SearchPane.ts
			}
		},

		searchPanesOptionsServer: function (
			searchPanesOptions,
			columnDefs,
			options
		) {
			var div = document.createElement('div');
			for (var i in searchPanesOptions) {
				if (!('searchPanes' in columnDefs[i])) {
					columnDefs[i].searchPanes = {};
				}
				columnDefs[i].searchPanes.show =
					Object.keys(searchPanesOptions[i]).length > 0;

				for (var ii in searchPanesOptions[i]) {
					if (options.searchPanes.htmlLabels === false) {
						div.innerHTML = searchPanesOptions[i][ii].label;
						searchPanesOptions[i][ii].label =
							div.textContent || div.innerText || '';
					}

					searchPanesOptions[i][ii].total = searchPanesOptions[i][ii].count;
				}
			}

			for (var i in columnDefs) {
				if ('searchPanes' in columnDefs[i] && !(i in searchPanesOptions)) {
					delete columnDefs[i].searchPanes;
				}
			}

			return searchPanesOptions;
		},

		callApi: function (
			data,
			callback,
			preloadData,
			searchPanesOptions,
			displayLog
		) {
			var payload = {
				action: 'ext.srf.datatables.api',
				format: 'json',
				data: JSON.stringify(data),
			};

			new mw.Api()
				.post(payload)
				.done(function (results) {
					var json = results['datatables-json'];

					if (displayLog) {
						console.log('results log', json.log);
					}

					// cache all retrieved rows for each sorting
					// dimension (column/dir), up to a fixed
					// threshold (CacheLimit)
					if (json.cacheKey) {
						_datatables.setCacheData(preloadData, json);
					}

					// we retrieve more than "length"
					// expected by datatables, so return the
					// sliced result
					json.data = json.data.slice(0, data.datalength);
					json.searchPanes = {
						options: searchPanesOptions,
					};
					callback(json);
				})
				.fail(function (error) {
					console.log('error', error);
				});
		},

		/**
		 * Internationalization
		 * @see  http://datatables.net/usage/i18n
		 *
		 * @private
		 * @return {object}
		 */
		oLanguage: {
			oAria: {
				sSortAscending: mw.msg('srf-ui-datatables-label-oAria-sSortAscending'),
				sSortDescending: mw.msg(
					'srf-ui-datatables-label-oAria-sSortDescending'
				),
			},
			oPaginate: {
				sFirst: mw.msg('srf-ui-datatables-label-oPaginate-sFirst'),
				sLast: mw.msg('srf-ui-datatables-label-oPaginate-sLast'),
				sNext: mw.msg('srf-ui-datatables-label-oPaginate-sNext'),
				sPrevious: mw.msg('srf-ui-datatables-label-oPaginate-sPrevious'),
			},
			sEmptyTable: mw.msg('srf-ui-datatables-label-sEmptyTable'),
			sInfo: mw.msg('srf-ui-datatables-label-sInfo'),
			sInfoEmpty: mw.msg('srf-ui-datatables-label-sInfoEmpty'),
			sInfoFiltered: mw.msg('srf-ui-datatables-label-sInfoFiltered'),
			sInfoPostFix: mw.msg('srf-ui-datatables-label-sInfoPostFix'),
			sInfoThousands: mw.msg('srf-ui-datatables-label-sInfoThousands'),
			sLengthMenu: mw.msg('srf-ui-datatables-label-sLengthMenu'),
			sLoadingRecords: mw.msg('srf-ui-datatables-label-sLoadingRecords'),

			// *** hide "processing" label above the indicator
			// sProcessing: mw.msg("srf-ui-datatables-label-sProcessing"),

			sSearch: mw.msg('srf-ui-datatables-label-sSearch'),
			sZeroRecords: mw.msg('srf-ui-datatables-label-sZeroRecords'),

			searchBuilder: {
				title: {
					// Format condition count into info chip
					_: `${mw.msg('srf-ui-datatables-label-conditions')} <span class="srf-datatables-info-chip">%d</span>`,
					0: mw.msg('srf-ui-datatables-label-conditions'),
				},
			},
			searchPanes: {
				title: {
					// Format filter count into info chip
					_: `${mw.msg('srf-ui-datatables-label-filters')} <span class="srf-datatables-info-chip">%d</span>`,
					0: mw.msg('srf-ui-datatables-label-filters'),
				},
			},
		},

		// we don't need it anymore, however keep it as
		// a reference for other use
		showNotice: function (context, container, msg) {
			var cookieKey =
				'srf-ui-datatables-searchpanes-notice-' +
				mw.config.get('wgUserName') +
				'-' +
				mw.config.get('wgArticleId');

			if (
				mw.config.get('wgUserName') != context.data('editor') ||
				mw.cookie.get(cookieKey)
			) {
				return;
			}

			var messageWidget = new OO.ui.MessageWidget({
				type: 'warning',
				label: new OO.ui.HtmlSnippet(mw.msg(msg)),
				// *** this does not work before ooui v0.43.0
				showClose: true,
			});
			var closeFunction = function () {
				// 1 month
				var expires = 1 * 30 * 24 * 3600;
				mw.cookie.set(cookieKey, true, {
					path: '/',
					expires: expires,
				});
				$(messageWidget.$element).parent().remove();
			};
			messageWidget.on('close', closeFunction);
			$(context).prepend($('<div><br/></div>').prepend(messageWidget.$element));
			if (!messageWidget.$element.hasClass('oo-ui-messageWidget-showClose')) {
				messageWidget.$element.addClass('oo-ui-messageWidget-showClose');
				var closeButton = new OO.ui.ButtonWidget({
					classes: ['oo-ui-messageWidget-close'],
					framed: false,
					icon: 'close',
					label: OO.ui.msg('ooui-popup-widget-close-button-aria-label'),
					invisibleLabel: true,
				});
				closeButton.on('click', closeFunction);
				messageWidget.$element.append(closeButton.$element);
			}
		},
	};

	/**
	 * Inheritance class for the srf.formats constructor
	 *
	 * @since 1.9
	 *
	 * @class
	 * @abstract
	 */
	srf.formats = srf.formats || {};

	/**
	 * Class that contains the DataTables JavaScript result printer
	 *
	 * @since 1.9
	 *
	 * @class
	 * @constructor
	 * @extends srf.formats
	 */
	srf.formats.datatables = function () {};

	/* Public methods */

	srf.formats.datatables.prototype = {
		/**
		 * Default settings
		 *
		 * @note MW 1.21 vs MW 1.20
		 * Apparently mw.config.get( 'srf' )/mw.config.get( 'smw' ) does only work
		 * in MW 1.21 therefore instead of being customizable those settings are
		 * going to be fixed
		 *
		 * TTL (if enabled) cache for resultObject is set to be 15 min by default
		 * TTL (if enabled) cache for imageInfo is set to be 24 h
		 *
		 * @since  1.9
		 *
		 * @property
		 */
		defaults: {
			// ...
		},

		/**
		 * Initializes the DataTables instance
		 *
		 * @since 1.9
		 *
		 * @param  {array} container
		 * @param  {array} data
		 */
		init: function (container, data) {
			var self = this;

			var table = container.find('table');
			table.removeClass('wikitable');
			table.find('tbody:first').attr('aria-live', 'polite');

			_datatables.initColumnSort(table);

			var order = table.data('order');
			var options = data['formattedOptions'];

			function isObject(obj) {
				return obj !== null && typeof obj === 'object' && !Array.isArray(obj);
			}

			if (isObject(options.scroller)) {
				if (!('scrollY' in options) || !options.scrollY) {
					options.scrollY = '300px';

					// expected type is string
				} else if (!isNaN(options.scrollY)) {
					options.scrollY = options.scrollY + 'px';
				}
			}

			var queryResult = data.query.result;
			var useAjax = table.data('useAjax');
			var count = parseInt(table.data('count'));

			// var mark = isObject(options.mark);

			var searchPanes = isObject(options.searchPanes);

			var searchBuilder = options.searchBuilder;

			if (searchBuilder) {
				// @see https://datatables.net/extensions/searchbuilder/customConditions.html
				// @see https://github.com/DataTables/SearchBuilder/blob/master/src/searchBuilder.ts
				options.searchBuilder = {
					depthLimit: 1,
					conditions: {
						html: {
							null: null,
						},
						string: {
							null: null,
						},
						date: {
							null: null,
						},
						num: {
							null: null,
						},
					},
				};
			}

			options.layout = {
				top9End: options.buttons.length ? 'buttons' : null,
				top3: searchBuilder ? 'searchBuilder' : null,
				top2: searchPanes ? 'searchPanes' : null,
				topStart: {
					pageLength: {
						text: '_MENU_',
					},
				},
				topEnd: {
					search: {
						// Hide label and use placeholder
						placeholder: mw.msg('search'),
						text: '_INPUT_',
					},
				},
				bottomStart: 'info',
				bottomEnd: 'paging',
			};

			// add the pagelength at the proper place in the length menu
			if ($.inArray(options.pageLength, options.lengthMenu) < 0) {
				options.lengthMenu.push(options.pageLength);
				options.lengthMenu.sort(function (a, b) {
					return a - b;
				});
			}

			// Replace -1 in lengthMenu with 'all' label
			var showAll = options.lengthMenu.indexOf(-1);
			var lengthMenuLabels = options.lengthMenu.slice();
			if (showAll !== -1) {
				lengthMenuLabels[showAll] = mw.msg('srf-ui-datatables-label-rows-all');
			}
			// Format value into readable label
			for (var i = 0; i < lengthMenuLabels.length; i++) {
				if (typeof lengthMenuLabels[i] !== 'number') {
					continue;
				}
				lengthMenuLabels[i] = mw.msg(
					'srf-ui-datatables-label-rows',
					lengthMenuLabels[i]
				);
			}
			options.lengthMenu = [options.lengthMenu, lengthMenuLabels];

			var query = data.query.ask;
			var printouts = table.data('printouts');
			var queryString = query.conditions;
			var printrequests = table.data('printrequests');
			var searchPanesOptions = data.searchPanes;

			var searchPanesLog = data.searchPanesLog;

			var displayLog = mw.config.get('performer') === table.data('editor');

			if (displayLog) {
				console.log('data', data);
				console.log('searchPanesLog', searchPanesLog);
			}

			var entityCollation = table.data('collation');

			var columnDefs = [];
			$.map(printrequests, function (property, index) {

				var isNumeric =
					(entityCollation === 'numeric' && property.typeid === '_wpg') ||
					['_num', '_tem', '_qty'].indexOf(property.typeid) !== -1;

				options.columns.type = isNumeric ? 'num' : 'string';

				columnDefs.push(
					$.extend(
						{
							// https://datatables.net/reference/option/columnDefs
							// data: ...
							title: property.label,
							// get canonical label or empty string if mainlabel
							name: printrequests[index].key !== '' ? printouts[index][1] : '',
							className: 'smwtype' + property.typeid,
							targets: [index],

							// https://datatables.net/reference/option/columns.render
							render: {
								_: 'display',
								display: 'display',
								filter: 'filter',
								sort: 'sort',
							},
						},
						options.columns,
						data.printoutsParametersOptions[index]
					)
				);

				// labelsCount[property.label]++;
			});

			if (searchPanes) {
				_datatables.initSearchPanesColumns(columnDefs, options);

				// *** this should now be true only if ajax is
				// disabled and the table has no fields with
				// multiple values
				if (!Object.keys(searchPanesOptions).length) {
					searchPanesOptions = _datatables.getPanesOptions(
						queryResult,
						columnDefs,
						options
					);
					_datatables.setPanesOptions(searchPanesOptions, columnDefs);
				} else {
					searchPanesOptions = _datatables.searchPanesOptionsServer(
						searchPanesOptions,
						columnDefs,
						options
					);
				}
			}

			// ***important !! this has already
			// been used for columnDefs initialization !
			// otherwise the table won't sort !!
			delete options.columns;

			var conf = $.extend(options, {
				columnDefs: columnDefs,
				language: _datatables.oLanguage,
				order: order,
				search: {
					caseInsensitive: table.data('nocase'),
				},
				initComplete: function () {
					$(container).find('.datatables-spinner').hide();
				},
			});

			// cacheKey ensures that the cached pages
			// are related to current sorting and searchPanes filters
			var getCacheKey = function (obj) {
				// this ensures that the preload key
				// and the dynamic key match
				// this does not work: "searchPanes" in obj && Object.entries(obj.searchPanes).find(x => Object.keys(x).length ) ? obj.searchPanes : {},
				if ('searchPanes' in obj) {
					for (var i in obj.searchPanes) {
						if (!Object.keys(obj.searchPanes[i]).length) {
							delete obj.searchPanes[i];
						}
					}
				}

				return objectHash.sha1({
					order: obj.order,
					// search: obj.search,
					searchPanes:
						'searchPanes' in obj &&
						Object.entries(obj.searchPanes).find((x) => Object.keys(x).length)
							? obj.searchPanes
							: {},
					searchBuilder: 'searchBuilder' in obj ? obj.searchBuilder : {},
				});
			};

			if ((searchPanes || searchBuilder) && table.data('multiple-values')) {
				useAjax = true;
			}

			if (!useAjax) {
				conf.serverSide = false;
				conf.data = queryResult;

				// use Ajax only when required
			} else {
				// prevents double spinner
				$(container).find('.datatables-spinner').hide();

				var preloadData = {};

				// cache using the column index and sorting
				// method, as pseudo-multidimensional array
				// column index + dir (asc/desc) + searchPanes (empty selection)
				var cacheKey = getCacheKey({
					order: order.map((x) => {
						return { column: x[0], dir: x[1] };
					}),
				});

				_datatables.setCacheData(preloadData, {
					cacheKey,
					data: queryResult,
					recordsFiltered: count,
					start: query.parameters.offset,
				});

				var payloadData = {
					queryString,
					columnDefs,
					printouts,
					printrequests,
					settings: $.extend(
						{ count: count, displayLog: displayLog },
						query.parameters
					),
				};

				conf = $.extend(conf, {
					// *** attention! deferLoading when used in conjunction with
					// ajax, expects only the first page of data, if the preloaded
					// data contain more rows, datatables will show a wrong rows
					// counter. For this reason we renounce to use deferRender, and
					// instead we use the following hack: the Ajax function returns
					// the preloaded data as long they are available for the requested
					// slice, and then it uses an ajax call for not available data.
					// deferLoading: table.data("count"),
					processing: true,
					serverSide: true,
					ajax: function (datatableData, callback, settings) {
						// must match initial cacheKey
						var thisCacheKey = !_datatables.getNestedProp(
							['search', 'value'],
							datatableData
						)
							? _datatables.getCacheKey(datatableData)
							: null;

						// returned cached data for the required
						// dimension (order column/dir)
						if (thisCacheKey) {
							var cacheData = _datatables.getCacheData(
								count,
								preloadData,
								thisCacheKey,
								datatableData
							);
							if (cacheData) {
								return callback({
									draw: datatableData.draw,
									data: cacheData.data,
									recordsTotal: count,
									recordsFiltered: cacheData.count,
									searchPanes: {
										options: searchPanesOptions,
									},
								});
							}
						}

						// flush cache each 40,000 rows
						// *** another method is to compute the actual
						// size in bytes of each row, but it takes more
						// resources
						for (var i in preloadData) {
							var totalSize = _datatables.objectValues(
								preloadData[i].data
							).length;

							if (totalSize > _datatables.getCacheLimit()) {
								console.log('flushing datatables cache!');
								preloadData[i] = {};
							}
						}

						_datatables.callApi(
							$.extend(payloadData, {
								datatableData,
								cacheKey,
							}),
							callback,
							preloadData,
							searchPanesOptions,
							displayLog
						);
					},
				});
			}

			table.DataTable(conf);
		},

		test: {
			// ...
		},
	};

	/**
	 * dataTables implementation
	 *
	 * @ignore
	 */
	var datatables = new srf.formats.datatables();

	$(document).ready(function () {
		$('.datatables-container').each(function () {
			var container = $(this);
			var data = JSON.parse(_datatables.getData(container));
			datatables.init(container, data);
		});
	});
})(jQuery, mediaWiki, semanticFormats);
